
<?php

// Mengelola data kelas serta operasi penyimpanan kelas ke database.
class Kelas
{
    // Menyimpan koneksi database yang dipakai oleh seluruh metode kelas.
    private mysqli $db;

    // Menerima koneksi database ketika objek Kelas dibuat.
    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    // Memvalidasi lalu menyimpan kelas baru dengan query berparameter.
    public function tambahKelas(string $nama_kelas, int $kapasitas): bool {
        // Merapikan spasi pada nama kelas sebelum validasi dan penyimpanan.
        $nama_kelas = trim($nama_kelas);

        // Menolak nama kosong atau kapasitas yang kurang dari satu.
        if ($nama_kelas === '' || $kapasitas < 1) {
            throw new InvalidArgumentException(
                "Nama kelas wajib diisi dan kapasitas minimal 1."
            );
        }

        // Menyiapkan query untuk menambahkan kelas baru secara aman.
        $query = $this->db->prepare(
            "INSERT INTO kelas (nama_kelas, kapasitas)
            VALUES (?, ?)"
        );

        // Mengikat nilai ke parameter query lalu menjalankan penyimpanan.
        $query->bind_param("si", $nama_kelas, $kapasitas);

        return $query->execute();
    }

    // Memperbarui data kelas jika kapasitas baru tidak di bawah jumlah pendaftar.
    public function editKelas(int $id, string $nama_kelas, int $kapasitas): bool {
        // Merapikan nama kelas sebelum memeriksa input.
        $nama_kelas = trim($nama_kelas);

        // Memastikan ID, nama kelas, dan kapasitas memiliki nilai yang valid.
        if ($id < 1 || $nama_kelas === '' || $kapasitas < 1) {
            throw new InvalidArgumentException(
                "Data kelas tidak valid."
            );
        }

        // Memperbarui kelas hanya jika kapasitas mencukupi pendaftar saat ini.
        $query = $this->db->prepare(
            "UPDATE kelas
            SET nama_kelas = ?, kapasitas = ?
            WHERE id = ?
            AND ? >= (
                SELECT COUNT(*)
                FROM pendaftaran
                WHERE kelas_id = ?
            )"
        );

        $query->bind_param(
            "siiii",
            $nama_kelas,
            $kapasitas,
            $id,
            $kapasitas,
            $id
        );

        // Menjalankan pembaruan; query berikutnya memeriksa keberadaan kelas.
        $query->execute();

        // Periksa keberadaan kelas agar perubahan yang tidak
        // mengubah nilai tetap dianggap berhasil.
        $query = $this->db->prepare(
            "SELECT id FROM kelas WHERE id = ?"
        );
        $query->bind_param("i", $id);
        $query->execute();

        return $query->get_result()->num_rows > 0;
    }

    // Menghapus kelas berdasarkan ID dan melaporkan apakah ada baris terhapus.
    public function hapusKelas(int $id): bool
    {
        // Menyiapkan query penghapusan dengan parameter ID.
        $query = $this->db->prepare(
            "DELETE FROM kelas WHERE id = ?"
        );

        // Menjalankan penghapusan dan memeriksa jumlah baris yang terpengaruh.
        $query->bind_param("i", $id);
        $query->execute();

        return $query->affected_rows > 0;
    }

    // Mengambil semua kelas beserta jumlah pendaftar dan sisa kursinya.
    public function tampilkanKelas(): array
    {
        // LEFT JOIN menjaga kelas tanpa pendaftar tetap muncul dalam hasil.
        $sql = "SELECT
                    k.id,
                    k.nama_kelas,
                    k.kapasitas,
                    COUNT(p.id) AS jumlah_daftar,
                    k.kapasitas - COUNT(p.id) AS sisa_kursi
                FROM kelas k
                LEFT JOIN pendaftaran p
                    ON p.kelas_id = k.id
                GROUP BY
                    k.id, k.nama_kelas, k.kapasitas
                ORDER BY k.id DESC";

        // Menjalankan query dan mengembalikan baris sebagai array asosiatif.
        $result = $this->db->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
