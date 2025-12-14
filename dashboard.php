<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$page_title = "Dashboard";
include 'includes/header.php';

// Redirect berdasarkan role
switch($_SESSION['user_role']) {
    case 'admin':
        redirect('admin/index.php');
        break;
    case 'kasir':
        redirect('kasir/index.php');
        break;
    case 'customer':
        redirect('customer/index.php');
        break;
    default:
        echo "Role tidak dikenali";
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5>Mengarahkan ke dashboard...</h5>
                    <p class="text-muted">Silakan tunggu sebentar</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
setTimeout(function() {
    window.location.href = '<?php 
        switch($_SESSION['user_role']) {
            case 'admin': echo 'admin/index.php'; break;
            case 'kasir': echo 'kasir/index.php'; break;
            case 'customer': echo 'customer/index.php'; break;
            default: echo 'index.php';
        }
    ?>';
}, 2000);
</script>

<?php include 'includes/footer.php'; ?>