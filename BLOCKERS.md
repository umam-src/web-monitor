# Blocker dan gate antar-fase

Dokumen ini adalah gerbang wajib sebelum fase roadmap dinyatakan selesai atau versi target dirilis. Kode yang sudah ada di fase lebih tinggi tidak otomatis berarti fase sebelumnya telah diterima.

## Status saat ini

**Fase aktif resmi: Fase 0 — Fondasi (target 0.1.0). Status: TERBLOKIR / BELUM DITERIMA.**

Sebagian implementasi Fase 1 sudah ada di `main`, tetapi itu belum berarti proyek secara formal lulus Fase 0 atau Fase 1. Jangan menerbitkan rilis 0.1.0 maupun memulai pekerjaan fitur Fase 2 sampai gate yang relevan dibuka. Pekerjaan investigasi dan perbaikan blocker tetap boleh dilakukan.

## Blocker Fase 0 — wajib ditutup sebelum Fase 1 diterima

- [ ] Catat model perangkat dan versi DSM Synology target.
- [ ] Verifikasi paket server web, PHP 7.4, dan ekstensi yang dibutuhkan: PDO, PDO MySQL, cURL, dan OpenSSL.
- [ ] Jalankan panduan pemasangan dari kondisi bersih pada lingkungan target atau lingkungan setara yang didokumentasikan.
- [x] CI memeriksa sintaks PHP 7.4 dan penerapan skema pada MySQL 8.0.
- [ ] Pastikan CI terbaru yang relevan selesai sukses setelah perubahan kode terkait.
- [ ] Tinjau konfigurasi dan repositori untuk memastikan tidak ada rahasia yang ikut ter-commit.

**Bukti yang tersedia:** CI pada commit `6de7075` berhasil. Ini hanya membuktikan pemeriksaan otomatis yang ada; bukan bukti instalasi Synology atau kompatibilitas runtime di perangkat target.

**Gate Fase 0 terbuka jika:** semua kotak di atas ditutup dengan bukti yang dapat diperiksa, panduan instalasi berhasil diikuti, dan hasil dicatat di log kerja. Setelah itu Fase 0 dapat diterima dan rilis 0.1.0 boleh dipertimbangkan.

## Gate Fase 1 — sebelum Fase 2

- [ ] Semua kriteria Fase 0 diterima.
- [ ] CI terbaru lulus untuk kode Fase 1.
- [ ] Uji runtime PHP 7.4 untuk respons sukses, HTTP 4xx/5xx, DNS gagal, timeout, dan TLS berhasil.
- [ ] Uji alur tambah, ubah, nonaktifkan, aktifkan, hapus, periksa, dan tampilkan riwayat.
- [ ] Mitigasi SSRF memvalidasi semua alamat hasil DNS dan mencegah DNS rebinding; protokol dan tujuan khusus ditolak secara menyeluruh.
- [ ] Periksa keamanan kueri, escaping output, CSRF, dan penanganan kesalahan.
- [ ] Dokumentasi dan CHANGELOG sesuai dengan fitur yang benar-benar selesai dan diuji.

**Gate Fase 1 terbuka jika:** seluruh item wajib di atas memiliki bukti pengujian yang lulus. Sampai itu terjadi, jangan mulai fitur pemantauan terjadwal Fase 2. Ini penting karena penjadwalan otomatis akan memperbesar dampak jika pemeriksa URL disalahgunakan.

## Gate untuk setiap fase berikutnya

Untuk Fase 2–5, sebelum masuk fase berikutnya:

1. Semua gate fase sebelumnya sudah diterima dan tidak ada blocker kritis/tinggi yang terbuka.
2. Kriteria penerimaan fase saat ini ditandai selesai hanya setelah diuji.
3. CI dan pengujian regresi yang relevan lulus.
4. Pengujian keamanan, migrasi data, dan kompatibilitas yang relevan lulus.
5. README, TODO, CHANGELOG, dan panduan operasional diperbarui jika terdampak.
6. Log kerja mencatat bukti, hasil yang belum diuji, dan keputusan penerimaan.
7. Versi target baru dimasukkan ke bagian bertanggal CHANGELOG hanya setelah gate disetujui.

## Aturan keputusan

- **GAGAL / TERBLOKIR:** satu saja kriteria wajib belum terpenuhi atau bukti belum ada. Jangan menandai fase selesai dan jangan merilis versi fase.
- **LULUS DENGAN CATATAN:** hanya untuk risiko nonkritis yang secara eksplisit disetujui dan tidak melanggar kriteria penerimaan; tulis alasan dan pemilik tindak lanjut.
- **LULUS:** seluruh kriteria wajib lulus dan bukti tercatat. Baru setelah itu fase berikutnya boleh dimulai.
- Keberhasilan CI tidak menggantikan pengujian runtime, instalasi, atau peninjauan keamanan yang diwajibkan.
- Jangan publikasikan aplikasi ke internet sebelum autentikasi, perlindungan SSRF, dan pemeriksaan keamanan yang diwajibkan selesai.

## Riwayat status

- 2026-10-17: Fase 0 tetap terbuka. CI untuk sintaks PHP 7.4 dan skema MySQL berhasil, tetapi detail lingkungan Synology dan pemasangan bersih belum diverifikasi. Implementasi Fase 1 sudah ada sebagian, tetapi uji runtime dan mitigasi SSRF penuh masih menjadi blocker.
