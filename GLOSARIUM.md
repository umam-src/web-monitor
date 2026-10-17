# Glosarium Web Monitor

Glosarium ini menjadi acuan istilah antarmuka, dokumentasi, kode, dan basis data. Tambahkan istilah baru di sini sebelum digunakan luas.

## Istilah produk

| Istilah baku | Makna | Hindari sebagai label antarmuka |
|---|---|---|
| Situs | Alamat web yang dipantau | website, web target |
| Pemantauan | Pemeriksaan situs secara berkala | monitoring |
| Pemeriksaan | Satu percobaan memperoleh respons dari situs | pengecekan, ping kecuali ICMP ping |
| Status | Kesimpulan hasil pemeriksaan | kondisi, keadaan |
| Aktif | Situs memenuhi aturan keberhasilan | online |
| Gangguan | Pemeriksaan gagal atau respons tidak memenuhi aturan | down |
| Tidak diketahui | Bukti belum cukup untuk menentukan status | unknown |
| Riwayat pemeriksaan | Catatan hasil sebelumnya | log status |
| Waktu respons | Lama permintaan sampai hasil diterima | latency sebagai label umum |
| Batas waktu | Waktu maksimum suatu operasi | timeout di antarmuka |
| Pemberitahuan | Pesan ketika status berubah atau aturan terpenuhi | notifikasi, alert di antarmuka |
| Pulih | Situs kembali memenuhi aturan setelah gangguan | recovered |
| Interval pemeriksaan | Jarak waktu antar pemeriksaan terjadwal | frekuensi sebagai nama pengaturan |
| Kode HTTP | Kode status protokol HTTP | status code di antarmuka |
| ID Ray Cloudflare | Pengenal permintaan untuk penelusuran gangguan | ray id tanpa penjelasan |

## Istilah teknis yang dipertahankan

PHP, HTTP, HTTPS, DNS, TLS, Cloudflare, cron, PDO, SQL, Semantic Versioning, dan Keep a Changelog adalah istilah teknis atau nama produk; jangan diterjemahkan sebagai nama API atau protokol.

## Aturan penamaan kode

- Utamakan bahasa Indonesia untuk nama yang dibuat khusus oleh proyek.
- Gunakan snake_case untuk fungsi, variabel, tabel, dan kolom; contoh: periksa_situs(), waktu_pemeriksaan, situs_pantauan.
- Gunakan istilah secara konsisten untuk konsep yang sama.
- Nama waktu yang disarankan: dibuat_pada, diperbarui_pada, diperiksa_pada.
- Nama bawaan PHP, kata kunci SQL, protokol, API pihak ketiga, dan nama berkas wajib ekosistem tetap menggunakan ejaan aslinya.
- Jika API pihak ketiga memakai nama asing, bungkus dalam fungsi internal berbahasa Indonesia bila membantu konsistensi.
- Jangan mengubah istilah yang telah disepakati tanpa memperbarui kode, pengujian, dan dokumentasi.
- Nama dokumentasi yang disepakati tetap README.md, TODO.md, CHANGELOG.md, CONTRIBUTING.md, dan GLOSARIUM.md.

## Perlu diputuskan sebelum implementasi

- Apakah aplikasi hanya untuk satu administrator atau banyak akun.
- Kanal pemberitahuan yang diperlukan.
- Aturan tepat penentuan status Aktif/Gangguan berdasarkan kode HTTP dan isi respons.
