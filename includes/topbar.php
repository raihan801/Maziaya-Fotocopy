<?php
// Top navigation bar untuk dashboard
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
?>

<!-- Top Bar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar-stats mb-4 rounded-4 shadow">
    
<!-- Page Title with Icon (LEFT SIDE) -->
<div class="d-flex align-items-center me-4">
    <div class="page-icon me-3" style="margin-left: 10px;">
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']);
        $icon = 'fa-tachometer-alt'; // default
        switch($current_page) {
            case 'orders.php': $icon = 'fa-shopping-cart'; break;
            case 'customers.php': $icon = 'fa-users'; break;
            case 'services.php': $icon = 'fa-cog'; break;
            case 'reports.php': $icon = 'fa-chart-bar'; break;
            case 'payments.php': $icon = 'fa-credit-card'; break;
            case 'order.php': $icon = 'fa-plus-circle'; break;
            case 'payment.php': $icon = 'fas fa-credit-card'; break;
            case 'payment_success.php': $icon = 'fas fa-credit-card'; break;
            case 'history.php': $icon = 'fa-history'; break;
            case 'profile.php': $icon = 'fa-user'; break;
        }
        ?>
        <i class="fas <?php echo $icon; ?> fa-lg text-primary"></i>
    </div>

    <div>
        <h5 class="mb-0 fw-bold text-gray-800">
            <?php 
            switch($current_page) {
                case 'index.php': echo 'Dashboard'; break;
                case 'orders.php': echo 'Kelola Pesanan'; break;
                case 'customers.php': echo 'Kelola Customer'; break;
                case 'services.php': echo 'Kelola Layanan'; break;
                case 'reports.php': echo 'Laporan'; break;
                case 'payments.php': echo 'Kelola Pembayaran'; break;
                case 'order.php': echo 'Pesan Layanan'; break;
                case 'payment.php': echo 'Pembayaran'; break;
                case 'payment_success.php': echo 'Pembayaran'; break;
                case 'history.php': echo 'Riwayat Pesanan'; break;
                case 'profile.php': echo 'Profil Saya'; break;
                default: echo 'Dashboard';
            }
            ?>
        </h5>
        <small class="text-muted">
            <?php 
            switch($_SESSION['user_role']) {
                case 'admin': echo 'Panel Administrator'; break;
                case 'kasir': echo 'Panel Kasir'; break;
                case 'customer': echo 'Panel Customer'; break;
            }
            ?>
        </small>
    </div>
</div>

<!-- RIGHT SIDE -->
<ul class="navbar-nav ms-auto">

    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fas fa-bars"></i>
    </button>

    <ul class="navbar-nav ml-auto">

        <?php if ($_SESSION['user_role'] == 'admin'): ?>

            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link" href="orders.php?status=pending" title="Pesanan Pending">
                    <i class="fas fa-clock fa-fw text-warning"></i>
                    <span class="badge bg-warning badge-counter">
                        <?php 
                        $pending_count = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch()['count'];
                        echo $pending_count;
                        ?>
                    </span>
                </a>
            </li>

            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link" href="customers.php" title="Total Customer">
                    <i class="fas fa-users fa-fw text-info"></i>
                    <span class="badge bg-info badge-counter">
                        <?php 
                        $customer_count = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch()['count'];
                        echo $customer_count;
                        ?>
                    </span>
                </a>
            </li>

        <?php elseif ($_SESSION['user_role'] == 'kasir'): ?>

            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link" href="orders.php?status=pending" title="Menunggu">
                    <i class="fas fa-clock fa-fw text-warning"></i>
                    <span class="badge bg-warning badge-counter">
                        <?php 
                        $pending_count = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending','confirmed')")->fetch()['count'];
                        echo $pending_count;
                        ?>
                    </span>
                </a>
            </li>

            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link" href="orders.php?status=completed" title="Siap Diambil">
                    <i class="fas fa-check-circle fa-fw text-success"></i>
                    <span class="badge bg-success badge-counter">
                        <?php 
                        $ready_count = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed' AND payment_status = 'paid'")->fetch()['count'];
                        echo $ready_count;
                        ?>
                    </span>
                </a>
            </li>

        <?php elseif ($_SESSION['user_role'] == 'customer'): ?>

            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link" href="history.php?status=pending" title="Pending">
                    <i class="fas fa-clock fa-fw text-warning"></i>
                    <span class="badge bg-warning badge-counter">
                        <?php 
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE customer_id = ? AND status = 'pending'");
                        $stmt->execute([$user_id]);
                        echo $stmt->fetch()['count'];
                        ?>
                    </span>
                </a>
            </li>

            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link" href="history.php?status=completed" title="Selesai">
                    <i class="fas fa-check-circle fa-fw text-success"></i>
                    <span class="badge bg-success badge-counter">
                        <?php 
                        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE customer_id = ? AND status = 'completed'");
                        $stmt->execute([$user_id]);
                        echo $stmt->fetch()['count'];
                        ?>
                    </span>
                </a>
            </li>

        <?php endif; ?>

        <!-- ============================
             USER DROPDOWN WITH PHOTO
        ============================ -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" data-bs-toggle="dropdown">
                
                <span class="me-2 d-none d-lg-inline text-gray-600 small">
                    <?php echo $_SESSION['full_name']; ?>
                </span>

                <!-- FOTO PROFIL -->
                <img src="<?php echo $photo_url; ?>" 
                     class="rounded-circle"
                     style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #e5e7eb;">
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                <a class="dropdown-item" href="../customer/profile.php">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profil
                </a>

                <div class="dropdown-divider"></div>

                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</ul>
</nav>

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Logout</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Apakah Anda yakin ingin logout?</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                <a class="btn btn-primary" href="../logout.php">Logout</a>
            </div>
        </div>
    </div>
</div>
