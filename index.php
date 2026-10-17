<?php
// Kerangka dasbor Web Monitor untuk PHP 7.4.
// Jangan publikasikan sebelum autentikasi dan pengamanan akses diterapkan.

$berkas_pengaturan = __DIR__ . '/pengaturan.php';
if (!is_file($berkas_pengaturan)) {
    http_response_code(503);
    $pesan = 'Pengaturan belum tersedia. Salin pengaturan.contoh.php menjadi pengaturan.php dan sesuaikan koneksi basis data.';
} else {
    try {
        $pengaturan = require $berkas_pengaturan;
        $pdo = new PDO(
            $pengaturan['dsn'],
            $pengaturan['pengguna'],
            $pengaturan['rahasia'],
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            )
        );
        $sql = "SELECT s.id, s.nama, s.alamat_url, s.aktif, s.interval_detik,
                       r.status, r.kode_http, r.waktu_respons_ms, r.pesan_kesalahan, r.diperiksa_pada
                FROM situs_pantauan s
                LEFT JOIN riwayat_pemeriksaan r ON r.id = (
                    SELECT MAX(r2.id) FROM riwayat_pemeriksaan r2 WHERE r2.situs_id = s.id
                )
                ORDER BY s.nama ASC";
        $situs = $pdo->query($sql)->fetchAll();
        $pesan = '';
    } catch (Exception $galat) {
        http_response_code(503);
        $situs = array();
        $pesan = 'Basis data belum siap atau tidak dapat diakses. Periksa pengaturan dan jalankan database/schema.sql.';
        error_log('Web Monitor: koneksi atau kueri basis data gagal.');
    }
}

function aman($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Web Monitor</title>
    <style>
        :root { color-scheme: light; font-family: system-ui, sans-serif; color: #20252b; background: #f4f6f8; }
        body { margin: 0; }
        header { background: #fff; border-bottom: 1px solid #dfe3e8; padding: 1.25rem max(1rem, calc((100% - 1100px) / 2)); }
        main { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
        h1 { margin: 0; font-size: 1.4rem; }
        .keterangan { color: #5c6672; margin-top: .35rem; }
        .panel { background: #fff; border: 1px solid #dfe3e8; border-radius: .7rem; padding: 1rem; overflow-x: auto; }
        .pesan { background: #fff7e6; border: 1px solid #f0d69a; padding: .9rem 1rem; border-radius: .5rem; }
        table { width: 100%; border-collapse: collapse; min-width: 680px; }
        th, td { text-align: left; padding: .75rem .6rem; border-bottom: 1px solid #e8ebef; vertical-align: top; }
        th { color: #4a5561; font-size: .85rem; }
        .status { display: inline-block; border-radius: 1rem; padding: .2rem .6rem; background: #edf0f3; }
        .aktif { background: #dff5e6; color: #14532d; }
        .gangguan { background: #fee2e2; color: #991b1b; }
        .catatan { color: #5c6672; font-size: .9rem; margin-top: 1rem; }
        a { color: #175cd3; overflow-wrap: anywhere; }
    </style>
</head>
<body>
<header>
    <h1>Web Monitor</h1>
    <div class="keterangan">Ringkasan ketersediaan situs dan hasil pemeriksaan terakhir.</div>
</header>
<main>
    <?php if ($pesan !== ''): ?>
        <p class="pesan"><?php echo aman($pesan); ?></p>
    <?php elseif (count($situs) === 0): ?>
        <section class="panel">Belum ada situs yang didaftarkan. Fitur pengelolaan situs dan pemeriksa terjadwal masih dalam roadmap.</section>
    <?php else: ?>
        <section class="panel">
            <table>
                <thead><tr><th>Situs</th><th>Status</th><th>Kode HTTP</th><th>Waktu respons</th><th>Pemeriksaan terakhir</th></tr></thead>
                <tbody>
                <?php foreach ($situs as $baris): ?>
                    <tr>
                        <td><?php echo aman($baris['nama']); ?><br><a href="<?php echo aman($baris['alamat_url']); ?>" rel="noreferrer noopener"><?php echo aman($baris['alamat_url']); ?></a></td>
                        <td><span class="status <?php echo aman($baris['status'] === 'aktif' ? 'aktif' : ($baris['status'] === 'gangguan' ? 'gangguan' : '')); ?>"><?php echo aman($baris['status'] ? ucfirst(str_replace('_', ' ', $baris['status'])) : 'Tidak diketahui'); ?></span></td>
                        <td><?php echo aman($baris['kode_http'] === null ? '—' : $baris['kode_http']); ?></td>
                        <td><?php echo aman($baris['waktu_respons_ms'] === null ? '—' : $baris['waktu_respons_ms'] . ' ms'); ?></td>
                        <td><?php echo aman($baris['diperiksa_pada'] ? $baris['diperiksa_pada'] : 'Belum diperiksa'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
    <p class="catatan">Kerangka awal. Pengelolaan situs, pemeriksaan berkala, autentikasi, dan diagnosis Cloudflare belum diimplementasikan.</p>
</main>
</body>
</html>
