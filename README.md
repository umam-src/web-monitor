# Web Monitor

Aplikasi PHP 7.4 untuk mengelola daftar situs HTTP/HTTPS, menjalankan pemeriksaan manual, dan menyimpan riwayat pemeriksaan.

**Status:** sebagian fungsi Fase 1 sudah diimplementasikan, tetapi Fase 0 belum diterima. Belum siap untuk penggunaan publik: belum ada autentikasi, mitigasi SSRF/DNS rebinding belum lengkap, dan pengujian runtime pada Synology belum dilakukan.

## Fitur yang sudah ada

- Tambah, ubah, aktifkan/nonaktifkan, dan hapus situs.
- Pemeriksaan HTTP/HTTPS manual melalui cURL dengan batas waktu, verifikasi TLS, dan pengalihan dimatikan.
- Penyimpanan hasil pemeriksaan dan tampilan hingga 20 hasil terbaru per situs.
- Kueri basis data terparameterisasi, escaping keluaran HTML, dan token CSRF untuk formulir POST.
- Skema MySQL/MariaDB di `database/schema.sql` dan contoh konfigurasi di `pengaturan.contoh.php`.
- CI GitHub Actions yang memeriksa sintaks PHP 7.4 dan penerapan skema pada MySQL 8.0.

**Batasan keamanan:** validasi URL saat ini hanya mitigasi SSRF awal. Resolusi DNS belum dikunci ke alamat IP yang sama saat koneksi, sehingga risiko DNS rebinding belum tertutup. Dasbor tidak memiliki autentikasi. Jangan mengekspos aplikasi ke internet.

## Persyaratan

- PHP 7.4.
- Ekstensi PHP: PDO, PDO MySQL, cURL, dan OpenSSL.
- MySQL/MariaDB dan server web yang dikonfigurasi untuk menjalankan PHP.
- Akses ke log server untuk diagnosis.

Pertahankan PHP 7.4 selama Synology target masih mendukungnya. PHP 7.4 sudah tidak mendapat dukungan keamanan resmi upstream, sehingga akses aplikasi harus dibatasi dan risiko dimitigasi. Jangan menganggap kompatibilitas runtime sudah terverifikasi sebelum diuji pada model Synology dan versi DSM yang dituju.

## Pemasangan awal untuk lingkungan terlindungi

1. Buat basis data MySQL/MariaDB bernama `web_monitor`.
2. Jalankan `database/schema.sql` pada basis data tersebut.
3. Salin `pengaturan.contoh.php` menjadi `pengaturan.php`, lalu isi DSN, nama pengguna, dan rahasia basis data.
4. Pastikan `pengaturan.php` tidak masuk ke Git; berkas ini tercantum di `.gitignore`.
5. Arahkan server web ke direktori aplikasi hanya pada lingkungan pengembangan yang terlindungi.
6. Pastikan PHP 7.4 dan semua ekstensi yang dibutuhkan aktif.
7. Buka aplikasi dan uji alur tambah situs serta pemeriksaan manual hanya terhadap alamat publik yang aman.

Langkah ini belum dianggap sebagai prosedur pemasangan terverifikasi. Gate Fase 0 mensyaratkan pemasangan dari kondisi bersih pada perangkat target atau lingkungan setara yang didokumentasikan. Jangan gunakan data produksi sebelum prosedur itu berhasil diuji.

## Status fase dan dokumen

- [BLOCKERS.md](BLOCKERS.md): blocker dan gate wajib sebelum menerima fase.
- [TODO.md](TODO.md): roadmap dan kriteria penerimaan.
- [CHANGELOG.md](CHANGELOG.md): catatan perubahan.
- [CONTRIBUTING.md](CONTRIBUTING.md): aturan kerja, pengujian, dan rilis.
- [GLOSARIUM.md](GLOSARIUM.md): istilah baku dan penamaan.

Fase 0 masih terblokir sampai detail Synology target, kebutuhan paket, pemeriksaan rahasia repositori, dan pemasangan bersih diverifikasi. Uji runtime PHP 7.4 dan penyelesaian mitigasi SSRF juga wajib sebelum Fase 1 diterima.
