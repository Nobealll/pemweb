
<?php

// Menangani pendaftaran mahasiswa ke kelas dan pembatalannya secara transaksional.
class Pendaftaran
{
    // Menyimpan koneksi database yang diperlukan untuk operasi pendaftaran.
    private mysqli $db;

    // Menerima koneksi database ketika pengelola pendaftaran dibuat.
    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    // Memeriksa kelayakan pendaftaran, menyimpan relasi, dan memperbarui jumlahnya.
    public function daftarMahasiswaKeKelas(
        Mahasiswa $mahasiswa,
        int $kelas_id
    ): bool {
        try {
            // Memulai transaksi agar perubahan pendaftaran dan jumlah tetap konsisten.
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

            // Menghentikan proses jika kelas tujuan tidak ditemukan.
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

            // Menolak pendaftaran duplikat untuk mahasiswa dan kelas yang sama.
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

            // Mencegah pendaftaran baru melampaui kapasitas kelas.
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

            // Menyimpan jumlah terbaru pada data kelas sebelum transaksi disahkan.
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

            // Mengesahkan semua perubahan dalam transaksi.
            $this->db->commit();

            return true;

        } catch (Throwable $e) {
            // Membatalkan seluruh perubahan jika salah satu tahap mengalami kegagalan.
            $this->db->rollback();
            throw $e;
        }
    }

    // Menghapus relasi pendaftaran lalu menyelaraskan jumlah pendaftar pada kelas.
    public function batalkanPendaftaran(
        Mahasiswa $mahasiswa,
        int $kelas_id
    ): bool {
        try {
            // Memulai transaksi dan mengunci kelas agar pembaruan jumlah konsisten.
            $this->db->begin_transaction();

            $query = $this->db->prepare(
                "SELECT id FROM kelas
                 WHERE id = ? FOR UPDATE"
            );
            $query->bind_param("i", $kelas_id);
            $query->execute();

            // Pastikan kelas tujuan masih tersedia sebelum membatalkan pendaftaran.
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

            // Laporkan kegagalan jika tidak ada pendaftaran yang dapat dibatalkan.
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

            // Menyelaraskan jumlah pendaftar kelas dengan hasil hitung terbaru.
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

            // Mengesahkan penghapusan pendaftaran dan pembaruan jumlah.
            $this->db->commit();

            return true;

        } catch (Throwable $e) {
            // Memulihkan keadaan database jika pembatalan gagal di tengah proses.
            $this->db->rollback();
            throw $e;
        }
    }
}
