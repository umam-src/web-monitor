<?php
// Wizard instalasi Web Monitor untuk PHP 7.4. Hanya untuk akses lokal/LAN.
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(array(
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
));
session_start();

function setupAman($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

function setupLokal()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    if ($ip === '127.0.0.1' || $ip === '::1') {
        return true;
    }
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $angka = ip2long($ip);
        return (($angka & ip2long('255.0.0.0')) === ip2long('10.0.0.0')) ||
            (($angka & ip2long('255.240.0.0')) === ip2long('172.16.0.0')) ||
            (($angka & ip2long('255.255.0.0')) === ip2long('192.168.0.0'));
    }
    $ipKecil = strtolower($ip);
    return strpos($ipKecil, 'fc') === 0 || strpos($ipKecil, 'fd') === 0 ||
        strpos($ipKecil, 'fe80:') === 0;
}

$berkasPengaturan = __DIR__ . '/pengaturan.php';
if (is_file($berkasPengaturan)) {
    http_response_code(404);
    exit('Wizard instalasi sudah dikunci.');
}
if (!setupLokal()) {
    http_response_code(403);
    exit('Setup hanya dapat dibuka dari perangkat lokal atau jaringan privat.');
}
if (empty($_SESSION['setup_token'])) {
    $_SESSION['setup_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['setup_token'];
$pesan = '';
$berhasil = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['token'], $_SESSION['setup_token']) ||
            !is_string($_POST['token']) || !hash_equals($_SESSION['setup_token'], $_POST['token'])) {
            throw new RuntimeException('Sesi tidak valid. Muat ulang halaman dan coba lagi.');
        }
        $host = trim(isset($_POST['host']) && is_string($_POST['host']) ? $_POST['host'] : '127.0.0.1');
        $database = trim(isset($_POST['database']) && is_string($_POST['database']) ? $_POST['database'] : '');
        $pengguna = trim(isset($_POST['pengguna']) && is_string($_POST['pengguna']) ? $_POST['pengguna'] : '');
        $rahasia = isset($_POST['rahasia']) && is_string($_POST['rahasia']) ? $_POST['rahasia'] : '';
        $admin = trim(isset($_POST['admin']) && is_string($_POST['admin']) ? $_POST['admin'] : '');
        $passwordAdmin = isset($_POST['password_admin']) && is_string($_POST['password_admin']) ? $_POST['password_admin'] : '';
        if (!preg_match('/^[a-zA-Z0-9_.-]{1,100}$/', $host)) {
            throw new InvalidArgumentException('Host tidak valid.');
        }
        if (!preg_match('/^[a-zA-Z0-9_]{1,64}$/', $database)) {
            throw new InvalidArgumentException('Nama database hanya boleh berisi huruf, angka, dan garis bawah (maksimal 64 karakter).');
        }
        if ($pengguna === '' || strlen($pengguna) > 128) {
            throw new InvalidArgumentException('Nama pengguna MariaDB wajib diisi.');
        }
        if (!preg_match('/^[a-zA-Z0-9_.@-]{3,80}$/', $admin)) {
            throw new InvalidArgumentException('Nama administrator harus 3–80 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, @, atau tanda hubung.');
        }
        if (strlen($passwordAdmin) < 12) {
            throw new InvalidArgumentException('Kata sandi administrator minimal 12 karakter.');
        }
        if (PHP_VERSION_ID < 70400) {
            throw new RuntimeException('Web Monitor membutuhkan PHP 7.4 atau lebih baru.');
        }
        $ekstensiWajib = array('pdo', 'pdo_mysql', 'curl', 'openssl');
        $ekstensiHilang = array();
        foreach ($ekstensiWajib as $ekstensi) {
            if (!extension_loaded($ekstensi)) {
                $ekstensiHilang[] = $ekstensi;
            }
        }
        if ($ekstensiHilang) {
            throw new RuntimeException('Aktifkan ekstensi PHP berikut pada profil situs melalui Web Station: ' . implode(', ', $ekstensiHilang) . '.');
        }
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('Ekstensi PDO MySQL belum aktif pada profil PHP situs ini. Aktifkan melalui Web Station, lalu muat ulang wizard.');
        }
        if (!is_writable(__DIR__)) {
            throw new RuntimeException('Folder aplikasi tidak dapat ditulis oleh PHP sehingga berkas konfigurasi tidak bisa dibuat. Periksa izin folder melalui File Station/DSM.');
        }

        $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $pengguna, $rahasia, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ));

        // Membuat skema langsung dari wizard. CREATE TABLE IF NOT EXISTS membuat percobaan ulang aman.
        $pernyataan = array(
            "CREATE TABLE IF NOT EXISTS situs_pantauan (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                nama VARCHAR(150) NOT NULL,
                alamat_url VARCHAR(2048) NOT NULL,
                situs_lokal TINYINT(1) NOT NULL DEFAULT 0,
                aktif TINYINT(1) NOT NULL DEFAULT 1,
                interval_detik INT UNSIGNED NOT NULL DEFAULT 300,
                dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                diperbarui_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_situs_pantauan_alamat_url (alamat_url(191)),
                KEY idx_situs_pantauan_aktif (aktif)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS riwayat_pemeriksaan (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                situs_id BIGINT UNSIGNED NOT NULL,
                status ENUM('aktif', 'gangguan', 'tidak_diketahui') NOT NULL DEFAULT 'tidak_diketahui',
                kode_http SMALLINT UNSIGNED NULL,
                waktu_respons_ms INT UNSIGNED NULL,
                pesan_kesalahan VARCHAR(500) NULL,
                diperiksa_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_riwayat_situs_waktu (situs_id, diperiksa_pada),
                CONSTRAINT fk_riwayat_pemeriksaan_situs FOREIGN KEY (situs_id)
                    REFERENCES situs_pantauan(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS pengguna_aplikasi (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                nama_pengguna VARCHAR(80) NOT NULL,
                hash_kata_sandi VARCHAR(255) NOT NULL,
                dibuat_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_pengguna_aplikasi_nama (nama_pengguna)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        foreach ($pernyataan as $sql) {
            $pdo->exec($sql);
        }

        $hash = password_hash($passwordAdmin, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO pengguna_aplikasi (nama_pengguna, hash_kata_sandi) VALUES (?, ?) ON DUPLICATE KEY UPDATE hash_kata_sandi = VALUES(hash_kata_sandi)');
        $stmt->execute(array($admin, $hash));

        $konfigurasi = "<?php\n// Dibuat oleh wizard Web Monitor. Jangan masukkan ke Git.\nreturn " .
            var_export(array('dsn' => $dsn, 'pengguna' => $pengguna, 'rahasia' => $rahasia), true) . ";\n";
        $sementara = $berkasPengaturan . '.tmp';
        if (file_put_contents($sementara, $konfigurasi, LOCK_EX) === false ||
            !@rename($sementara, $berkasPengaturan)) {
            @unlink($sementara);
            throw new RuntimeException('Tabel dan administrator telah dibuat, tetapi konfigurasi gagal disimpan. Periksa izin folder melalui File Station, lalu jalankan wizard lagi dengan nama administrator yang sama.');
        }
        @chmod($berkasPengaturan, 0600);
        unset($rahasia, $passwordAdmin, $_POST['rahasia'], $_POST['password_admin']);
        unset($_SESSION['setup_token']);
        $berhasil = true;
        $pesan = 'Instalasi berhasil. Tabel dibuat otomatis dan akun administrator siap digunakan. Wizard sekarang terkunci.';
    } catch (InvalidArgumentException $galat) {
        $pesan = $galat->getMessage();
    } catch (PDOException $galat) {
        $pesan = 'Koneksi atau pembuatan tabel gagal. Pastikan host, database, dan akun MariaDB benar serta akun tersebut memiliki hak membuat tabel pada database. Detail teknis tidak ditampilkan demi keamanan.';
        error_log('Web Monitor setup: operasi MariaDB gagal: ' . $galat->getMessage());
    } catch (Exception $galat) {
        $pesan = $galat->getMessage();
        error_log('Web Monitor setup: ' . $galat->getMessage());
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Instalasi Web Monitor</title>
<style>
:root{font-family:system-ui,sans-serif;color:#20252b;background:#f4f6f8}body{margin:0}main{max-width:720px;margin:2rem auto;padding:0 1rem}.panel{background:#fff;border:1px solid #dfe3e8;border-radius:.7rem;padding:1.25rem;margin-bottom:1rem}label{display:block;margin:.8rem 0 .25rem}input{box-sizing:border-box;width:100%;padding:.7rem;border:1px solid #b8c1cb;border-radius:.35rem}button,a.button{display:inline-block;padding:.65rem .9rem;border:1px solid #b8c1cb;border-radius:.35rem;background:#fff;color:#175cd3;text-decoration:none;cursor:pointer;font:inherit}.notice{padding:.9rem 1rem;border-radius:.5rem;background:#fff7e6;border:1px solid #f0d69a}.success{background:#e4f7e9;border-color:#a6dfb5}.muted{color:#5c6672;font-size:.9rem}
</style>
</head>
<body><main>
<section class="panel">
<h1>Instalasi Web Monitor</h1>
<p>Wizard ini menguji koneksi ke database yang sudah dibuat, membuat tabel secara otomatis, dan menyiapkan administrator pertama. Tidak perlu membuka phpMyAdmin.</p>
<?php if ($pesan !== ''): ?><p class="notice <?php echo $berhasil ? 'success' : ''; ?>"><?php echo setupAman($pesan); ?></p><?php endif; ?>
<?php if ($berhasil): ?>
<p><a class="button" href="index.php">Masuk ke Web Monitor</a></p>
<?php else: ?>
<form method="post" autocomplete="off">
<input type="hidden" name="token" value="<?php echo setupAman($token); ?>">
<h2>Koneksi database yang sudah tersedia</h2>
<label for="host">Host MariaDB</label><input id="host" name="host" value="127.0.0.1" required>
<label for="database">Nama database</label><input id="database" name="database" maxlength="64" required>
<label for="pengguna">Nama pengguna MariaDB</label><input id="pengguna" name="pengguna" maxlength="128" required>
<label for="rahasia">Kata sandi MariaDB</label><input id="rahasia" name="rahasia" type="password" autocomplete="new-password">
<p class="muted">Akun harus memiliki hak membuat tabel dan hak baca/tulis pada database ini. Kata sandi ini hanya digunakan untuk setup dan koneksi aplikasi yang disimpan di konfigurasi terlindungi.</p>
<h2>Administrator Web Monitor</h2>
<label for="admin">Nama pengguna administrator</label><input id="admin" name="admin" minlength="3" maxlength="80" required>
<label for="password_admin">Kata sandi administrator (minimal 12 karakter)</label><input id="password_admin" name="password_admin" type="password" minlength="12" autocomplete="new-password" required>
<p><button type="submit">Uji koneksi dan instal otomatis</button></p>
</form>
<?php endif; ?>
<p class="muted">Untuk keamanan, wizard hanya menerima koneksi lokal atau jaringan privat. Setelah berhasil, berkas konfigurasi akan mengunci wizard.</p>
</section>
</main></body></html>
