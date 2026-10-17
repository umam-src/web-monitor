<?php
// Salin menjadi pengaturan.php dan isi nilai lokal.
return array(
    'dsn' => 'mysql:host=127.0.0.1;dbname=web_monitor;charset=utf8mb4',
    'pengguna' => 'web_monitor',
    'rahasia' => '',\n    // Ubah menjadi true hanya jika perlu memantau target intranet/IP privat.\n    'izinkan_alamat_lokal' => false,
);
