# Web Monitor

Aplikasi PHP 7.4 untuk mengelola daftar situs HTTP/HTTPS, menjalankan pemeriksaan manual, dan menyimpan riwayat pemeriksaan.

**Status:** wizard setup browser dan login administrator awal sudah diimplementasikan di `main`; Fase 0 belum diterima sampai CI, runtime Synology, dan audit keamanan diverifikasi. Belum siap untuk penggunaan publik karena mitigasi SSRF/DNS rebinding belum lengkap.

## Fitur yang sudah ada

- Tambah, ubah, aktifkan/nonaktifkan, dan hapus situs.
- Pemeriksaan HTTP/HTTPS manual melalui cURL dengan batas waktu, verifikasi TLS, dan pengalihan dimatikan. Target lokal/intranet dapat diizinkan secara eksplisit.
- Penyimpanan hasil pemeriksaan dan tampilan hingga 20 hasil terbaru per situs.
- Kueri basis data terparameterisasi, escaping keluaran HTML, dan token CSRF untuk formulir POST.
- Wizard `setup.php` menguji koneksi MariaDB, membuat tabel secara otomatis, menyimpan konfigurasi, dan membuat administrator pertama.
- Login administrator dengan hash kata sandi; dashboard dan tindakan memerlukan sesi login.
- Skema MySQL/MariaDB di `database/schema.sql` dan contoh konfigurasi di `pengaturan.contoh.php`.
- CI GitHub Actions yang memeriksa sintaks PHP 7.4 dan penerapan skema pada MySQL 8.0.

**Batasan keamanan:** opsi target lokal/intranet memperluas akses yang dapat dilakukan server dan meningkatkan risiko SSRF. Aktifkan hanya jika diperlukan dan batasi aplikasi ke jaringan tepercaya. Validasi URL saat ini hanya mitigasi SSRF awal. Resolusi DNS belum dikunci ke alamat IP yang sama saat koneksi, sehingga risiko DNS rebinding belum tertutup. Login dasar sudah ada, tetapi belum menggantikan audit keamanan. Jangan mengekspos aplikasi ke internet.

## Persyaratan

- PHP 7.4.
- Ekstensi PHP: PDO, PDO MySQL, cURL, dan OpenSSL.
- MySQL/MariaDB dan server web yang dikonfigurasi untuk menjalankan PHP.
- Akses ke log server untuk diagnosis.

Pertahankan PHP 7.4 selama Synology target masih mendukungnya. PHP 7.4 sudah tidak mendapat dukungan keamanan resmi upstream, sehingga akses aplikasi harus dibatasi dan risiko dimitigasi. Jangan menganggap kompatibilitas runtime sudah terverifikasi sebelum diuji pada model Synology dan versi DSM yang dituju.

## Target pemasangan: Synology DSM 7.2.2

Target perangkat: **Synology DS923+ dengan DSM 7.2.2**. Target penggunaan adalah pemasangan dan konfigurasi **tanpa CLI pada sisi pengguna**. Semua langkah harus dapat dilakukan melalui Package Center, Web Station, File Station/SMB, dan browser; tidak memerlukan SSH atau terminal.

Pengguna mengonfirmasi Web Station, MariaDB, dan PHP 7.4 sudah dipasang pada DS923+ dengan DSM 7.2.2, serta database sudah diatur melalui phpMyAdmin pada `127.0.0.1`. Profil PHP yang benar-benar dipakai situs, ekstensi aktif, hak akun database, dan koneksi dari aplikasi tetap harus diuji. Dokumentasi resmi Synology untuk DSM 7.2 mencantumkan PHP 7.4 sebagai backend Web Station yang didukung dan menjelaskan pengaturan ekstensi lewat GUI. [Spesifikasi Web Station DSM 7.2](https://www.synology.com/en-au/dsm/7.2/software_spec/web_station) · [Pengaturan profil PHP](https://kb.synology.com/en-global/DSM/help/WebStation/application_webserv_php).

### Wizard instalasi otomatis (`setup.php`)

1. Letakkan berkas proyek pada folder situs Web Station melalui File Station atau SMB.
2. Pastikan profil PHP 7.4 untuk situs mengaktifkan PDO MySQL, cURL, dan OpenSSL. Folder aplikasi harus dapat ditulis PHP saat instalasi agar konfigurasi dapat dibuat.
3. Buka `setup.php` dari browser di perangkat lokal atau jaringan privat.
4. Masukkan host `127.0.0.1`, nama database yang sudah dibuat, dan kredensial MariaDB yang memiliki hak membuat tabel pada database itu. Tidak perlu membuka phpMyAdmin atau membuat database lagi.
5. Pilih nama pengguna dan kata sandi administrator Web Monitor (minimal 12 karakter), lalu jalankan instalasi. Wizard menguji koneksi, membuat tabel situs/riwayat/akun, membuat administrator, dan menyimpan konfigurasi.
6. Opsional: centang **Izinkan target intranet, hostname lokal, dan IP privat** bila ingin memantau layanan internal. Pilihan ini disimpan di `pengaturan.php`; default-nya nonaktif.
7. Setelah berhasil, wizard terkunci karena berkas `pengaturan.php` sudah dibuat. Masuk melalui `index.php`.

Wizard hanya menerima akses loopback/LAN privat dan menggunakan token CSRF. Jika instalasi sudah selesai, pengaturan `izinkan_alamat_lokal` dapat diubah melalui File Station dengan menyunting `pengaturan.php` dan menetapkan nilainya ke `true` atau `false` (tanpa mengubah kredensial lain). Jika koneksi atau izin folder gagal, wizard berhenti dengan pesan aman; tidak mencoba melewati hak MariaDB. Kredensial yang dimasukkan menjadi kredensial koneksi aplikasi yang disimpan di `pengaturan.php`, jadi gunakan akun database khusus berhak minimum, bukan root.

1. Periksa profil PHP yang dipakai situs, ekstensi wajib, izin direktori, dan koneksi MariaDB.
2. Uji apakah database dan akun aplikasi yang sudah dibuat melalui phpMyAdmin dapat digunakan oleh Web Monitor.
3. Jika database atau akun belum siap, sediakan langkah GUI untuk membuat database/tabel dan akun MariaDB khusus aplikasi dengan hak minimum. Kredensial administratif hanya dipakai selama pemasangan dan tidak disimpan.
4. Simpan hanya konfigurasi akun database aplikasi pada berkas yang dilindungi, tidak masuk Git, dan tidak dapat diunduh melalui web.
5. Minta pengguna membuat akun administrator Web Monitor pertama; kata sandi disimpan sebagai hash.
6. Tandai instalasi selesai dan kunci wizard agar tidak dapat digunakan ulang untuk membuat akun atau mengubah konfigurasi.
7. Jika hak database tidak cukup, tampilkan instruksi yang dapat dilakukan lewat GUI DSM/phpMyAdmin; jangan meminta CLI atau mencoba melewati hak akses.

Jangan gunakan akun root MariaDB untuk koneksi harian aplikasi. Jangan gunakan kata sandi default. Wizard harus dibatasi ke akses lokal/tepercaya, memakai perlindungan CSRF, dan menutup dirinya setelah instalasi sukses.

Wizard otomatis telah tersedia, tetapi pemasangan belum dianggap terverifikasi sampai uji pada NAS target atau lingkungan setara berhasil. Jangan gunakan data produksi sebelum prosedur itu berhasil diuji.

## Status fase dan dokumen

- [BLOCKERS.md](BLOCKERS.md): blocker dan gate wajib sebelum menerima fase.
- [TODO.md](TODO.md): roadmap dan kriteria penerimaan.
- [CHANGELOG.md](CHANGELOG.md): catatan perubahan.
- [CONTRIBUTING.md](CONTRIBUTING.md): aturan kerja, pengujian, dan rilis.
- [GLOSARIUM.md](GLOSARIUM.md): istilah baku dan penamaan.

Fase 0 masih terblokir sampai profil PHP dan ekstensi aktif, koneksi Web Monitor ke MariaDB di `127.0.0.1`, hak akun database, pemeriksaan rahasia repositori, dan pemasangan/pengujian bersih diverifikasi. Uji runtime PHP 7.4 dan penyelesaian mitigasi SSRF juga wajib sebelum Fase 1 diterima.
