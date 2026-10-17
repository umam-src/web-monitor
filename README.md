# Web Monitor

Aplikasi web PHP 7.4 untuk memantau ketersediaan situs melalui HTTP/HTTPS, menyimpan riwayat pemeriksaan, dan membantu mengenali gangguan seperti halaman kesalahan Cloudflare.

Status: fondasi dokumentasi dan roadmap; implementasi aplikasi belum selesai.

## Cakupan awal
- Daftar situs yang dipantau.
- Pemeriksaan berkala dengan batas waktu.
- Status terakhir, kode HTTP, waktu respons, dan ringkasan kesalahan.
- Riwayat pemeriksaan dan penjadwalan melalui cron.

## Persyaratan awal
PHP 7.4, PDO, PDO MySQL, cURL, OpenSSL, MySQL/MariaDB, server web, dan cron.

Peringatan: PHP 7.4 sudah tidak mendapat dukungan keamanan resmi. Gunakan lingkungan terisolasi untuk pengembangan dan rencanakan peningkatan ke versi yang masih didukung sebelum penggunaan publik.

## Dokumen
- [TODO.md](TODO.md): roadmap dan kriteria selesai.
- [CHANGELOG.md](CHANGELOG.md): catatan perubahan dengan Keep a Changelog dan Semantic Versioning.
- [CONTRIBUTING.md](CONTRIBUTING.md): aturan kontribusi dan pengujian.
- [GLOSARIUM.md](GLOSARIUM.md): istilah baku dan penamaan.
