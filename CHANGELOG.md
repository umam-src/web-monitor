# Catatan Perubahan

Mengikuti Keep a Changelog dan Semantic Versioning.

## [Belum dirilis]

### Ditambahkan
- Workflow CI pada push ke main dan pemicu manual untuk memeriksa sintaks PHP 7.4 serta penerapan skema pada MySQL 8.0.

### Diubah
- Roadmap TODO.md menetapkan target versi MINOR per fase dari 0.1.0 sampai 0.6.0 serta kriteria penerimaan.
- PHP 7.4 dipertahankan sebagai target runtime selama lingkungan Synology yang dituju masih mendukungnya; migrasi ditinjau kembali jika dukungan Synology berakhir.

### Keamanan
- Dasbor belum memiliki autentikasi; jangan dipublikasikan ke internet.
- PHP 7.4 tidak lagi mendapat dukungan keamanan resmi upstream; mitigasi risiko tetap diperlukan.

Belum ada rilis resmi dan kerangka belum diuji pada runtime PHP.
