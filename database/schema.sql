CREATE TABLE situs_pantauan (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama VARCHAR(150) NOT NULL,
    alamat_url VARCHAR(2048) NOT NULL,
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    interval_detik INT UNSIGNED NOT NULL DEFAULT 300,
    dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_situs_pantauan_alamat_url (alamat_url(191)),
    KEY idx_situs_pantauan_aktif (aktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE riwayat_pemeriksaan (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    situs_id BIGINT UNSIGNED NOT NULL,
    status ENUM('aktif', 'gangguan', 'tidak_diketahui') NOT NULL DEFAULT 'tidak_diketahui',
    kode_http SMALLINT UNSIGNED NULL,
    waktu_respons_ms INT UNSIGNED NULL,
    pesan_kesalahan VARCHAR(500) NULL,
    diperiksa_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_riwayat_situs_waktu (situs_id, diperiksa_pada),
    CONSTRAINT fk_riwayat_pemeriksaan_situs
        FOREIGN KEY (situs_id) REFERENCES situs_pantauan(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
