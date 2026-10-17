# Panduan Kontribusi

## Prinsip
- Dahulukan keamanan, kehilangan data, dan kerusakan fungsi inti.
- Buat perubahan sekecil mungkin yang menyelesaikan masalah.
- Perbarui README.md, TODO.md, GLOSARIUM.md, dan CHANGELOG.md jika diperlukan.
- Jangan menyatakan tugas selesai tanpa pengujian yang dapat diulang.
- Gunakan bahasa Indonesia dan istilah baku dari GLOSARIUM.md.
- Jangan menyimpan kata sandi, token, atau data sensitif di repositori maupun log.

## Lingkungan dan kompatibilitas
- Target runtime adalah PHP 7.4 dengan PDO, PDO MySQL, cURL, dan OpenSSL, sesuai paket yang tersedia pada lingkungan Synology target.
- Pertahankan kompatibilitas PHP 7.4 selama lingkungan Synology target masih mendukungnya. Jangan memigrasikan versi PHP hanya karena dukungan upstream telah berakhir.
- PHP 7.4 sudah tidak mendapat dukungan keamanan resmi upstream. Mitigasi risiko dengan membatasi akses aplikasi, menjaga dependensi tetap diperbarui sejauh kompatibel, dan melakukan pengujian keamanan. Jangan mengekspos aplikasi ke internet sebelum kontrol keamanan wajib selesai.
- Jika Synology target tidak lagi mendukung PHP 7.4, buat rencana migrasi terpisah berdasarkan versi PHP yang didukung saat itu; uji kode, ekstensi, dan dependensi sebelum mengubah persyaratan.

## Alur kerja
1. Baca README.md, TODO.md, GLOSARIUM.md, dan CHANGELOG.md.
2. Tentukan masalah dan kriteria penerimaan sebelum mengubah kode.
3. Jalankan pemeriksaan sintaks PHP dan pengujian terkait.
4. Tambahkan atau perbarui pengujian untuk perilaku yang berubah.
5. Tinjau validasi alamat, pengalihan HTTP, kueri basis data, keluaran HTML, sesi, dan penanganan kesalahan.
6. Catat hasil pengujian serta hal yang belum diverifikasi.

## Penamaan
- Gunakan istilah baku dari GLOSARIUM.md.
- Utamakan snake_case untuk fungsi, variabel, tabel, dan kolom.
- Nama wajib PHP, SQL, HTTP, Composer, cron, dan pustaka pihak ketiga tidak diterjemahkan.
- Perbarui glosarium sebelum memperkenalkan padanan baru yang akan dipakai luas.

## Keamanan pemantauan
Pemeriksa situs berisiko disalahgunakan untuk mengakses jaringan internal. Batasi protokol ke HTTP/HTTPS, validasi alamat, blokir tujuan privat/loopback/link-local, validasi ulang setelah DNS dan pengalihan, batasi waktu dan ukuran respons, serta jangan mencatat isi respons mentah atau rahasia.

## Versi dan fase roadmap
- Setiap fase roadmap yang selesai dan menambah kemampuan kompatibel menghasilkan rilis MINOR tersendiri. Fase 0 sampai 5 menargetkan 0.1.0 sampai 0.6.0; lihat TODO.md untuk kriteria penerimaan.
- Jangan merilis versi target hanya karena pekerjaan dimulai. Semua kriteria penerimaan fase harus lulus, pengujian harus diverifikasi, dan CHANGELOG.md harus diperbarui.
- PATCH untuk perbaikan bug atau keamanan tanpa menyelesaikan fase/menambah kemampuan utama.
- MAJOR untuk perubahan tidak kompatibel terhadap kontrak atau konfigurasi yang dirilis. Sebelum 1.0.0, jelaskan dampak perubahan dengan jelas.
- Gunakan Keep a Changelog dan Semantic Versioning.
