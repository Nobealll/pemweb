<?php

class Mahasiswa
{
    public string $id;
    public string $nama;

    public function __construct(string $id, string $nama)
    {
        $this->id = trim($id);
        $this->nama = trim($nama);

        if ($this->id === '' || mb_strlen($this->id) > 50) {
            throw new InvalidArgumentException('ID mahasiswa wajib diisi dan maksimal 50 karakter.');
        }
        if ($this->nama === '' || mb_strlen($this->nama) > 150) {
            throw new InvalidArgumentException('Nama mahasiswa wajib diisi dan maksimal 150 karakter.');
        }
    }

    public function daftarKelas(Kelas $kelas, Pendaftaran $pendaftaran): void
    {
        $pendaftaran->daftarMahasiswaKeKelas($this, $kelas);
    }

    public function batalDaftar(Kelas $kelas, Pendaftaran $pendaftaran): void
    {
        $pendaftaran->batalkanPendaftaran($this, $kelas);
    }
}
