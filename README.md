# Web Monitor

Aplikasi web PHP 7.4 untuk memantau ketersediaan situs melalui HTTP/HTTPS, menyimpan riwayat pemeriksaan, dan membantu mengenali gangguan seperti halaman kesalahan Cloudflare.

**Status:** kerangka awal dan dokumentasi tersedia; pemeriksa otomatis belum diimplementasikan dan aplikasi belum siap untuk penggunaan publik.

## Yang tersedia
- Dasbor baca-saja untuk melihat situs dan hasil pemeriksaan terakhir dari basis data.
- Skema awal basis data pada database/schema.sql.
- Contoh pengaturan lokal pada pengaturan.contoh.php.
- Roadmap, catatan perubahan, panduan kontribusi, dan glosarium.
- CI dasar untuk sintaks PHP 7.4 dan penerapan skema basis data.

## Persyaratan awal
PHP 7.4, PDO, PDO MySQL, MySQL/MariaDB, server web, dan cron untuk tahap pemeriksaan terjadwal.

Kompatibilitas: pertahankan PHP 7.4 selama Synology target masih mendukungnya. PHP 7.4 sudah tidak mendapat dukungan keamanan resmi upstream, sehingga akses harus dibatasi dan risiko keamanan dimitigasi. Dasbor belum memiliki autentikasi; jangan publikasikan ke internet.

## Memulai
1. Buat basis data MySQL/MariaDB bernama web_monitor.
2. Jalankan database/schema.sql pada basis data tersebut.
3. Salin pengaturan.contoh.php menjadi pengaturan.php dan isi DSN, nama pengguna, serta rahasia basis data.
4. Arahkan server web ke direktori proyek hanya di lingkungan pengembangan yang terlindungi.
5. Pastikan PHP 7.4 dan ekstensi PDO MySQL tersedia.

Kerangka belum diuji pada runtime PHP; petunjuk ini adalah langkah awal dan perlu diverifikasi pada lingkungan sasaran.

## Dokumen
- [TODO.md](TODO.md): roadmap dan kriteria selesai.
- [CHANGELOG.md](CHANGELOG.md): catatan perubahan dengan Keep a Changelog dan Semantic Versioning.
- [CONTRIBUTING.md](CONTRIBUTING.md): aturan kontribusi dan pengujian.
- [GLOSARIUM.md](GLOSARIUM.md): istilah baku dan penamaan.
