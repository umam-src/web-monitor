# Catatan Perubahan

Mengikuti Keep a Changelog dan Semantic Versioning.

## [Belum dirilis]

### Direncanakan
- Verifikasi runtime wizard setup dan login pada NAS Synology target; masih menunggu pengujian.

### Ditambahkan
- Wizard browser `setup.php` menguji koneksi ke database yang sudah ada dan membuat tabel situs, riwayat, serta akun administrator secara otomatis tanpa phpMyAdmin.
- Login/logout administrator dasar; dashboard dan tindakan memerlukan sesi login.
- Tampilan riwayat hingga 20 pemeriksaan terbaru per situs, diakses dari daftar situs.
- Workflow GitHub Actions untuk membuat tag dan GitHub Release dari bagian versi bertanggal di CHANGELOG.md.
- Workflow CI pada push ke main dan pemicu manual untuk memeriksa sintaks PHP 7.4 serta penerapan skema pada MySQL 8.0.

### Diubah
- Opsi konfigurasi eksplisit dapat mengizinkan target intranet; secara bawaan nonaktif.
- Roadmap TODO.md menetapkan target versi MINOR per fase dari 0.1.0 sampai 0.6.0 serta kriteria penerimaan.
- PHP 7.4 dipertahankan sebagai target runtime selama lingkungan Synology yang dituju masih mendukungnya; migrasi ditinjau kembali jika dukungan Synology berakhir.

### Keamanan
- Autentikasi dasar sudah ditambahkan, tetapi jangan publikasikan ke internet sebelum audit sesi/login dan mitigasi SSRF selesai.
- PHP 7.4 tidak lagi mendapat dukungan keamanan resmi upstream; mitigasi risiko tetap diperlukan.

Belum ada rilis resmi; runtime PHP 7.4 pada Synology belum diuji.
