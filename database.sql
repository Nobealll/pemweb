
CREATE DATABASE pendaftaran_kelas;
USE pendaftaran_kelas;

CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL,
    jumlah_daftar INT NOT NULL DEFAULT 0
);

CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL
);

CREATE TABLE pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    kelas_id INT NOT NULL,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (mahasiswa_id, kelas_id),

    FOREIGN KEY (mahasiswa_id)
        REFERENCES mahasiswa(id) ON DELETE CASCADE,

    FOREIGN KEY (kelas_id)
        REFERENCES kelas(id) ON DELETE CASCADE
);

INSERT INTO kelas (nama_kelas, kapasitas)
VALUES
('Pemrograman Web', 2),
('Basis Data', 3),
('Pemrograman Berorientasi Objek', 2);

INSERT INTO mahasiswa (nama)
VALUES
('Andi'),
('Budi'),
('Citra');
