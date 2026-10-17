# Panduan Kontribusi

## Prinsip
- Dahulukan keamanan, kehilangan data, dan kerusakan fungsi inti.
- Buat perubahan sekecil mungkin yang menyelesaikan masalah.
- Perbarui README.md, TODO.md, GLOSARIUM.md, dan CHANGELOG.md jika diperlukan.
- Jangan menyatakan tugas selesai tanpa pengujian yang dapat diulang.
- Gunakan bahasa Indonesia untuk dokumentasi, antarmuka, komentar, dan nama kode jika praktis; ikuti GLOSARIUM.md.
- Jangan menyimpan kata sandi, token, atau data sensitif di repositori maupun log.

## Lingkungan awal
- PHP 7.4.
- PDO, PDO MySQL, cURL, dan OpenSSL.
- MySQL atau MariaDB, server web, dan cron.

PHP 7.4 sudah tidak mendapat dukungan keamanan resmi. Gunakan lingkungan terisolasi dan rencanakan peningkatan ke versi PHP yang masih didukung.

## Alur kerja
1. Baca README.md, TODO.md, GLOSARIUM.md, dan CHANGELOG.md.
2. Tentukan masalah dan kriteria penerimaan sebelum mengubah kode.
3. Jalankan php -l pada berkas PHP yang berubah dan pengujian terkait.
4. Tambahkan atau perbarui pengujian untuk perilaku yang berubah.
5. Periksa validasi alamat, pengalihan HTTP, kueri basis data, keluaran HTML, sesi, dan penanganan kesalahan.
6. Catat hasil pengujian serta hal yang belum diverifikasi.

## Penamaan
- Gunakan istilah baku dari GLOSARIUM.md.
- Utamakan snake_case untuk fungsi, variabel, tabel, dan kolom.
- Nama wajib PHP, SQL, HTTP, Composer, cron, dan pustaka pihak ketiga tidak diterjemahkan.
- Perbarui glosarium sebelum memperkenalkan padanan baru yang akan dipakai luas.

## Keamanan pemantauan
Pemeriksa situs berisiko disalahgunakan untuk mengakses jaringan internal. Batasi protokol ke HTTP/HTTPS, validasi alamat, blokir tujuan privat/loopback/link-local, validasi ulang setelah DNS dan pengalihan, batasi waktu dan ukuran respons, serta jangan mencatat isi respons mentah atau rahasia.

## Versi
Gunakan Semantic Versioning: MAJOR untuk perubahan tidak kompatibel, MINOR untuk kemampuan baru yang kompatibel, dan PATCH untuk perbaikan atau perubahan keamanan. Catat perubahan di CHANGELOG.md dengan format Keep a Changelog.
