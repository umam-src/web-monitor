<?php
// Web Monitor PHP 7.4. Jangan publikasikan sebelum autentikasi dan audit keamanan tersedia.
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(array(
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
));
session_start();

function aman($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

function urlTargetValid($url, $izinkanLokal = false)
{
    if (!is_string($url) || $url === '' || strlen($url) > 2048 ||
        preg_match('/[\\x00-\\x20\\x7f]/', $url)) {
        return false;
    }
    $bagian = parse_url($url);
    if (!$bagian || !isset($bagian['scheme'], $bagian['host']) ||
        !in_array(strtolower($bagian['scheme']), array('http', 'https'), true) ||
        isset($bagian['user']) || isset($bagian['pass']) ||
        (isset($bagian['port']) && ($bagian['port'] < 1 || $bagian['port'] > 65535))) {
        return false;
    }
    $host = trim($bagian['host'], '[]');
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        if ($izinkanLokal) {
            return true;
        }
        return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    // Mode lokal menerima hostname intranet, termasuk nama satu label dan domain AD.
    // Dalam mode publik, pertahankan syarat hostname publik dan resolusi ke IP publik.
    if ($izinkanLokal) {
        if (preg_match('/^[0-9.]+$/', $host) || strlen($host) > 253 || !preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9.-]*[a-zA-Z0-9])?$/', $host) ||
            strpos($host, '..') !== false) {
            return false;
        }
        foreach (explode('.', $host) as $label) {
            if ($label === '' || strlen($label) > 63 ||
                !preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/', $label)) {
                return false;
            }
        }
        return true;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL) ||
        strpos($host, '.') === false || preg_match('/\\.local$/i', $host)) {
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
    $pesan = 'Pengaturan belum tersedia. Jalankan wizard instalasi otomatis melalui setup.php.';
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

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_SESSION['pengguna_id'])) {
    if (!tokenValid()) {
        http_response_code(400);
        $pesan = 'Permintaan tidak valid atau sesi kedaluwarsa. Muat ulang halaman.';
        $jenisPesan = 'error';
    } elseif (!isset($_POST['aksi']) || $_POST['aksi'] !== 'masuk') {
        $pesan = 'Silakan masuk terlebih dahulu.';
        $jenisPesan = 'error';
    } else {
        $namaPengguna = trim(isset($_POST['nama_pengguna']) && is_string($_POST['nama_pengguna']) ? $_POST['nama_pengguna'] : '');
        $kataSandi = isset($_POST['kata_sandi']) && is_string($_POST['kata_sandi']) ? $_POST['kata_sandi'] : '';
        try {
            $stmt = $pdo->prepare('SELECT id, nama_pengguna, hash_kata_sandi FROM pengguna_aplikasi WHERE nama_pengguna = ? LIMIT 1');
            $stmt->execute(array($namaPengguna));
            $akun = $stmt->fetch();
            if ($akun && password_verify($kataSandi, $akun['hash_kata_sandi'])) {
                session_regenerate_id(true);
                $_SESSION['pengguna_id'] = (int) $akun['id'];
                $_SESSION['nama_pengguna'] = $akun['nama_pengguna'];
                $_SESSION['token'] = bin2hex(random_bytes(32));
                $token = $_SESSION['token'];
                $pesan = 'Berhasil masuk.';
                $jenisPesan = 'success';
            } else {
                $pesan = 'Nama pengguna atau kata sandi salah.';
                $jenisPesan = 'error';
            }
        } catch (Exception $galat) {
            $pesan = 'Tabel akun belum tersedia. Jalankan wizard instalasi melalui setup.php.';
            $jenisPesan = 'error';
            error_log('Web Monitor: autentikasi gagal karena tabel akun tidak tersedia.');
        }
    }
}

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['pengguna_id']) && (!isset($_POST['aksi']) || $_POST['aksi'] !== 'masuk')) {
    if (!tokenValid()) {
        http_response_code(400);
        $pesan = 'Permintaan tidak valid atau sesi kedaluwarsa. Muat ulang halaman.';
        $jenisPesan = 'error';
    } else {
        $aksi = isset($_POST['aksi']) && is_string($_POST['aksi']) ? $_POST['aksi'] : '';
        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
        try {
            if ($aksi === 'keluar') {
                unset($_SESSION['pengguna_id'], $_SESSION['nama_pengguna']);
                session_regenerate_id(true);
                $_SESSION['token'] = bin2hex(random_bytes(32));
                $token = $_SESSION['token'];
                $pesan = 'Anda sudah keluar.';
                $jenisPesan = 'success';
            } elseif ($aksi === 'simpan') {
                $nama = trim(isset($_POST['nama']) && is_string($_POST['nama']) ? $_POST['nama'] : '');
                $url = trim(isset($_POST['alamat_url']) && is_string($_POST['alamat_url']) ? $_POST['alamat_url'] : '');
                $interval = isset($_POST['interval_detik']) ? filter_var($_POST['interval_detik'], FILTER_VALIDATE_INT) : false;
                if ($nama === '' || strlen($nama) > 150) {
                    throw new InvalidArgumentException('Nama wajib diisi dan maksimal 150 karakter.');
                }
                $izinkanLokal = isset($pengaturan['izinkan_alamat_lokal']) && $pengaturan['izinkan_alamat_lokal'] === true;
                if (!urlTargetValid($url, $izinkanLokal)) {
                    throw new InvalidArgumentException('Alamat harus berupa URL HTTP/HTTPS valid. Target privat/lokal hanya diizinkan jika pengaturan izinkan_alamat_lokal aktif.');
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
                $izinkanLokal = isset($pengaturan['izinkan_alamat_lokal']) && $pengaturan['izinkan_alamat_lokal'] === true;
                if (!urlTargetValid($target['alamat_url'], $izinkanLokal)) {
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
:root{font-family:system-ui,sans-serif;color:#20252b;background:#f4f6f8}body{margin:0}header{background:#fff;border-bottom:1px solid #dfe3e8;padding:1.2rem max(1rem,calc((100% - 1100px)/2))}main{max-width:1100px;margin:2rem auto;padding:0 1rem}h1{margin:0;font-size:1.4rem}.muted{color:#5c6672;font-size:.9rem}.panel{background:#fff;border:1px solid #dfe3e8;border-radius:.7rem;padding:1rem;margin-bottom:1rem;overflow-x:auto}.notice{padding:.9rem 1rem;border-radius:.5rem;background:#fff7e6;border:1px solid #f0d69a}.success{background:#e4f7e9;border-color:#a6dfb5}.error{background:#fee2e2;border-color:#f3aaaa}table{width:100%;border-collapse:collapse;min-width:780px}th,td{text-align:left;padding:.7rem .55rem;border-bottom:1px solid #e8ebef;vertical-align:top}th{font-size:.85rem;color:#4a5561}.status{display:inline-block;border-radius:1rem;padding:.2rem .6rem;background:#edf0f3}.aktif{background:#dff5e6;color:#14532d}.gangguan{background:#fee2e2;color:#991b1b}label{display:block;margin:.7rem 0 .25rem}input{box-sizing:border-box;width:100%;max-width:100%;padding:.6rem;border:1px solid #b8c1cb;border-radius:.35rem}button,.button{display:inline-block;padding:.5rem .7rem;border:1px solid #b8c1cb;border-radius:.35rem;background:#fff;color:#175cd3;text-decoration:none;cursor:pointer;font:inherit}form.inline{display:inline}form.inline button{margin:.1rem}.actions{white-space:nowrap}a{color:#175cd3;overflow-wrap:anywhere}.button-primary{background:#16834a;border-color:#16834a;color:#fff}.button-danger{background:#c62828;border-color:#c62828;color:#fff}.dashboard-toolbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin:1rem 0}.dashboard-toolbar h2{margin:0}.page-footer{display:flex;justify-content:flex-end;margin:2rem 0 1rem;padding-top:1rem;border-top:1px solid #dfe3e8}.dialog-situs{box-sizing:border-box;width:min(560px,calc(100vw - 2rem));min-width:min(300px,calc(100vw - 2rem));max-width:560px;height:min(560px,calc(100dvh - 2rem));min-height:min(320px,calc(100dvh - 2rem));max-height:calc(100dvh - 2rem);padding:0;border:1px solid #cbd2da;border-radius:.8rem;box-shadow:0 20px 60px #0003;overflow:hidden}.dialog-situs::backdrop{background:#17212b80}.dialog-layout{display:flex;flex-direction:column;height:100%;min-height:0}.dialog-layout>form{flex:1;min-height:0;height:auto}.dialog-head,.dialog-foot{flex:none;padding:1rem 1.2rem;background:#fff}.dialog-head{border-bottom:1px solid #e8ebef}.dialog-head h2{margin:0}.dialog-body{padding:.25rem 1.2rem 1rem;overflow:auto;flex:1;min-height:0}.dialog-foot{border-top:1px solid #e8ebef;display:flex;gap:.5rem;justify-content:flex-end;align-items:center}.dialog-head-row{display:flex;align-items:center;justify-content:space-between;gap:1rem}.dialog-close{font-size:1.2rem;color:#20252b;padding:.25rem .55rem}.field-help{margin:.6rem 0 0}@media(max-width:480px){.dialog-situs{width:calc(100vw - 1rem);min-width:0;height:calc(100dvh - 1rem);min-height:0;max-height:calc(100dvh - 1rem);border-radius:.6rem}.dialog-head,.dialog-foot{padding:.8rem}.dialog-body{padding:.2rem .8rem .8rem}}
</style>
</head>
<body>
<header><h1>Web Monitor</h1><div class="muted">Kelola situs dan jalankan pemeriksaan HTTP/HTTPS manual.</div></header>
<main>
<?php if ($pesan !== ''): ?><p class="notice <?php echo aman($jenisPesan); ?>"><?php echo aman($pesan); ?></p><?php endif; ?>
<?php if ($pdo && !empty($_SESSION['pengguna_id'])): ?>
<p class="muted">Masuk sebagai <?php echo aman($_SESSION['nama_pengguna']); ?>.</p>
<dialog id="dialog-situs" class="dialog-situs" aria-labelledby="judul-dialog-situs">
<div class="dialog-layout">
<div class="dialog-head"><div class="dialog-head-row"><h2 id="judul-dialog-situs"><?php echo $edit ? 'Ubah situs' : 'Tambah situs'; ?></h2><button type="button" class="dialog-close" id="tutup-dialog-situs" aria-label="Tutup dialog">×</button></div></div>
<form method="post" action="index.php" class="dialog-layout">
<div class="dialog-body">
<input type="hidden" name="token" value="<?php echo aman($token); ?>"><input type="hidden" name="aksi" value="simpan">
<?php if ($edit): ?><input type="hidden" name="id" value="<?php echo aman($edit['id']); ?>"><?php endif; ?>
<label for="nama">Nama situs</label><input id="nama" name="nama" maxlength="150" required value="<?php echo aman($edit ? $edit['nama'] : ''); ?>">
<label for="alamat_url">Alamat URL</label><input id="alamat_url" name="alamat_url" type="url" maxlength="2048" placeholder="https://contoh.com" required value="<?php echo aman($edit ? $edit['alamat_url'] : ''); ?>">
<label for="interval_detik">Interval pemeriksaan (detik, minimal 60)</label><input id="interval_detik" name="interval_detik" type="number" min="60" max="86400" required value="<?php echo aman($edit ? $edit['interval_detik'] : 300); ?>">
<p class="muted field-help">Pemeriksaan manual memakai cURL, batas waktu koneksi 5 detik dan total 15 detik, tanpa mengikuti pengalihan. Target privat/lokal mengikuti pengaturan izinkan_alamat_lokal di pengaturan.php (default: nonaktif). Mengaktifkannya meningkatkan risiko SSRF; batasi akses dashboard ke jaringan tepercaya dan jangan publikasikan sebelum audit keamanan selesai.</p>
</div>
<div class="dialog-foot"><button type="button" id="batal-dialog-situs">Batal</button><button type="submit">Simpan situs</button></div>
</form>
</div>
</dialog>
<script>
(function () {
    var dialog = document.getElementById('dialog-situs');
    var buka = document.getElementById('buka-dialog-situs');
    var tutup = document.getElementById('tutup-dialog-situs');
    var batal = document.getElementById('batal-dialog-situs');
    if (!dialog || !buka || typeof dialog.showModal !== 'function') return;
    function tutupDialog() { dialog.close(); }
    buka.addEventListener('click', function () { dialog.showModal(); });
    tutup.addEventListener('click', tutupDialog);
    batal.addEventListener('click', tutupDialog);
    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) tutupDialog();
    });
    <?php if ($edit): ?>dialog.showModal();<?php endif; ?>
})();
</script>
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
<section class="panel"><div class="dashboard-toolbar"><h2>Daftar situs</h2><button type="button" id="buka-dialog-situs" class="button-primary"><?php echo $edit ? 'Tambah situs lain' : 'Tambah situs'; ?></button></div>
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
<?php elseif ($pdo): ?>
<section class="panel">
<h2>Masuk ke Web Monitor</h2>
<form method="post" action="index.php" autocomplete="on">
<input type="hidden" name="token" value="<?php echo aman($token); ?>">
<input type="hidden" name="aksi" value="masuk">
<label for="nama_pengguna">Nama pengguna</label><input id="nama_pengguna" name="nama_pengguna" maxlength="80" autocomplete="username" required>
<label for="kata_sandi">Kata sandi</label><input id="kata_sandi" name="kata_sandi" type="password" autocomplete="current-password" required>
<p><button type="submit">Masuk</button></p>
</form>
</section>
<?php elseif (!is_file($berkasPengaturan)): ?>
<p><a class="button" href="setup.php">Mulai instalasi otomatis</a></p>
<?php endif; ?>
<p class="muted">Versi awal Fase 1. Autentikasi, pemeriksaan DNS/redirect menyeluruh, penjadwalan otomatis, dan audit keamanan masih diperlukan sebelum penggunaan publik.</p>
<?php if ($pdo && !empty($_SESSION['pengguna_id'])): ?>
<footer class="page-footer"><form method="post"><input type="hidden" name="token" value="<?php echo aman($token); ?>"><input type="hidden" name="aksi" value="keluar"><button type="submit" class="button-danger">Keluar</button></form></footer>
<?php endif; ?>
</main></body></html>
