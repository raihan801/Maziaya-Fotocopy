<?php
// Cek apakah fungsi sudah dideklarasikan
if (!function_exists('isLoggedIn')) {

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Cek role user
function checkRole($allowed_roles) {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
    
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        header("Location: ../unauthorized.php");
        exit();
    }
}

// Login user
function login($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        return true;
    }
    
    return false;
}

// Logout user
function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Register user baru
function register($username, $email, $password, $full_name, $phone = '', $address = '') {
    global $pdo;
    
    // Cek apakah username atau email sudah ada
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Username atau email sudah terdaftar.'];
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $address])) {
        return ['success' => true, 'message' => 'Registrasi berhasil. Silakan login.'];
    } else {
        return ['success' => false, 'message' => 'Terjadi kesalahan saat registrasi.'];
    }
}

} // End if !function_exists
?>