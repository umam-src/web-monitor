# Roadmap dan TODO

## Tahap 0 — Fondasi
- [ ] Tetapkan lingkungan pemasangan, basis data, dan kebutuhan akses.
- [x] Buat struktur awal PHP 7.4 dan dasbor baca-saja.
- [x] Buat skema awal basis data untuk situs dan riwayat pemeriksaan.
- [x] Terapkan glosarium awal dan panduan kontribusi.
- [ ] Lengkapi panduan pemasangan dan periksa sintaks/runtime.
- [ ] Siapkan pengujian otomatis.

## Tahap 1 — MVP pemantauan
- [ ] Buat pengelolaan situs pantauan.
- [ ] Validasi alamat HTTP/HTTPS dan tujuan yang diperbolehkan.
- [ ] Periksa situs dengan batas waktu koneksi dan respons.
- [ ] Catat waktu, kode HTTP, durasi, hasil, dan ringkasan kesalahan.
- [ ] Hubungkan pemeriksa dengan dasbor dan riwayat.
- [ ] Uji situs sehat, DNS gagal, batas waktu, kode 4xx/5xx, dan TLS bermasalah.

## Tahap 2 — Penjadwalan dan riwayat
- [ ] Jalankan pemeriksaan terjadwal melalui cron.
- [ ] Cegah proses tumpang tindih.
- [ ] Atur interval dan retensi riwayat.
- [ ] Tampilkan riwayat yang dapat disaring.

## Tahap 3 — Diagnosis Cloudflare
- [ ] Simpan indikator halaman kesalahan dan Cloudflare Ray ID jika tersedia.
- [ ] Bedakan kegagalan koneksi, kode HTTP, dan halaman tantangan.
- [ ] Terapkan percobaan ulang terbatas dan perubahan status yang stabil.
- [ ] Dokumentasikan batasan bahwa satu lokasi pemeriksaan tidak membuktikan gangguan global.

## Tahap 4 — Pemberitahuan
- [ ] Tentukan kanal pemberitahuan yang dibutuhkan.
- [ ] Kirim pemberitahuan saat gangguan terkonfirmasi dan saat pulih.
- [ ] Cegah pemberitahuan berulang.

## Tahap 5 — Keamanan dan rilis
- [ ] Tambahkan autentikasi dan perlindungan CSRF sebelum dipublikasikan.
- [ ] Cegah pemeriksa digunakan untuk mengakses jaringan yang tidak diizinkan.
- [ ] Uji kueri basis data, validasi masukan, keluaran HTML, dan sesi.
- [ ] Uji pemasangan, peningkatan skema, cadangan, dan pemulihan.
- [ ] Siapkan CI untuk pemeriksaan sintaks dan pengujian.
- [ ] Selaraskan dokumentasi, CHANGELOG.md, dan nomor versi.

## Di luar MVP
- [ ] Banyak lokasi pemeriksaan.
- [ ] Halaman status publik.
- [ ] Pemeriksaan isi halaman dan kata kunci.
- [ ] Pemantauan masa berlaku sertifikat TLS.
- [ ] Laporan ketersediaan dan ekspor data.

## Catatan
PHP 7.4 dipakai sesuai permintaan awal, tetapi sudah tidak mendapat dukungan keamanan resmi. Rencanakan peningkatan ke versi yang masih didukung. Kerangka awal belum diuji pada runtime PHP dan belum siap dipublikasikan.
