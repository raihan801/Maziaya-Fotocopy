<?php
// Sidebar navigation untuk semua role
if (!function_exists('isLoggedIn') || !isLoggedIn()) {
    return;
}

global $pdo;
$user_id = $_SESSION['user_id'];

// Ambil foto user
$stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

$photo = $user_data['profile_photo'] ?: 'default.png';
$photo_url = "../uploads/profile/" . $photo;

// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="col-md-3 col-lg-2 bg-dark text-white vh-100 position-fixed sidebar">
    <div class="sidebar-sticky pt-3">

        <!-- User Info -->
        <div class="text-center mb-4 p-3 border-bottom">

            <div class="user-avatar mb-2">
    <a href="../customer/profile.php" style="text-decoration: none;">
        <img src="<?php echo $photo_url; ?>" 
             class="rounded-circle"
             style="width: 70px; height: 70px; object-fit: cover; border: 3px solid #aaa; cursor: pointer;">
    </a>
</div>


            <h6 class="mb-1"><?php echo $_SESSION['full_name']; ?></h6>

            <span class="badge 
                <?php 
                switch($_SESSION['user_role']) {
                    case 'admin': echo 'bg-danger'; break;
                    case 'kasir': echo 'bg-warning'; break;
                    case 'customer': echo 'bg-info'; break;
                    default: echo 'bg-secondary';
                }
                ?>">
                <?php echo ucfirst($_SESSION['user_role']); ?>
            </span>
        </div>

        <!-- Navigation Menu -->
        <ul class="nav flex-column">

            <?php if ($_SESSION['user_role'] == 'admin'): ?>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'index.php' ? 'active bg-primary' : ''; ?>" href="index.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'orders.php' ? 'active bg-primary' : ''; ?>" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'customers.php' ? 'active bg-primary' : ''; ?>" href="customers.php">
                        <i class="fas fa-users me-2"></i>Kelola Customer
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'services.php' ? 'active bg-primary' : ''; ?>" href="services.php">
                        <i class="fas fa-cog me-2"></i>Kelola Layanan
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'reports.php' ? 'active bg-primary' : ''; ?>" href="reports.php">
                        <i class="fas fa-chart-bar me-2"></i>Laporan
                    </a>
                </li>

            <?php elseif ($_SESSION['user_role'] == 'kasir'): ?>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'index.php' ? 'active bg-primary' : ''; ?>" href="index.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'orders.php' ? 'active bg-primary' : ''; ?>" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'payments.php' ? 'active bg-primary' : ''; ?>" href="payments.php">
                        <i class="fas fa-credit-card me-2"></i>Kelola Pembayaran
                    </a>
                </li>

            <?php elseif ($_SESSION['user_role'] == 'customer'): ?>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'index.php' ? 'active bg-primary' : ''; ?>" href="index.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'order.php' ? 'active bg-primary' : ''; ?>" href="order.php">
                        <i class="fas fa-plus-circle me-2"></i>Pesan Layanan
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'payment.php' ? 'active bg-primary' : ''; ?>" href="payment.php">
                        <i class="fas fa-credit-card me-2"></i>Pembayaran
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-white <?php echo $current_page == 'history.php' ? 'active bg-primary' : ''; ?>" href="history.php">
                        <i class="fas fa-history me-2"></i>Riwayat Pesanan
                    </a>
                </li>

            <?php endif; ?>

            <!-- Common Navigation for All Roles -->
            <li class="nav-item">
                <a class="nav-link text-white" href="../index.php">
                    <i class="fas fa-home me-2"></i>Beranda Utama
                </a>
            </li>

            <li class="nav-item mt-3">
                <a class="nav-link text-white" href="profile.php">
                    <i class="fas fa-user me-2"></i>Profil Saya
                </a>
            </li>

            <li class="nav-item">
                <button class="nav-link text-warning border-0 bg-transparent w-100 text-start"
                        data-bs-toggle="modal"
                        data-bs-target="#sidebarLogoutModal">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </li>
        </ul>

        <!-- Quick Stats (Admin & Kasir) -->
        <?php if (in_array($_SESSION['user_role'], ['admin', 'kasir'])): ?>
        <div class="mt-4 p-3 bg-dark border-top">
            <h6 class="text-muted mb-3">Statistik Cepat</h6>
            <?php
            if ($_SESSION['user_role'] == 'admin') {
                $pending_orders = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch()['count'];
                $today_orders = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()")->fetch()['count'];
            } else {
                $pending_orders = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'confirmed')")->fetch()['count'];
                $today_orders = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = CURDATE()")->fetch()['count'];
            }
            ?>
            <div class="small">
                <div class="d-flex justify-content-between mb-2">
                    <span>Pesanan Hari Ini:</span>
                    <span class="badge bg-info"><?php echo $today_orders; ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Butuh Tindakan:</span>
                    <span class="badge bg-warning"><?php echo $pending_orders; ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Sidebar Logout Modal -->
<div class="modal fade" id="sidebarLogoutModal" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Logout</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Apakah Anda yakin ingin logout dari sistem?</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a class="btn btn-warning" href="../logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Wrapper -->
<div class="main-content">
