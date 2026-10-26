<?php
// Memulai sesi untuk membaca pesan notifikasi dari proses sebelumnya.
session_start();

// Memuat koneksi database dan model yang digunakan halaman.
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../classes/Kelas.php';
require_once __DIR__ . '/../classes/Mahasiswa.php';
require_once __DIR__ . '/../classes/Pendaftaran.php';

// Mengambil data kelas beserta informasi jumlah pendaftar dan kursi tersisa.
$kelasModel = new Kelas($koneksi);
$daftarKelas = $kelasModel->tampilkanKelas();

// Mengambil daftar mahasiswa untuk pilihan pada formulir pendaftaran.
$mahasiswa = $koneksi->query(
    "SELECT id, nama FROM mahasiswa ORDER BY nama"
)->fetch_all(MYSQLI_ASSOC);

// Mengambil daftar mahasiswa dan kelas yang sudah terhubung melalui pendaftaran.
$hasil = $koneksi->query(
    "SELECT p.mahasiswa_id, p.kelas_id,
            m.nama, k.nama_kelas
    FROM pendaftaran p
    JOIN mahasiswa m ON m.id = p.mahasiswa_id
    JOIN kelas k ON k.id = p.kelas_id
    ORDER BY m.nama, k.nama_kelas"
)->fetch_all(MYSQLI_ASSOC);

// Membaca notifikasi sesi lalu menghapusnya agar hanya tampil satu kali.
$pesan = $_SESSION['pesan'] ?? '';
$tipe = $_SESSION['tipe'] ?? 'success';
unset($_SESSION['pesan'], $_SESSION['tipe']);

// Mengamankan nilai sebelum ditampilkan ke dalam HTML.
function e(mixed $nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <!-- Mengatur metadata halaman, judul, dan stylesheet eksternal. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Pendaftaran Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* Menyesuaikan warna utama Bootstrap dengan tema oranye halaman. */
        :root {
            --bs-primary: #fd7e14;
            --bs-primary-rgb: 253, 126, 20;
            --bs-link-color: #fd7e14;
            --bs-link-hover-color: #df6a0c;
        }

        /* Menentukan warna dasar dan tampilan latar halaman. */
        body {
            background: #f8fafc;
            color: #1f2937;
        }

        /* Membatasi lebar konten utama serta memberi gaya pada header. */
        .page-shell {
            max-width: 1100px;
        }

        .topbar {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
        }

        /* Menyamakan gaya kartu dan tabel agar sesuai dengan tampilan halaman. */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: none;
            background: #fff;
        }

        .table thead th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            white-space: nowrap;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Merapikan jarak sel tabel dan sudut elemen formulir serta tombol. */
        .table td,
        .table th {
            border-color: #edf2f7;
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
        }

        .form-control,
        .form-select {
            border-color: #dfe7f1;
            border-radius: 0.75rem;
        }

        .btn {
            border-radius: 0.75rem;
        }

        /* Mengatur konsistensi warna tombol dan elemen primer Bootstrap. */
        .text-primary,
        .btn-primary,
        .bg-primary,
        .badge,
        .btn-outline-primary {
            --bs-btn-bg: #fd7e14;
            --bs-btn-border-color: #fd7e14;
            --bs-btn-hover-bg: #df6a0c;
            --bs-btn-hover-border-color: #df6a0c;
            --bs-btn-active-bg: #c95e09;
            --bs-btn-active-border-color: #c95e09;
            --bs-btn-color: #fff;
            --bs-btn-hover-color: #fff;
            --bs-btn-active-color: #fff;
            color: #fd7e14 !important;
        }

        /* Memberi warna isian untuk tombol primer, badge, dan latar primer. */
        .btn-primary,
        .bg-primary,
        .badge {
            background-color: #fd7e14 !important;
            border-color: #fd7e14 !important;
            color: #fff !important;
        }

        /* Menentukan tampilan tombol primer versi outline saat normal dan aktif. */
        .btn-outline-primary {
            color: #fd7e14 !important;
            border-color: #fd7e14 !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus,
        .btn-outline-primary:active {
            background-color: #fd7e14 !important;
            border-color: #fd7e14 !important;
            color: #fff !important;
        }
    </style>
</head>

<body>
    <main class="container page-shell py-4 py-lg-5">
        <!-- Menampilkan identitas dan ringkasan aplikasi pada bagian atas. -->
        <header class="topbard-flex align-items-center gap-3 mb-4">
            <div class="bg-light rounded-3 p-3 fs-4 text-primary"><i class="bi bi-mortarboard-fill"></i></div>
            <div>
                <h1 class="h3 fw-bold mb-1">Sistem Pendaftaran Kelas</h1>
                <p class="mb-0 text-secondary">Pengelolaan Kelas dan Pendaftaran Mahasiswa</p>
            </div>
        </header>

        <!-- Menampilkan notifikasi hasil operasi jika tersedia dari sesi. -->
        <?php if ($pesan !== ''): ?>
            <?php $alertType = $tipe === 'error' ? 'danger' : $tipe; ?>
            <div class="alert alert-<?= e($alertType) ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= e($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <!-- Menyediakan tombol untuk membuka formulir penambahan kelas. -->
        <section class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3"><i class="bi bi-plus-circle text-primary me-2"></i>Tambah Kelas</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Kelas
                </button>
            </div>
        </section>

        <!-- Modal untuk mengirim data kelas baru ke proses.php. -->
        <div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-labelledby="judulTambahKelas" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="../proses.php" method="POST">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="judulTambahKelas">Tambah Kelas</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="aksi" value="tambah">
                            <div class="mb-3">
                                <label for="nama-kelas" class="form-label">Nama kelas</label>
                                <input id="nama-kelas" type="text" name="nama_kelas" class="form-control" maxlength="100" required>
                            </div>
                            <div>
                                <label for="kapasitas" class="form-label">Kapasitas</label>
                                <input id="kapasitas" type="number" name="kapasitas" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Tambah Kelas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Menampilkan daftar kelas, kapasitas, jumlah pendaftar, dan sisa kursi. -->
        <section class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3"><i class="bi bi-collection text-primary me-2"></i>Daftar Kelas</h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Kelas</th>
                                <th>Kapasitas</th>
                                <th>Terdaftar</th>
                                <th>Sisa Kursi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Membuat satu baris tabel untuk setiap kelas. -->
                            <?php foreach ($daftarKelas as $k): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($k['nama_kelas']) ?></td>
                                    <td><?= e($k['kapasitas']) ?></td>
                                    <td><?= e($k['jumlah_daftar']) ?></td>
                                    <td><span class="badge text-bg-primary rounded-pill"><?= e($k['sisa_kursi']) ?> kursi</span></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditKelas<?= e($k['id']) ?>">Edit</button>
                                            <form action="../proses.php" method="POST" onsubmit="return confirm('Hapus kelas ini?')">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id" value="<?= e($k['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Menampilkan informasi ketika belum ada kelas. -->
                            <?php if (empty($daftarKelas)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-secondary py-4">Belum ada kelas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Membuat modal edit terpisah untuk setiap kelas yang tersedia. -->
        <?php foreach ($daftarKelas as $k): ?>
            <div class="modal fade" id="modalEditKelas<?= e($k['id']) ?>" tabindex="-1" aria-labelledby="judulEditKelas<?= e($k['id']) ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="../proses.php" method="POST">
                            <div class="modal-header">
                                <h2 class="modal-title fs-5" id="judulEditKelas<?= e($k['id']) ?>">Edit Kelas</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="aksi" value="edit">
                                <input type="hidden" name="id" value="<?= e($k['id']) ?>">
                                <div class="mb-3">
                                    <label for="nama-kelas-<?= e($k['id']) ?>" class="form-label">Nama kelas</label>
                                    <input id="nama-kelas-<?= e($k['id']) ?>" type="text" name="nama_kelas" class="form-control" value="<?= e($k['nama_kelas']) ?>" maxlength="100" required>
                                </div>
                                <div>
                                    <label for="kapasitas-<?= e($k['id']) ?>" class="form-label">Kapasitas</label>
                                    <input id="kapasitas-<?= e($k['id']) ?>" type="number" name="kapasitas" class="form-control" value="<?= e($k['kapasitas']) ?>" min="1" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Menyediakan tombol untuk membuka formulir pendaftaran mahasiswa. -->
        <section class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3"><i class="bi bi-person-plus text-primary me-2"></i>Pendaftaran Mahasiswa</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPendaftaran">
                    <i class="bi bi-person-plus me-1"></i>Input Pendaftaran
                </button>
            </div>
        </section>

        <!-- Modal pendaftaran untuk memilih mahasiswa dan kelas tujuan. -->
        <div class="modal fade" id="modalPendaftaran" tabindex="-1" aria-labelledby="judulPendaftaran" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="../proses.php" method="POST">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="judulPendaftaran">Pendaftaran Mahasiswa</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="mahasiswa" class="form-label">Pilih mahasiswa</label>
                                <select id="mahasiswa" name="mahasiswa_id" class="form-select" required>
                                    <option value="">-- Pilih Mahasiswa --</option>
                                    <!-- Mengisi pilihan mahasiswa dari data database. -->
                                    <?php foreach ($mahasiswa as $m): ?>
                                        <option value="<?= e($m['id']) ?>"><?= e($m['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="kelas" class="form-label">Pilih kelas</label>
                                <select id="kelas" name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <!-- Menampilkan kelas dan jumlah kursi yang masih tersedia. -->
                                    <?php foreach ($daftarKelas as $k): ?>
                                        <option value="<?= e($k['id']) ?>"><?= e($k['nama_kelas']) ?> (sisa <?= e($k['sisa_kursi']) ?> kursi)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="aksi" value="daftar" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Daftar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Menampilkan seluruh relasi pendaftaran dengan aksi pembatalan. -->
        <section class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3"><i class="bi bi-people text-primary me-2"></i>Daftar Mahasiswa Terdaftar</h2>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Mahasiswa</th>
                                <th>Nama Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Membuat baris data beserta formulir pembatalan pendaftaran. -->
                            <?php foreach ($hasil as $p): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($p['nama']) ?></td>
                                    <td><?= e($p['nama_kelas']) ?></td>
                                    <td>
                                        <form action="../proses.php" method="POST">
                                            <input type="hidden" name="aksi" value="batal">
                                            <input type="hidden" name="mahasiswa_id" value="<?= e($p['mahasiswa_id']) ?>">
                                            <input type="hidden" name="kelas_id" value="<?= e($p['kelas_id']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg me-1"></i>Batalkan</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Memberi keterangan ketika belum ada pendaftaran. -->
                            <?php if (empty($hasil)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-4">Belum ada mahasiswa yang mendaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
    <!-- Memuat JavaScript Bootstrap untuk mengaktifkan modal dan notifikasi. -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>