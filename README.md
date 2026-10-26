# Sistem Pendaftaran Kelas

Aplikasi web sederhana untuk mengelola kelas dan pendaftaran mahasiswa. Aplikasi dibuat dengan PHP, MySQL, dan Bootstrap.

## Fitur

- Menambah, mengubah, dan menghapus kelas.
- Melihat kapasitas, jumlah mahasiswa terdaftar, dan sisa kursi setiap kelas.
- Mendaftarkan mahasiswa ke kelas dan membatalkan pendaftaran.
- Mencegah pendaftaran ganda dan menolak pendaftaran saat kelas penuh.
- Memastikan kapasitas kelas tidak dapat diubah menjadi lebih kecil daripada jumlah pendaftar saat ini.

## Persyaratan

- PHP 8.1 atau lebih baru dengan ekstensi `mysqli` aktif.
- MySQL atau MariaDB.
- Web server lokal, misalnya Laragon atau Apache.
- Koneksi internet saat membuka aplikasi untuk memuat Bootstrap dan Bootstrap Icons dari CDN.

## Instalasi dengan Laragon

1. Letakkan folder proyek di direktori web Laragon, misalnya `C:\laragon\www\pemweb-crud1`.
2. Jalankan Apache dan MySQL dari Laragon.
3. Buka phpMyAdmin, lalu impor file [`database.sql`](./database.sql). Skrip tersebut membuat database `pendaftaran_kelas`, tabel-tabel yang dibutuhkan, dan beberapa data contoh.
4. Periksa pengaturan koneksi di [`koneksi.php`](./koneksi.php). Nilai bawaan:

   | Pengaturan | Nilai |
   | --- | --- |
   | Host | `localhost` |
   | Username | `root` |
   | Password | kosong |
   | Database | `pendaftaran_kelas` |

   Sesuaikan nilainya bila konfigurasi MySQL lokal berbeda.
5. Buka aplikasi di `http://localhost/pemweb-crud1/views/index.php`.

## Penggunaan

- Gunakan **Tambah Kelas** untuk membuat kelas dengan nama dan kapasitas minimal satu.
- Gunakan tombol **Edit** atau **Hapus** pada daftar kelas untuk mengelola kelas.
- Gunakan **Input Pendaftaran** untuk memilih mahasiswa dan kelas.
- Tabel **Daftar Mahasiswa Terdaftar** menampilkan pendaftaran aktif dan menyediakan tombol **Batalkan**.

Data awal mencakup tiga mahasiswa dan tiga kelas. Pengelolaan data mahasiswa tidak tersedia melalui antarmuka aplikasi.

## Struktur proyek

```text
.
├── assets/
│   └── style.css
├── classes/
│   ├── Kelas.php
│   ├── Mahasiswa.php
│   └── Pendaftaran.php
├── views/
│   └── index.php
├── database.sql
├── koneksi.php
└── proses.php
```

- `views/index.php` menampilkan halaman daftar kelas dan pendaftaran.
- `proses.php` menangani aksi formulir.
- `classes/` berisi logika kelas, mahasiswa, dan pendaftaran.
- `database.sql` membuat skema dan mengisi data contoh.
- `koneksi.php` mengatur koneksi database.
