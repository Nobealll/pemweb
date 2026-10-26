
<?php

class Mahasiswa
{
    private mysqli $db;

    public int $id;
    public string $nama;

    public function __construct(
        mysqli $db,
        int $id,
        string $nama
    ) {
        $this->db = $db;
        $this->id = $id;
        $this->nama = $nama;
    }

    public function daftarKelas(int $kelas_id): bool
    {
        $pendaftaran = new Pendaftaran($this->db);

        return $pendaftaran->daftarMahasiswaKeKelas(
            $this,
            $kelas_id
        );
    }

    public function batalDaftar(int $kelas_id): bool
    {
        $pendaftaran = new Pendaftaran($this->db);

        return $pendaftaran->batalkanPendaftaran(
            $this,
            $kelas_id
        );
    }
}
