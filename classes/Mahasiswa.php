
<?php

// Merepresentasikan mahasiswa dan menyediakan pintasan untuk mengelola pendaftarannya.
class Mahasiswa
{
    // Koneksi database yang diteruskan ke pengelola pendaftaran.
    private mysqli $db;

    // Data identitas mahasiswa yang dipakai pada proses pendaftaran.
    public int $id;
    public string $nama;

    // Menginisialisasi koneksi database dan identitas mahasiswa.
    public function __construct(
        mysqli $db,
        int $id,
        string $nama
    ) {
        $this->db = $db;
        $this->id = $id;
        $this->nama = $nama;
    }

    // Mendaftarkan mahasiswa ini ke kelas melalui kelas Pendaftaran.
    public function daftarKelas(int $kelas_id): bool
    {
        // Membuat pengelola pendaftaran dengan koneksi database yang sama.
        $pendaftaran = new Pendaftaran($this->db);

        // Meneruskan objek mahasiswa dan ID kelas untuk diproses.
        return $pendaftaran->daftarMahasiswaKeKelas(
            $this,
            $kelas_id
        );
    }

    // Membatalkan pendaftaran mahasiswa ini dari kelas tertentu.
    public function batalDaftar(int $kelas_id): bool
    {
        // Membuat pengelola pendaftaran dengan koneksi database yang sama.
        $pendaftaran = new Pendaftaran($this->db);

        // Meneruskan objek mahasiswa dan ID kelas untuk pembatalan.
        return $pendaftaran->batalkanPendaftaran(
            $this,
            $kelas_id
        );
    }
}
