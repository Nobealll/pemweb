<?php

session_start();

require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/classes/Kelas.php';
require_once __DIR__ . '/classes/Mahasiswa.php';
require_once __DIR__ . '/classes/Pendaftaran.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$kelasModel = new Kelas($koneksi);
$pendaftaranModel = new Pendaftaran($koneksi);
$pesan = '';
$jenisPesan = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            throw new RuntimeException('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
        }

        $aksi = $_POST['aksi'] ?? '';
        switch ($aksi) {
            case 'tambah_kelas':
                $kelasModel->tambahKelas(
                    trim((string) ($_POST['nama_kelas'] ?? '')),
                    filter_var($_POST['kapasitas'] ?? null, FILTER_VALIDATE_INT) ?: 0
                );
                $pesan = 'Kelas berhasil ditambahkan.';
                break;

            case 'ubah_kelas':
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                if (!$id || $id < 1) {
                    throw new InvalidArgumentException('ID kelas tidak valid.');
                }
                $kelasModel->ubahKelas(
                    $id,
                    trim((string) ($_POST['nama_kelas'] ?? '')),
                    filter_var($_POST['kapasitas'] ?? null, FILTER_VALIDATE_INT) ?: 0
                );
                $pesan = 'Kelas berhasil diperbarui.';
                break;

            case 'hapus_kelas':
                $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
                if (!$id || $id < 1) {
                    throw new InvalidArgumentException('ID kelas tidak valid.');
                }
                $kelasModel->hapusKelas($id);
                $pesan = 'Kelas dan seluruh pendaftarannya berhasil dihapus.';
                break;

            case 'daftar':
                $mahasiswa = new Mahasiswa(
                    (string) ($_POST['mahasiswa_id'] ?? ''),
                    (string) ($_POST['nama_mahasiswa'] ?? '')
                );
                $kelasId = filter_var($_POST['kelas_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$kelasId || $kelasId < 1) {
                    throw new InvalidArgumentException('Kelas yang dipilih tidak valid.');
                }
                $kelasTerpilih = new Kelas($koneksi, $kelasId);
                $mahasiswa->daftarKelas($kelasTerpilih, $pendaftaranModel);
                $pesan = 'Pendaftaran kelas berhasil.';
                break;

            case 'batal_daftar':
                $mahasiswa = new Mahasiswa((string) ($_POST['mahasiswa_id'] ?? ''), 'Mahasiswa');
                $kelasId = filter_var($_POST['kelas_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$kelasId || $kelasId < 1) {
                    throw new InvalidArgumentException('Kelas yang dipilih tidak valid.');
                }
                $kelasTerpilih = new Kelas($koneksi, $kelasId);
                $mahasiswa->batalDaftar($kelasTerpilih, $pendaftaranModel);
                $pesan = 'Pendaftaran berhasil dibatalkan.';
                break;

            default:
                throw new InvalidArgumentException('Aksi tidak dikenal.');
        }
    } catch (InvalidArgumentException | RuntimeException $error) {
        $pesan = $error->getMessage();
        $jenisPesan = 'error';
    }

    $_SESSION['flash'] = ['pesan' => $pesan, 'jenis' => $jenisPesan];
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['flash'])) {
    $pesan = $_SESSION['flash']['pesan'];
    $jenisPesan = $_SESSION['flash']['jenis'];
    unset($_SESSION['flash']);
}

$daftarKelas = $kelasModel->tampilkanKelas();
$kelasEdit = null;
if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId && $editId > 0) {
        $kelasEdit = $kelasModel->cariById($editId);
    }
}

require __DIR__ . '/views/kelas.php';
