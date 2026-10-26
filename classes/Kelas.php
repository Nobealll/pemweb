<?php

class Kelas
{
    private mysqli $koneksi;
    public int $id;
    public string $nama_kelas;
    public int $kapasitas;
    public int $jumlah_daftar;

    public function __construct(mysqli $koneksi, int $id = 0, string $nama_kelas = '', int $kapasitas = 0, int $jumlah_daftar = 0)
    {
        $this->koneksi = $koneksi;
        $this->id = $id;
        $this->nama_kelas = $nama_kelas;
        $this->kapasitas = $kapasitas;
        $this->jumlah_daftar = $jumlah_daftar;
    }

    public function tambahKelas(string $nama_kelas, int $kapasitas): int
    {
        $this->validasi($nama_kelas, $kapasitas);
        $stmt = $this->koneksi->prepare('INSERT INTO kelas (nama_kelas, kapasitas) VALUES (?, ?)');
        $stmt->bind_param('si', $nama_kelas, $kapasitas);
        $stmt->execute();

        return (int) $this->koneksi->insert_id;
    }

    public function ubahKelas(int $id, string $nama_kelas, int $kapasitas): void
    {
        $this->validasi($nama_kelas, $kapasitas);

        $kelas = $this->cariById($id);
        if ($kelas === null) {
            throw new RuntimeException('Kelas tidak ditemukan.');
        }

        if ($kapasitas < $kelas['jumlah_daftar']) {
            throw new RuntimeException('Kapasitas tidak boleh lebih kecil dari jumlah mahasiswa yang sudah terdaftar.');
        }

        $stmt = $this->koneksi->prepare('UPDATE kelas SET nama_kelas = ?, kapasitas = ? WHERE id = ?');
        $stmt->bind_param('ssi', $nama_kelas, $kapasitas, $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0 && ($kelas['nama_kelas'] !== $nama_kelas || $kelas['kapasitas'] !== $kapasitas)) {
            throw new RuntimeException('Perubahan kelas gagal disimpan.');
        }
    }

    public function hapusKelas(int $id): void
    {
        $stmt = $this->koneksi->prepare('DELETEFROM kelas WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            throw new RuntimeException('Kelas tidak ditemukan.');
        }
    }

    public function tampilkanKelas(): array
    {
        $result = $this->koneksi->query(
            'SELECT k.id, k.nama_kelas, k.kapasitas, COUNT(p.mahasiswa_id) AS jumlah_daftar
            FROM kelas k
            LEFT JOIN pendaftaran p ON p.kelas_id = k.id
            GROUP BY k.id, k.nama_kelas, k.kapasitas
            ORDER BY k.nama_kelas'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function cariById(int $id): ?array
    {
        $stmt = $this->koneksi->prepare(
            'SELECT k.id, k.nama_kelas, k.kapasitas, COUNT(p.mahasiswa_id) AS jumlah_daftar
            FROM kelas k
            LEFT JOIN pendaftaran p ON p.kelas_id = k.id
            WHERE k.id = ?
            GROUP BY k.id, k.nama_kelas, k.kapasitas'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $kelas = $stmt->get_result()->fetch_assoc();

        if ($kelas === null) {
            return null;
        }

        $kelas['id'] = (int) $kelas['id'];
        $kelas['kapasitas'] = (int) $kelas['kapasitas'];
        $kelas['jumlah_daftar'] = (int) $kelas['jumlah_daftar'];

        return $kelas;
    }

    private function validasi(string $nama_kelas, int $kapasitas): void
    {
        $nama_tersih = trim($nama_kelas);

        if ($nama_tersih === '' || mb_strlen($nama_tersih) > 150) {
            throw new InvalidArgumentException('Nama kelas wajib diisi dan maksimal 150 karakter.');
        }

        if ($kapasitas < 1 || $kapasitas > 100000) {
            throw new InvalidArgumentException('Kapasitas harus antara 1 dan 100.000 mahasiswa.');
        }
    }
}
