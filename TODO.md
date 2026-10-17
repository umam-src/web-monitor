# Roadmap dan TODO

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

## Fase 0 — Fondasi (target 0.1.0)
- [x] Buat dokumentasi utama dan glosarium.
- [x] Buat kerangka dasbor PHP dan skema basis data awal.
- [x] Buat contoh pengaturan lokal dan .gitignore.
- [ ] Tetapkan lingkungan PHP, ekstensi, basis data, dan server web.
- [ ] Verifikasi panduan pemasangan pada lingkungan bersih.
- [ ] Tambahkan pemeriksaan sintaks PHP dan pengujian otomatis di CI.
- [ ] Uji skema pada MySQL/MariaDB.

Kriteria penerimaan: instalasi dapat diulang, semua berkas PHP lulus pemeriksaan sintaks, skema bisa diterapkan pada basis data kosong, dan rahasia tidak masuk Git.

## Fase 1 — Pengelolaan situs dan pemeriksaan manual (target 0.2.0)
- [ ] Tambah, ubah, aktifkan/nonaktifkan, dan hapus situs.
- [ ] Validasi alamat HTTP/HTTPS.
- [ ] Periksa situs dengan batas waktu koneksi dan total.
- [ ] Simpan kode HTTP, waktu respons, waktu pemeriksaan, status, dan ringkasan kesalahan.
- [ ] Tampilkan hasil terakhir dan riwayat dasar.
- [ ] Uji respons sukses, 4xx/5xx, DNS gagal, batas waktu, dan TLS.

Kriteria penerimaan: pengguna bisa mengelola situs dari antarmuka; pemeriksaan menyimpan hasil; kegagalan koneksi dibedakan dari kode HTTP; keluaran dan kueri ditangani dengan aman.

## Fase 2 — Pemantauan terjadwal dan riwayat (target 0.3.0)
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
- [ ] Tambahkan autentikasi, pengelolaan sesi, dan perlindungan CSRF.
- [ ] Cegah pemeriksa mengakses jaringan internal atau alamat khusus; validasi DNS dan setiap pengalihan.
- [ ] Batasi waktu, ukuran respons, pengalihan, dan frekuensi pemeriksaan.
- [ ] Uji akses tanpa izin, injeksi SQL, XSS, CSRF, dan validasi alamat.
- [ ] Uji pemasangan, migrasi skema, pencadangan, dan pemulihan.
- [ ] Siapkan CI dan panduan rilis.
- [ ] Rencanakan peningkatan dari PHP 7.4 ke versi yang masih mendapat dukungan keamanan.

Kriteria penerimaan: jangan publikasikan tanpa autentikasi dan pemeriksaan keamanan; pengujian lulus; cadangan/pemulihan teruji; status dukungan PHP dinyatakan jelas.

## Setelah 0.6.0 — berdasarkan kebutuhan
- [ ] Banyak lokasi pemeriksaan.
- [ ] Halaman status publik.
- [ ] Pemeriksaan isi/kata kunci.
- [ ] Pemantauan masa berlaku sertifikat TLS.
- [ ] Laporan dan ekspor.
- [ ] Banyak akun/organisasi.

## Risiko dan keputusan terbuka
- PHP 7.4 sudah tidak mendapat dukungan keamanan resmi; gunakan lingkungan terisolasi selama pengembangan dan rencanakan peningkatan.
- Runtime PHP dan integrasi basis data belum diuji.
- Belum diputuskan apakah aplikasi mendukung banyak akun.
- Kanal pemberitahuan belum dipilih.
