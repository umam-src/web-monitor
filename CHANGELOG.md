# Catatan Perubahan

Mengikuti Keep a Changelog dan Semantic Versioning.

## [Belum dirilis]

### Ditambahkan
- Tampilan riwayat hingga 20 pemeriksaan terbaru per situs, diakses dari daftar situs.
- Workflow GitHub Actions untuk membuat tag dan GitHub Release dari bagian versi bertanggal di CHANGELOG.md.
- Workflow CI pada push ke main dan pemicu manual untuk memeriksa sintaks PHP 7.4 serta penerapan skema pada MySQL 8.0.

### Diubah
- Roadmap TODO.md menetapkan target versi MINOR per fase dari 0.1.0 sampai 0.6.0 serta kriteria penerimaan.
- PHP 7.4 dipertahankan sebagai target runtime selama lingkungan Synology yang dituju masih mendukungnya; migrasi ditinjau kembali jika dukungan Synology berakhir.

### Keamanan
- Dasbor belum memiliki autentikasi; jangan dipublikasikan ke internet.
- PHP 7.4 tidak lagi mendapat dukungan keamanan resmi upstream; mitigasi risiko tetap diperlukan.

Belum ada rilis resmi; runtime PHP 7.4 pada Synology belum diuji.
