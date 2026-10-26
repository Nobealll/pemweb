
<?php

session_start();

include_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/classes/Kelas.php';
require_once __DIR__ . '/classes/Mahasiswa.php';
require_once __DIR__ . '/classes/Pendaftaran.php';

function kembali(string $pesan, string $tipe = 'success'): never
{
    $_SESSION['pesan'] = $pesan;
    $_SESSION['tipe'] = $tipe;

    header('Location: views/index.php');
    exit;
}

try {
    $aksi = $_POST['aksi'] ?? '';
    $kelasModel = new Kelas($koneksi);

    // CRUD kelas.
    if (in_array($aksi, ['tambah', 'edit'], true)) {
        $nama = trim($_POST['nama_kelas'] ?? '');

        $kapasitas = filter_var(
            $_POST['kapasitas'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $nama === '' ||
            $kapasitas === false ||
            $kapasitas < 1
        ) {
            throw new InvalidArgumentException(
                "Nama kelas dan kapasitas harus valid."
            );
        }

        if ($aksi === 'tambah') {
            $kelasModel->tambahKelas($nama, $kapasitas);
            kembali("Kelas berhasil ditambahkan.");
        }

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || !$kelasModel->editKelas($id, $nama, $kapasitas)) {
            throw new RuntimeException(
                "Kelas tidak ditemukan atau kapasitas tidak valid."
            );
        }

        kembali("Kelas berhasil diperbarui.");
    }

    if ($aksi === 'hapus') {
        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || !$kelasModel->hapusKelas($id)) {
            throw new RuntimeException(
                "Kelas tidak ditemukan."
            );
        }

        kembali("Kelas berhasil dihapus.");
    }

    // Pendaftaran dan pembatalan.
    if (in_array($aksi, ['daftar', 'batal'], true)) {
        $mahasiswa_id = filter_var(
            $_POST['mahasiswa_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $kelas_id = filter_var(
            $_POST['kelas_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            !$mahasiswa_id || $mahasiswa_id < 1 ||
            !$kelas_id || $kelas_id < 1
        ) {
            throw new InvalidArgumentException(
                "Pilih mahasiswa dan kelas yang valid."
            );
        }

        $stmt = $koneksi->prepare(
            "SELECT id, nama FROM mahasiswa WHERE id = ?"
        );
        $stmt->bind_param("i", $mahasiswa_id);
        $stmt->execute();

        $data = $stmt->get_result()->fetch_assoc();

        if (!$data) {
            throw new RuntimeException(
                "Mahasiswa tidak ditemukan."
            );
        }

        $mahasiswa = new Mahasiswa(
            $koneksi,
            (int) $data['id'],
            $data['nama']
        );

        if ($aksi === 'daftar') {
            $mahasiswa->daftarKelas($kelas_id);
            kembali("Berhasil mendaftar ke kelas.");
        }

        $mahasiswa->batalDaftar($kelas_id);
        kembali("Pendaftaran berhasil dibatalkan.");
    }

    throw new InvalidArgumentException(
        "Aksi tidak dikenal."
    );

} catch (Throwable $e) {
    // Catat detail teknis pada log server.
    error_log($e->__toString());

    $pesan = $e instanceof RuntimeException ||
            $e instanceof InvalidArgumentException
        ? $e->getMessage()
        : "Terjadi kesalahan pada sistem.";

    kembali($pesan, 'error');
}
