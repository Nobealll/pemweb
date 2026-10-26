
<?php

class Kelas
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function tambahKelas(string $nama_kelas, int $kapasitas): bool {
        $nama_kelas = trim($nama_kelas);

        if ($nama_kelas === '' || $kapasitas < 1) {
            throw new InvalidArgumentException(
                "Nama kelas wajib diisi dan kapasitas minimal 1."
            );
        }

        $query = $this->db->prepare(
            "INSERT INTO kelas (nama_kelas, kapasitas)
            VALUES (?, ?)"
        );

        $query->bind_param("si", $nama_kelas, $kapasitas);

        return $query->execute();
    }

    public function editKelas(int $id, string $nama_kelas, int $kapasitas): bool {
        $nama_kelas = trim($nama_kelas);

        if ($id < 1 || $nama_kelas === '' || $kapasitas < 1) {
            throw new InvalidArgumentException(
                "Data kelas tidak valid."
            );
        }

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

    public function hapusKelas(int $id): bool
    {
        $query = $this->db->prepare(
            "DELETE FROM kelas WHERE id = ?"
        );

        $query->bind_param("i", $id);
        $query->execute();

        return $query->affected_rows > 0;
    }

    public function tampilkanKelas(): array
    {
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

        $result = $this->db->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
