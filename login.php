<?php
// Hanya include config.php sekali saja
require_once 'includes/config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$page_title = "Login";
$no_sidebar = true; // Tambahkan ini untuk menonaktifkan header/footer
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (login($username, $password)) {
        $_SESSION['message'] = "Login berhasil!";
        $_SESSION['message_type'] = "success";
        
        // Redirect berdasarkan role
        switch($_SESSION['user_role']) {
            case 'admin':
                redirect('admin/index.php');
                break;
            case 'kasir':
                redirect('kasir/index.php');
                break;
            default:
                redirect('customer/index.php');
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!-- Tambahkan CSS khusus untuk halaman login -->
<style>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #f5f7ff 0%, #e6ebff 100%);
    padding: 20px 0;
}

.login-container {
    max-width: 400px;
    width: 100%;
    margin: 0 auto;
}

.login-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.login-card .card-header {
    border-radius: 15px 15px 0 0 !important;
    padding: 2rem;
    text-align: center;
}

.login-card .card-body {
    padding: 2rem;
}

.btn-login {
    padding: 12px;
    font-weight: 600;
    border-radius: 8px;
}

.form-control {
    padding: 12px;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
}
</style>

<div class="login-page">
    <div class="container">
        <div class="login-container">
            <div class="login-card card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username atau Email</label>
                            <input type="text" class="form-control" id="username" name="username" required 
                                   placeholder="Masukkan username atau email">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required 
                                   placeholder="Masukkan password">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-login">Login</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Belum punya akun? <a href="register.php" class="text-primary">Daftar di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>