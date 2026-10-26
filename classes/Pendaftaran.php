
<?php

class Pendaftaran
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function daftarMahasiswaKeKelas(
        Mahasiswa $mahasiswa,
        int $kelas_id
    ): bool {
        try {
            $this->db->begin_transaction();

            // Kunci baris kelas selama proses pendaftaran.
            $query = $this->db->prepare(
                "SELECT id, kapasitas
                FROM kelas
                WHERE id = ?
                FOR UPDATE"
            );
            $query->bind_param("i", $kelas_id);
            $query->execute();

            $kelas = $query->get_result()->fetch_assoc();

            if (!$kelas) {
                throw new RuntimeException(
                    "Kelas tidak ditemukan."
                );
            }

            // Periksa apakah mahasiswa sudah terdaftar.
            $query = $this->db->prepare(
                "SELECT id FROM pendaftaran
                WHERE mahasiswa_id = ? AND kelas_id = ?"
            );
            $query->bind_param(
                "ii",
                $mahasiswa->id,
                $kelas_id
            );
            $query->execute();

            if ($query->get_result()->num_rows > 0) {
                throw new RuntimeException(
                    "Mahasiswa sudah terdaftar di kelas ini."
                );
            }

            // Hitung jumlah pendaftar yang sebenarnya.
            $query = $this->db->prepare(
                "SELECT COUNT(*) AS total
                FROM pendaftaran
                WHERE kelas_id = ?"
            );
            $query->bind_param("i", $kelas_id);
            $query->execute();

            $jumlah = (int) $query->get_result()
                ->fetch_assoc()['total'];

            if ($jumlah >= (int) $kelas['kapasitas']) {
                throw new RuntimeException(
                    "Pendaftaran gagal. Kelas sudah penuh."
                );
            }

            // Simpan pendaftaran.
            $query = $this->db->prepare(
                "INSERT INTO pendaftaran
                 (mahasiswa_id, kelas_id)
                 VALUES (?, ?)"
            );
            $query->bind_param(
                "ii",
                $mahasiswa->id,
                $kelas_id
            );
            $query->execute();

            // Perbarui jumlah pendaftar.
            $jumlah++;

            $query = $this->db->prepare(
                "UPDATE kelas
                 SET jumlah_daftar = ?
                 WHERE id = ?"
            );
            $query->bind_param(
                "ii",
                $jumlah,
                $kelas_id
            );
            $query->execute();

            $this->db->commit();

            return true;

        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function batalkanPendaftaran(
        Mahasiswa $mahasiswa,
        int $kelas_id
    ): bool {
        try {
            $this->db->begin_transaction();

            $query = $this->db->prepare(
                "SELECT id FROM kelas
                 WHERE id = ? FOR UPDATE"
            );
            $query->bind_param("i", $kelas_id);
            $query->execute();

            if (!$query->get_result()->fetch_assoc()) {
                throw new RuntimeException(
                    "Kelas tidak ditemukan."
                );
            }

            $query = $this->db->prepare(
                "DELETE FROM pendaftaran
                 WHERE mahasiswa_id = ? AND kelas_id = ?"
            );
            $query->bind_param(
                "ii",
                $mahasiswa->id,
                $kelas_id
            );
            $query->execute();

            if ($query->affected_rows === 0) {
                throw new RuntimeException(
                    "Pendaftaran tidak ditemukan."
                );
            }

            // Hitung ulang jumlah pendaftar.
            $query = $this->db->prepare(
                "SELECT COUNT(*) AS total
                 FROM pendaftaran
                 WHERE kelas_id = ?"
            );
            $query->bind_param("i", $kelas_id);
            $query->execute();

            $jumlah = (int) $query->get_result()
                ->fetch_assoc()['total'];

            $query = $this->db->prepare(
                "UPDATE kelas
                 SET jumlah_daftar = ?
                 WHERE id = ?"
            );
            $query->bind_param(
                "ii",
                $jumlah,
                $kelas_id
            );
            $query->execute();

            $this->db->commit();

            return true;

        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
