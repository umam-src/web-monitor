<?php
// Web Monitor PHP 7.4. Jangan publikasikan sebelum autentikasi dan audit keamanan tersedia.
session_start();

function aman($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

function urlPublikValid($url)
{
    if (!is_string($url) || strlen($url) > 2048 || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $bagian = parse_url($url);
    if (!$bagian || !isset($bagian['scheme'], $bagian['host']) ||
        !in_array(strtolower($bagian['scheme']), array('http', 'https'), true) ||
        isset($bagian['user']) || isset($bagian['pass'])) {
        return false;
    }
    $host = trim($bagian['host'], '[]');
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
    if (strpos($host, '.') === false || preg_match('/\.local$/i', $host)) {
        return false;
    }
    $ip = gethostbyname($host);
    return $ip !== $host && (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

function tokenValid()
{
    return isset($_POST['token'], $_SESSION['token']) &&
        is_string($_POST['token']) && hash_equals($_SESSION['token'], $_POST['token']);
}

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];
$pesan = '';
$jenisPesan = 'info';
$situs = array();
$edit = null;
$riwayatSitus = null;
$riwayat = array();
$berkasPengaturan = __DIR__ . '/pengaturan.php';
$pdo = null;

if (!is_file($berkasPengaturan)) {
    http_response_code(503);
    $pesan = 'Pengaturan belum tersedia. Salin pengaturan.contoh.php menjadi pengaturan.php dan sesuaikan koneksi basis data.';
} else {
    try {
        $pengaturan = require $berkasPengaturan;
        $pdo = new PDO($pengaturan['dsn'], $pengaturan['pengguna'], $pengaturan['rahasia'], array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ));
    } catch (Exception $galat) {
        http_response_code(503);
        $pesan = 'Basis data tidak dapat diakses. Periksa pengaturan dan jalankan database/schema.sql.';
        error_log('Web Monitor: koneksi basis data gagal.');
    }
}

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!tokenValid()) {
        http_response_code(400);
        $pesan = 'Permintaan tidak valid atau sesi kedaluwarsa. Muat ulang halaman.';
        $jenisPesan = 'error';
    } else {
        $aksi = isset($_POST['aksi']) && is_string($_POST['aksi']) ? $_POST['aksi'] : '';
        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
        try {
            if ($aksi === 'simpan') {
                $nama = trim(isset($_POST['nama']) && is_string($_POST['nama']) ? $_POST['nama'] : '');
                $url = trim(isset($_POST['alamat_url']) && is_string($_POST['alamat_url']) ? $_POST['alamat_url'] : '');
                $interval = isset($_POST['interval_detik']) ? filter_var($_POST['interval_detik'], FILTER_VALIDATE_INT) : false;
                if ($nama === '' || strlen($nama) > 150) {
                    throw new InvalidArgumentException('Nama wajib diisi dan maksimal 150 karakter.');
                }
                if (!urlPublikValid($url)) {
                    throw new InvalidArgumentException('Alamat harus berupa URL HTTP/HTTPS publik yang valid. Alamat privat/lokal ditolak.');
                }
                if ($interval === false || $interval < 60 || $interval > 86400) {
                    throw new InvalidArgumentException('Interval harus antara 60 dan 86400 detik.');
                }
                if ($id) {
                    $stmt = $pdo->prepare('UPDATE situs_pantauan SET nama = ?, alamat_url = ?, interval_detik = ? WHERE id = ?');
                    $stmt->execute(array($nama, $url, $interval, $id));
                    $pesan = 'Situs berhasil diperbarui.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO situs_pantauan (nama, alamat_url, interval_detik) VALUES (?, ?, ?)');
                    $stmt->execute(array($nama, $url, $interval));
                    $pesan = 'Situs berhasil ditambahkan.';
                }
                $jenisPesan = 'success';
            } elseif ($aksi === 'toggle' && $id) {
                $stmt = $pdo->prepare('UPDATE situs_pantauan SET aktif = IF(aktif = 1, 0, 1) WHERE id = ?');
                $stmt->execute(array($id));
                $pesan = 'Status situs diperbarui.';
                $jenisPesan = 'success';
            } elseif ($aksi === 'hapus' && $id) {
                $stmt = $pdo->prepare('DELETE FROM situs_pantauan WHERE id = ?');
                $stmt->execute(array($id));
                $pesan = 'Situs dan riwayatnya dihapus.';
                $jenisPesan = 'success';
            } elseif ($aksi === 'periksa' && $id) {
                $stmt = $pdo->prepare('SELECT id, alamat_url FROM situs_pantauan WHERE id = ? AND aktif = 1');
                $stmt->execute(array($id));
                $target = $stmt->fetch();
                if (!$target) {
                    throw new InvalidArgumentException('Situs tidak ditemukan atau sedang dinonaktifkan.');
                }
                // Validasi ulang tepat sebelum permintaan; pengalihan dimatikan untuk mengurangi risiko SSRF.
                if (!urlPublikValid($target['alamat_url'])) {
                    throw new InvalidArgumentException('Alamat tidak lolos validasi keamanan saat pemeriksaan.');
                }
                if (!function_exists('curl_init')) {
                    throw new RuntimeException('Ekstensi cURL PHP belum tersedia.');
                }
                $mulai = microtime(true);
                $ch = curl_init($target['alamat_url']);
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => false,
                    CURLOPT_NOBODY => true,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_USERAGENT => 'WebMonitor/0.2',
                ));
                curl_exec($ch);
                $kode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $durasi = (int) round((microtime(true) - $mulai) * 1000);
                $errNo = curl_errno($ch);
                $err = $errNo ? curl_error($ch) : null;
                curl_close($ch);
                $status = (!$errNo && $kode >= 200 && $kode < 400) ? 'aktif' : 'gangguan';
                $ringkas = $err ? substr($err, 0, 500) : (($kode === 0) ? 'Tidak menerima kode HTTP.' : null);
                $stmt = $pdo->prepare('INSERT INTO riwayat_pemeriksaan (situs_id, status, kode_http, waktu_respons_ms, pesan_kesalahan) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute(array($id, $status, $kode ?: null, $durasi, $ringkas));
                $pesan = 'Pemeriksaan selesai: ' . ($status === 'aktif' ? 'respons HTTP berhasil.' : 'gangguan terdeteksi.') . ($kode ? ' Kode HTTP ' . $kode . '.' : '') . ($err ? ' ' . $err : '');
                $jenisPesan = $status === 'aktif' ? 'success' : 'error';
            } else {
                throw new InvalidArgumentException('Aksi tidak dikenal atau parameter tidak valid.');
            }
        } catch (InvalidArgumentException $galat) {
            $pesan = $galat->getMessage();
            $jenisPesan = 'error';
        } catch (Exception $galat) {
            $pesan = 'Operasi gagal. Periksa log server untuk rincian.';
            $jenisPesan = 'error';
            error_log('Web Monitor: operasi gagal: ' . $galat->getMessage());
        }
    }
}

if ($pdo) {
    try {
        if (isset($_GET['edit'])) {
            $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
            if ($editId) {
                $stmt = $pdo->prepare('SELECT id, nama, alamat_url, interval_detik FROM situs_pantauan WHERE id = ?');
                $stmt->execute(array($editId));
                $edit = $stmt->fetch() ?: null;
            }
        }
        if (isset($_GET['riwayat'])) {
            $riwayatId = filter_var($_GET['riwayat'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
            if ($riwayatId) {
                $stmt = $pdo->prepare('SELECT id, nama FROM situs_pantauan WHERE id = ?');
                $stmt->execute(array($riwayatId));
                $riwayatSitus = $stmt->fetch() ?: null;
                if ($riwayatSitus) {
                    $stmt = $pdo->prepare('SELECT status, kode_http, waktu_respons_ms, pesan_kesalahan, diperiksa_pada
                        FROM riwayat_pemeriksaan WHERE situs_id = ? ORDER BY diperiksa_pada DESC, id DESC LIMIT 20');
                    $stmt->execute(array($riwayatId));
                    $riwayat = $stmt->fetchAll();
                }
            }
        }
        $situs = $pdo->query("SELECT s.id, s.nama, s.alamat_url, s.aktif, s.interval_detik,
                r.status, r.kode_http, r.waktu_respons_ms, r.pesan_kesalahan, r.diperiksa_pada
            FROM situs_pantauan s
            LEFT JOIN riwayat_pemeriksaan r ON r.id = (
                SELECT MAX(r2.id) FROM riwayat_pemeriksaan r2 WHERE r2.situs_id = s.id
            ) ORDER BY s.nama ASC")->fetchAll();
    } catch (Exception $galat) {
        $pesan = 'Tidak dapat membaca data situs. Pastikan skema basis data sudah diterapkan.';
        $jenisPesan = 'error';
        error_log('Web Monitor: kueri dasbor gagal.');
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Web Monitor</title>
<style>
:root{font-family:system-ui,sans-serif;color:#20252b;background:#f4f6f8}body{margin:0}header{background:#fff;border-bottom:1px solid #dfe3e8;padding:1.2rem max(1rem,calc((100% - 1100px)/2))}main{max-width:1100px;margin:2rem auto;padding:0 1rem}h1{margin:0;font-size:1.4rem}.muted{color:#5c6672;font-size:.9rem}.panel{background:#fff;border:1px solid #dfe3e8;border-radius:.7rem;padding:1rem;margin-bottom:1rem;overflow-x:auto}.notice{padding:.9rem 1rem;border-radius:.5rem;background:#fff7e6;border:1px solid #f0d69a}.success{background:#e4f7e9;border-color:#a6dfb5}.error{background:#fee2e2;border-color:#f3aaaa}table{width:100%;border-collapse:collapse;min-width:780px}th,td{text-align:left;padding:.7rem .55rem;border-bottom:1px solid #e8ebef;vertical-align:top}th{font-size:.85rem;color:#4a5561}.status{display:inline-block;border-radius:1rem;padding:.2rem .6rem;background:#edf0f3}.aktif{background:#dff5e6;color:#14532d}.gangguan{background:#fee2e2;color:#991b1b}label{display:block;margin:.7rem 0 .25rem}input{box-sizing:border-box;width:100%;max-width:650px;padding:.6rem;border:1px solid #b8c1cb;border-radius:.35rem}button,.button{display:inline-block;padding:.5rem .7rem;border:1px solid #b8c1cb;border-radius:.35rem;background:#fff;color:#175cd3;text-decoration:none;cursor:pointer;font:inherit}form.inline{display:inline}form.inline button{margin:.1rem}.actions{white-space:nowrap}a{color:#175cd3;overflow-wrap:anywhere}
</style>
</head>
<body>
<header><h1>Web Monitor</h1><div class="muted">Kelola situs dan jalankan pemeriksaan HTTP/HTTPS manual.</div></header>
<main>
<?php if ($pesan !== ''): ?><p class="notice <?php echo aman($jenisPesan); ?>"><?php echo aman($pesan); ?></p><?php endif; ?>
<?php if ($pdo): ?>
<section class="panel">
<h2><?php echo $edit ? 'Ubah situs' : 'Tambah situs'; ?></h2>
<form method="post" action="index.php">
<input type="hidden" name="token" value="<?php echo aman($token); ?>"><input type="hidden" name="aksi" value="simpan">
<?php if ($edit): ?><input type="hidden" name="id" value="<?php echo aman($edit['id']); ?>"><?php endif; ?>
<label for="nama">Nama situs</label><input id="nama" name="nama" maxlength="150" required value="<?php echo aman($edit ? $edit['nama'] : ''); ?>">
<label for="alamat_url">Alamat URL</label><input id="alamat_url" name="alamat_url" type="url" maxlength="2048" placeholder="https://contoh.com" required value="<?php echo aman($edit ? $edit['alamat_url'] : ''); ?>">
<label for="interval_detik">Interval pemeriksaan (detik, minimal 60)</label><input id="interval_detik" name="interval_detik" type="number" min="60" max="86400" required value="<?php echo aman($edit ? $edit['interval_detik'] : 300); ?>">
<p><button type="submit">Simpan situs</button> <?php if ($edit): ?><a class="button" href="index.php">Batal</a><?php endif; ?></p>
</form>
<p class="muted">Pemeriksaan manual memakai cURL, batas waktu koneksi 5 detik dan total 15 detik, tanpa mengikuti pengalihan. Alamat IP privat/lokal ditolak. Aplikasi ini belum memiliki autentikasi; jangan publikasikan.</p>
</section>
<?php if ($riwayatSitus): ?>
<section class="panel">
<h2>Riwayat pemeriksaan: <?php echo aman($riwayatSitus['nama']); ?></h2>
<p class="muted">Menampilkan maksimal 20 pemeriksaan terbaru.</p>
<?php if (!$riwayat): ?><p>Belum ada riwayat pemeriksaan.</p><?php else: ?>
<table><thead><tr><th>Waktu</th><th>Status</th><th>HTTP</th><th>Respons</th><th>Keterangan</th></tr></thead><tbody>
<?php foreach ($riwayat as $item): ?>
<tr><td><?php echo aman($item['diperiksa_pada']); ?></td>
<td><span class="status <?php echo aman($item['status']); ?>"><?php echo aman(ucfirst($item['status'])); ?></span></td>
<td><?php echo aman($item['kode_http'] === null ? '—' : $item['kode_http']); ?></td>
<td><?php echo aman($item['waktu_respons_ms'] === null ? '—' : $item['waktu_respons_ms'] . ' ms'); ?></td>
<td><?php echo aman($item['pesan_kesalahan'] ? $item['pesan_kesalahan'] : '—'); ?></td></tr>
<?php endforeach; ?>
</tbody></table><?php endif; ?>
<p><a class="button" href="index.php">Tutup riwayat</a></p>
</section>
<?php endif; ?>
<section class="panel"><h2>Daftar situs</h2>
<?php if (!$situs): ?><p>Belum ada situs terdaftar.</p><?php else: ?>
<table><thead><tr><th>Situs</th><th>Status</th><th>HTTP</th><th>Respons</th><th>Pemeriksaan terakhir</th><th>Tindakan</th></tr></thead><tbody>
<?php foreach ($situs as $baris): ?>
<tr><td><strong><?php echo aman($baris['nama']); ?></strong><br><a href="<?php echo aman($baris['alamat_url']); ?>" rel="noreferrer noopener"><?php echo aman($baris['alamat_url']); ?></a><br><span class="muted">Setiap <?php echo aman($baris['interval_detik']); ?> detik</span></td>
<td><span class="status <?php echo aman($baris['status'] ? $baris['status'] : ''); ?>"><?php echo aman($baris['status'] ? ucfirst(str_replace('_',' ',$baris['status'])) : ($baris['aktif'] ? 'Belum diperiksa' : 'Nonaktif')); ?></span></td>
<td><?php echo aman($baris['kode_http'] === null ? '—' : $baris['kode_http']); ?></td><td><?php echo aman($baris['waktu_respons_ms'] === null ? '—' : $baris['waktu_respons_ms'].' ms'); ?></td><td><?php echo aman($baris['diperiksa_pada'] ? $baris['diperiksa_pada'] : '—'); ?><?php if ($baris['pesan_kesalahan']): ?><br><span class="muted"><?php echo aman($baris['pesan_kesalahan']); ?></span><?php endif; ?></td>
<td class="actions"><a class="button" href="index.php?riwayat=<?php echo aman($baris['id']); ?>">Riwayat</a>
<a class="button" href="index.php?edit=<?php echo aman($baris['id']); ?>">Ubah</a>
<form class="inline" method="post"><input type="hidden" name="token" value="<?php echo aman($token); ?>"><input type="hidden" name="id" value="<?php echo aman($baris['id']); ?>"><input type="hidden" name="aksi" value="toggle"><button type="submit"><?php echo $baris['aktif'] ? 'Nonaktifkan' : 'Aktifkan'; ?></button></form>
<?php if ($baris['aktif']): ?><form class="inline" method="post"><input type="hidden" name="token" value="<?php echo aman($token); ?>"><input type="hidden" name="id" value="<?php echo aman($baris['id']); ?>"><input type="hidden" name="aksi" value="periksa"><button type="submit">Periksa</button></form><?php endif; ?>
<form class="inline" method="post" onsubmit="return confirm('Hapus situs dan seluruh riwayatnya?');"><input type="hidden" name="token" value="<?php echo aman($token); ?>"><input type="hidden" name="id" value="<?php echo aman($baris['id']); ?>"><input type="hidden" name="aksi" value="hapus"><button type="submit">Hapus</button></form></td></tr>
<?php endforeach; ?>
</tbody></table><?php endif; ?></section>
<?php endif; ?>
<p class="muted">Versi awal Fase 1. Autentikasi, pemeriksaan DNS/redirect menyeluruh, penjadwalan otomatis, dan audit keamanan masih diperlukan sebelum penggunaan publik.</p>
</main></body></html>
