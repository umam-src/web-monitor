# Roadmap dan TODO

> **Gate fase:** status dan blocker resmi ada di [BLOCKERS.md](BLOCKERS.md). Fase berikutnya dan rilis target tidak boleh dimulai sebelum gate fase sebelumnya diterima.

## Aturan versi
Setiap fase yang selesai menjadi rilis MINOR selama versi utama masih 0.x. Rilis hanya dibuat setelah kriteria penerimaan dan pengujian lulus. Perbaikan bug dan keamanan tanpa kemampuan baru menjadi PATCH. Perubahan tidak kompatibel harus didokumentasikan dengan jelas.

| Fase | Versi target | Hasil |
|---|---|---|
| 0. Fondasi | 0.1.0 | Instalasi, konfigurasi, basis data, CI, dan pengujian dasar |
| 1. Pemeriksaan manual | 0.2.0 | Kelola situs dan jalankan pemeriksaan HTTP/HTTPS |
| 2. Pemantauan terjadwal | 0.3.0 | Cron, status terakhir, dan riwayat pemeriksaan |
| 3. Diagnosis | 0.4.0 | Diagnosis DNS, koneksi, TLS, HTTP, dan indikator Cloudflare |
| 4. Pemberitahuan | 0.5.0 | Pemberitahuan gangguan dan pemulihan |
| 5. Kesiapan penggunaan | 0.6.0 | Autentikasi, keamanan, pengujian, pencadangan, dan panduan rilis |

## Fase 0 — Fondasi (target 0.1.0; status: TERBLOKIR / BELUM DITERIMA)
- [x] Buat dokumentasi utama dan glosarium.
- [x] Buat kerangka dasbor PHP dan skema basis data awal.
- [x] Buat contoh pengaturan lokal dan .gitignore.
- [x] Catat versi DSM target: DSM 7.2.2.
- [x] Catat perangkat target: Synology DS923+ dengan DSM 7.2.2.
- [x] Catat konfirmasi pengguna bahwa Web Station, MariaDB, dan PHP 7.4 sudah dipasang.
- [x] Catat bahwa database telah diatur melalui phpMyAdmin pada `127.0.0.1`.
- [ ] Verifikasi profil PHP yang dipakai situs Web Monitor serta ekstensi aktif: PDO, PDO MySQL, cURL, dan OpenSSL.
- [ ] Uji koneksi Web Monitor ke database di `127.0.0.1`; pastikan nama database dan hak akun aplikasi benar.
- [x] Implementasikan wizard browser `setup.php` tanpa CLI untuk menguji koneksi ke database yang sudah ada dan membuat tabel otomatis.
- [x] Implementasikan pembuatan akun administrator pertama dengan hash kata sandi dan penguncian wizard setelah konfigurasi berhasil disimpan.
- [x] Tambahkan login/logout administrator dan lindungi dashboard serta tindakan dengan sesi login.
- [ ] Uji wizard, hak membuat tabel, login/logout, dan penguncian setup pada PHP 7.4/MariaDB di NAS target.
- [ ] Verifikasi panduan pemasangan pada lingkungan Synology yang bersih.
- [x] Tambahkan CI untuk pemeriksaan sintaks PHP 7.4 dan penerapan skema MySQL.
- [x] Tambahkan uji penerapan skema pada MySQL 8.0 di CI.

Kriteria penerimaan: pemasangan dari browser/GUI tanpa CLI, database dan tabel dibuat otomatis dengan hak akses yang jelas, akun administrator pertama dibuat setelah instalasi, wizard terkunci setelah selesai, instalasi dapat diulang dengan aman, semua berkas PHP lulus pemeriksaan sintaks, skema bisa diterapkan pada basis data kosong, dan rahasia tidak masuk Git. CI PHP 7.4 dan penerapan skema MySQL 8.0 berhasil pada commit implementasi Fase 1; perubahan lanjutan tetap harus diverifikasi oleh run baru.

## Fase 1 — Pengelolaan situs dan pemeriksaan manual (target 0.2.0; belum diterima, menunggu gate Fase 0)
- [x] Implementasikan tambah, ubah, aktifkan/nonaktifkan, dan hapus situs.
- [x] Validasi awal alamat HTTP/HTTPS dan tolak alamat IP privat/lokal yang terdeteksi.
- [x] Tambahkan pemeriksaan manual dengan batas waktu koneksi 5 detik dan total 15 detik; pengalihan tidak diikuti.
- [x] Simpan kode HTTP, waktu respons, waktu pemeriksaan, status, dan ringkasan kesalahan.
- [x] Tampilkan hingga 20 riwayat pemeriksaan terbaru per situs.
- [ ] Uji respons sukses, 4xx/5xx, DNS gagal, batas waktu, dan TLS pada runtime PHP 7.4.

Kriteria penerimaan: pengguna bisa mengelola situs dari antarmuka; pemeriksaan menyimpan hasil; kegagalan koneksi dibedakan dari kode HTTP; keluaran dan kueri ditangani dengan aman. Implementasi baru belum dianggap terverifikasi sampai CI dan uji runtime lulus.

Implementasi Fase 1 belum dianggap siap rilis sampai uji runtime PHP 7.4 pada Synology atau lingkungan setara lulus. Validasi URL merupakan mitigasi SSRF awal, belum menutup DNS rebinding; aplikasi jangan dipublikasikan.

## Fase 2 — Pemantauan terjadwal dan riwayat (target 0.3.0; TERKUNCI sampai gate Fase 1 lulus)
- [ ] Jalankan pemeriksaan melalui cron dengan interval per situs.
- [ ] Cegah proses tumpang tindih.
- [ ] Tampilkan riwayat menurut waktu.
- [ ] Terapkan retensi dan pembersihan data lama yang aman.

Kriteria penerimaan: pemeriksaan berjalan tanpa membuka halaman; kegagalan satu situs tidak menghentikan situs lain; tidak ada proses ganda yang tak terkendali.

## Fase 3 — Diagnosis gangguan dan Cloudflare (target 0.4.0)
- [ ] Bedakan DNS, koneksi, TLS, batas waktu, kode HTTP, dan kesalahan aplikasi.
- [ ] Kenali indikator halaman kesalahan/tantangan Cloudflare.
- [ ] Catat Ray ID jika tersedia, tanpa menyimpan isi respons penuh secara default.
- [ ] Gunakan beberapa kegagalan berturut-turut dan percobaan ulang terbatas.
- [ ] Tampilkan alasan status dan bukti diagnostik.

Kriteria penerimaan: status memiliki alasan yang dapat dijelaskan; satu kegagalan sementara tidak langsung menjadi insiden; pemeriksaan satu lokasi tidak diklaim mewakili kondisi global.

## Fase 4 — Pemberitahuan (target 0.5.0)
- [ ] Pilih kanal berdasarkan kebutuhan.
- [ ] Kirim pemberitahuan ketika gangguan terkonfirmasi dan ketika pulih.
- [ ] Redam pemberitahuan berulang dan catat hasil pengiriman.

Kriteria penerimaan: pemberitahuan mengikuti aturan status; kegagalan kanal tidak menghentikan pemeriksaan.

## Fase 5 — Keamanan dan kesiapan penggunaan (target 0.6.0)
- [ ] Tambahkan autentikasi dan pengelolaan sesi.
- [x] Terapkan token CSRF untuk formulir POST.
- [ ] Cegah pemeriksa mengakses jaringan internal atau alamat khusus secara menyeluruh; validasi semua alamat hasil DNS dan setiap pengalihan.
- [x] Tetapkan batas waktu pemeriksaan dan matikan pengalihan otomatis.
- [ ] Batasi ukuran respons dan frekuensi pemeriksaan.
- [ ] Uji akses tanpa izin, injeksi SQL, XSS, CSRF, dan validasi alamat.
- [ ] Uji pemasangan, migrasi skema, pencadangan, dan pemulihan.
- [ ] Siapkan CI dan panduan rilis.

Kriteria penerimaan: jangan publikasikan tanpa autentikasi dan pemeriksaan keamanan; pengujian lulus; cadangan/pemulihan teruji; kompatibilitas PHP 7.4 pada Synology target terverifikasi dan risiko dukungan upstream dijelaskan.

## Setelah 0.6.0 — berdasarkan kebutuhan
- [ ] Banyak lokasi pemeriksaan.
- [ ] Halaman status publik.
- [ ] Pemeriksaan isi/kata kunci.
- [ ] Pemantauan masa berlaku sertifikat TLS.
- [ ] Laporan dan ekspor.
- [ ] Banyak akun/organisasi.

## Wizard instalasi dan akun (implementasi awal; verifikasi runtime masih terbuka)

- Seluruh langkah pemasangan dan pengaturan untuk pengguna dilakukan lewat GUI DSM dan wizard browser; tidak mengharuskan SSH, terminal, atau perintah CLI.
- Target DSM yang diketahui: 7.2.2 pada Synology DS923+. Pengguna mengonfirmasi Web Station, MariaDB, dan PHP 7.4 sudah dipasang; database sudah diatur melalui phpMyAdmin pada `127.0.0.1`. Versi/profil PHP aktif, ekstensi, nama database, hak akun aplikasi, dan koneksi dari aplikasi masih perlu diverifikasi.
- Wizard memeriksa PDO MySQL, format host/nama database, izin tulis folder konfigurasi, dan koneksi MariaDB. Verifikasi menyeluruh semua ekstensi PHP yang diperlukan masih perlu ditambahkan/diuji.
- Karena database sudah dibuat sebelumnya, wizard meminta kredensial untuk database tersebut dan membuat tabel otomatis. Akun MariaDB harus memiliki hak membuat tabel serta baca/tulis pada database itu; wizard tidak membuat database atau akun MariaDB baru. Kredensial yang dimasukkan disimpan sebagai kredensial koneksi runtime di `pengaturan.php`, jadi gunakan akun aplikasi berhak minimum dan bukan akun root.
- Setelah database siap, wizard meminta nama pengguna dan kata sandi administrator Web Monitor, menyimpan hash kata sandi, dan menutup endpoint installer ketika `pengaturan.php` sudah dibuat.
- Dashboard kini meminta login administrator; pengujian sesi, pembatasan percobaan login, dan audit autentikasi masih perlu dilakukan.
- Jangan menggunakan akun root MariaDB untuk koneksi harian aplikasi. Jangan membuat kredensial default, kata sandi bawaan, atau endpoint setup yang tetap terbuka.
- Kredensial database aplikasi disimpan dalam berkas konfigurasi di luar Git dan tidak boleh dapat diunduh atau disajikan sebagai teks melalui web. Izin berkas harus dibatasi ke akun layanan yang menjalankan PHP sejauh dapat dilakukan melalui GUI DSM.
- Semua langkah pemasangan dapat dilakukan lewat File Station, Package Center, Web Station, dan browser. Pengunggahan/penempatan berkas dapat memakai File Station atau SMB; tidak ada perintah terminal dalam petunjuk pengguna.

## Risiko dan keputusan terbuka
- Pertahankan PHP 7.4 selama Synology target masih mendukungnya. PHP 7.4 tidak lagi mendapat dukungan keamanan upstream, jadi mitigasi risikonya dan evaluasi ulang bila dukungan Synology berakhir.
- Runtime PHP dan integrasi basis data belum diuji pada lingkungan Synology.
- Pemeriksaan URL saat ini adalah mitigasi awal SSRF, bukan pengganti validasi seluruh alamat DNS/redirect yang harus diselesaikan sebelum penggunaan publik.
- Belum diputuskan apakah aplikasi mendukung banyak akun.
- Kanal pemberitahuan belum dipilih.
