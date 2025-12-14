<?php
include 'includes/config.php';
include 'includes/auth.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$page_title = "Daftar";
$no_sidebar = true; // Tambahkan ini untuk menonaktifkan header/footer
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    // Validasi
    if ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak sama!";
    } else {
        $result = register($username, $email, $password, $full_name, $phone, $address);
        
        if ($result['success']) {
            $_SESSION['message'] = $result['message'];
            $_SESSION['message_type'] = "success";
            redirect('login.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!-- Tambahkan CSS khusus untuk halaman register -->
<style>
.register-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #f5f7ff 0%, #e6ebff 100%);
    padding: 20px 0;
}

.register-container {
    max-width: 800px;
    width: 100%;
    margin: 0 auto;
}

.register-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.register-card .card-header {
    border-radius: 15px 15px 0 0 !important;
    padding: 2rem;
    text-align: center;
}

.register-card .card-body {
    padding: 2rem;
}

.btn-register {
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

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}
</style>

<div class="register-page">
    <div class="container">
        <div class="register-container">
            <div class="register-card card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Daftar Akun Baru</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" required
                                           placeholder="Masukkan username">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                           placeholder="Masukkan email">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required
                                           placeholder="Masukkan password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                           placeholder="Konfirmasi password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required
                                   placeholder="Masukkan nama lengkap">
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   placeholder="Masukkan nomor telepon">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3"
                                      placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-register">Daftar</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Sudah punya akun? <a href="login.php" class="text-primary">Login di sini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>