<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pendaftaran_kelas';

$koneksi = mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');