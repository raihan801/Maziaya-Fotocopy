<?php
include 'includes/config.php';
$page_title = "Akses Ditolak";
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card">
                <div class="card-body py-5">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                    <h2>Akses Ditolak</h2>
                    <p class="text-muted">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary me-2">Beranda</a>
                        <a href="login.php" class="btn btn-outline-primary">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>