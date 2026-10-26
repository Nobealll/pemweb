<?php
$e = static fn ($nilai): string => htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
$csrf = $e($_SESSION['csrf_token']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Kelas</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="container">
    <header class="page-header">
        <div>
            <p class="eyebrow">Portal Akademik</p>
            <h1>Sistem Pendaftaran Kelas</h1>
            <p>Kelola kelas dan pendaftaran mahasiswa dalam satu tempat.</p>
        </div>
    </header>

    <?php if ($pesan !== ''): ?>
        <div class="alert <?= $e($jenisPesan) ?>" role="status"><?= $e($pesan) ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2><?= $kelasEdit ? 'Edit kelas' : 'Tambah kelas' ?></h2>
        <form method="post" action="index.php" class="form-grid">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="aksi" value="<?= $kelasEdit ? 'ubah_kelas' : 'tambah_kelas' ?>">
            <?php if ($kelasEdit): ?>
                <input type="hidden" name="id" value="<?= $e($kelasEdit['id']) ?>">
            <?php endif; ?>
            <label>
                Nama kelas
                <input name="nama_kelas" type="text" maxlength="150" required
                    value="<?= $e($kelasEdit['nama_kelas'] ?? '') ?>">
            </label>
            <label>
                Kapasitas mahasiswa
                <input name="kapasitas" type="number" min="1" max="100000" required
                    value="<?= $e($kelasEdit['kapasitas'] ?? '') ?>">
            </label>
            <div class="form-actions">
                <button type="submit"><?= $kelasEdit ? 'Simpan perubahan' : 'Tambah kelas' ?></button>
                <?php if ($kelasEdit): ?>
                    <a class="button secondary" href="index.php">Batal edit</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="section-heading">
            <div>
                <h2>Daftar kelas</h2>
                <p><?= count($daftarKelas) ?> kelas tersedia</p>
            </div>
        </div>
        <?php if ($daftarKelas === []): ?>
            <p class="empty-state">Belum ada kelas. Tambahkan kelas menggunakan formulir di atas.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Kapasitas</th>
                        <th>Terdaftar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($daftarKelas as $kelas): ?>
                        <tr>
                            <td class="class-name"><?= $e($kelas['nama_kelas']) ?></td>
                            <td><?= $e($kelas['kapasitas']) ?></td>
                            <td><?= $e($kelas['jumlah_daftar']) ?></td>
                            <td>
                                <?php if ($kelas['jumlah_daftar'] >= $kelas['kapasitas']): ?>
                                    <span class="badge full">Penuh</span>
                                <?php else: ?>
                                    <span class="badge available">Tersedia</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="button small secondary" href="index.php?edit=<?= $e($kelas['id']) ?>">Edit</a>
                                    <form method="post" action="index.php" onsubmit="return confirm('Hapus kelas beserta seluruh pendaftarannya?')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="aksi" value="hapus_kelas">
                                        <input type="hidden" name="id" value="<?= $e($kelas['id']) ?>">
                                        <button class="small danger" type="submit">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <div class="two-columns">
        <section class="panel">
            <h2>Daftar kelas</h2>
            <p class="muted">Masukkan ID dan nama sesuai data mahasiswa. ID yang sama harus menggunakan nama yang sama.</p>
            <form method="post" action="index.php" class="stacked-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="aksi" value="daftar">
                <label>
                    ID mahasiswa
                    <input name="mahasiswa_id" type="text" maxlength="50" required>
                </label>
                <label>
                    Nama mahasiswa
                    <input name="nama_mahasiswa" type="text" maxlength="150" required>
                </label>
                <label>
                    Pilih kelas
                    <select name="kelas_id" required>
                        <option value="">Pilih kelas yang tersedia</option>
                        <?php foreach ($daftarKelas as $kelas): ?>
                            <?php if ($kelas['jumlah_daftar'] < $kelas['kapasitas']): ?>
                                <option value="<?= $e($kelas['id']) ?>">
                                    <?= $e($kelas['nama_kelas']) ?> (sisa <?= $e($kelas['kapasitas'] - $kelas['jumlah_daftar']) ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit">Daftar ke kelas</button>
            </form>
        </section>

        <section class="panel">
            <h2>Batalkan pendaftaran</h2>
            <p class="muted">Gunakan ID mahasiswa yang digunakan saat mendaftar.</p>
            <form method="post" action="index.php" class="stacked-form">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="aksi" value="batal_daftar">
                <label>
                    ID mahasiswa
                    <input name="mahasiswa_id" type="text" maxlength="50" required>
                </label>
                <label>
                    Kelas
                    <select name="kelas_id" required>
                        <option value="">Pilih kelas</option>
                        <?php foreach ($daftarKelas as $kelas): ?>
                            <option value="<?= $e($kelas['id']) ?>"><?= $e($kelas['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="secondary" type="submit">Batalkan pendaftaran</button>
            </form>
        </section>
    </div>

    <footer>Data kelas dan pendaftaran dikelola melalui database MySQL.</footer>
</main>
</body>
</html>
