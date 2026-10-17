# Catatan Perubahan

Mengikuti Keep a Changelog dan Semantic Versioning.

## [Belum dirilis]

### Direncanakan
- Verifikasi runtime wizard setup dan login pada NAS Synology target; masih menunggu pengujian.

### Ditambahkan
- Pemeriksaan mode publik menggunakan DNS-over-HTTPS Cloudflare dan mengunci koneksi cURL ke alamat IPv4 publik yang divalidasi, tanpa bergantung pada DNS internal untuk target.
- Kegagalan resolusi DNS publik dicatat sebagai hasil gangguan dalam riwayat, baik pada pemeriksaan satu situs maupun pemeriksaan semua situs.
- Opsi `Situs lokal` per situs pada formulir tambah/ubah; nilainya disimpan di database dan kolom baru dimigrasikan otomatis pada instalasi lama.
- Dialog tambah/ubah situs dengan ukuran berbasis viewport, batas minimum/maksimum, dan area isi yang dapat digulir.
- Wizard browser `setup.php` menguji koneksi ke database yang sudah ada dan membuat tabel situs, riwayat, serta akun administrator secara otomatis tanpa phpMyAdmin.
- Login/logout administrator dasar; dashboard dan tindakan memerlukan sesi login.
- Tampilan riwayat hingga 20 pemeriksaan terbaru per situs, diakses dari daftar situs.
- Workflow GitHub Actions untuk membuat tag dan GitHub Release dari bagian versi bertanggal di CHANGELOG.md.
- Workflow CI pada push ke main dan pemicu manual untuk memeriksa sintaks PHP 7.4 serta penerapan skema pada MySQL 8.0.

### Diubah
- Target publik dapat disimpan meskipun DNS sedang gagal agar gangguan DNS bisa dideteksi saat pemeriksaan; target publik di-resolve melalui DNS-over-HTTPS saat diperiksa.
- Inisialisasi dialog tambah/ubah ditunda hingga DOM selesai diparsing agar tombol pemicu tersedia saat event handler dipasang.
- Tombol Periksa semua ditempatkan di samping Tambah situs; pemeriksaan massal memeriksa seluruh situs aktif dan menyimpan hasil tiap situs.
- Tombol tindakan tiap situs disederhanakan menjadi ikon SVG inline dengan label aksesibel, tooltip, dan tanpa dependensi eksternal.
- Tombol tambah situs ditempatkan di sisi kanan judul daftar situs dengan warna hijau; tombol keluar dipindahkan ke bagian bawah halaman dan diberi warna merah.
- Mode target lokal menerima hostname intranet/Active Directory dan IP privat/loopback tanpa mensyaratkan resolusi DNS publik pada saat penyimpanan.
- Pengaturan global izin alamat lokal dihapus; target intranet diatur per situs melalui checkbox `Situs lokal`.
- Roadmap TODO.md menetapkan target versi MINOR per fase dari 0.1.0 sampai 0.6.0 serta kriteria penerimaan.
- PHP 7.4 dipertahankan sebagai target runtime selama lingkungan Synology yang dituju masih mendukungnya; migrasi ditinjau kembali jika dukungan Synology berakhir.

### Keamanan
- Autentikasi dasar sudah ditambahkan, tetapi jangan publikasikan ke internet sebelum audit sesi/login dan mitigasi SSRF selesai.
- PHP 7.4 tidak lagi mendapat dukungan keamanan resmi upstream; mitigasi risiko tetap diperlukan.

Belum ada rilis resmi; runtime PHP 7.4 pada Synology belum diuji.
