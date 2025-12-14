<?php
// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'maziaya_fotocopy');
define('SITE_NAME', 'Maziaya Fotocopy');
define('SITE_URL', 'http://localhost/maziaya_fotocopy');

// Koneksi database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk mencegah SQL injection
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Include functions (gunakan require_once untuk menghindari redeclaration)
if (!function_exists('redirect')) {
    require_once 'functions.php';
}

if (!function_exists('isLoggedIn')) {
    require_once 'auth.php';
}
?>