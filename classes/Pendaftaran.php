<?php

class Pendaftaran
{
    private mysqli $koneksi;

    public function __construct(mysqli $koneksi)
    {
        $this->koneksi = $koneksi;
    }

    public function daftarMahasiswaKeKelas(Mahasiswa $mahasiswa, Kelas $kelas): void
    {
        $kelasId = $kelas->id;
        if ($kelasId < 1) {
            throw new InvalidArgumentException('Kelas yang dipilih tidak valid.');
        }

        $this->koneksi->begin_transaction();

        try {
            $stmt = $this->koneksi->prepare('SELECT id FROM mahasiswa WHERE id = ?');
            $stmt->bind_param('s', $mahasiswa->id);
            $stmt->execute();
            $mahasiswaAda = $stmt->get_result()->fetch_assoc() !== null;

            if ($mahasiswaAda) {
                $stmt = $this->koneksi->prepare('SELECT nama FROM mahasiswa WHERE id = ?');
                $stmt->bind_param('s', $mahasiswa->id);
                $stmt->execute();
                $dataMahasiswa = $stmt->get_result()->fetch_assoc();
                if ($dataMahasiswa['nama'] !== $mahasiswa->nama) {
                    throw new RuntimeException('Nama tidak sesuai dengan ID mahasiswa tersebut.');
                }
            } else {
                $stmt = $this->koneksi->prepare('INSERT INTO mahasiswa (id, nama) VALUES (?, ?)');
                $stmt->bind_param('ss', $mahasiswa->id, $mahasiswa->nama);
                $stmt->execute();
            }

            $stmt = $this->koneksi->prepare('SELECT kapasitas FROM kelas WHERE id = ? FOR UPDATE');
            $stmt->bind_param('i', $kelasId);
            $stmt->execute();
            $kelas = $stmt->get_result()->fetch_assoc();
            if ($kelas === null) {
                throw new RuntimeException('Kelas tidak ditemukan.');
            }

            $stmt = $this->koneksi->prepare(
                'SELECT COUNT(*) AS jumlah FROM pendaftaran WHERE kelas_id = ?'
            );
            $stmt->bind_param('i', $kelasId);
            $stmt->execute();
            $jumlah = (int) $stmt->get_result()->fetch_assoc()['jumlah'];
            if ($jumlah >= (int) $kelas['kapasitas']) {
                throw new RuntimeException('Kelas sudah penuh dan tidak menerima pendaftaran baru.');
            }

            $stmt = $this->koneksi->prepare(
                'INSERT INTO pendaftaran (mahasiswa_id, kelas_id) VALUES (?, ?)'
            );
            $stmt->bind_param('si', $mahasiswa->id, $kelasId);
            $stmt->execute();
            $this->koneksi->commit();
        } catch (Throwable $error) {
            $this->koneksi->rollback();
            throw $error;
        }
    }

    public function batalkanPendaftaran(Mahasiswa $mahasiswa, Kelas $kelas): void
    {
        $kelasId = $kelas->id;
        if ($kelasId < 1) {
            throw new InvalidArgumentException('Kelas yang dipilih tidak valid.');
        }

        $stmt = $this->koneksi->prepare(
            'DELETE FROM pendaftaran WHERE mahasiswa_id = ? AND kelas_id = ?'
        );
        $stmt->bind_param('si', $mahasiswa->id, $kelasId);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            throw new RuntimeException('Pendaftaran tidak ditemukan.');
        }
    }
}
