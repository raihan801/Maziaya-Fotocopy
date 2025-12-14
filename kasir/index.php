<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role kasir
checkRole(['kasir']);

$page_title = "Dashboard Kasir";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Hitung statistik untuk kasir
$stmt = $pdo->query("SELECT COUNT(*) as today_orders FROM orders WHERE DATE(order_date) = CURDATE()");
$today_orders = $stmt->fetch(PDO::FETCH_ASSOC)['today_orders'];

$stmt = $pdo->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status IN ('pending', 'confirmed')");
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

$stmt = $pdo->query("SELECT COUNT(*) as ready_orders FROM orders WHERE status = 'completed' AND payment_status = 'paid'");
$ready_orders = $stmt->fetch(PDO::FETCH_ASSOC)['ready_orders'];

$stmt = $pdo->query("SELECT SUM(total_price) as today_revenue FROM orders WHERE DATE(order_date) = CURDATE() AND payment_status = 'paid'");
$today_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['today_revenue'] ?? 0;

// Ambil pesanan hari ini
$stmt = $pdo->query("
    SELECT o.*, u.full_name as customer_name, s.name as service_name 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    JOIN services s ON o.service_id = s.id 
    WHERE DATE(o.order_date) = CURDATE() 
    ORDER BY o.order_date DESC 
    LIMIT 5
");
$today_orders_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Kasir</h1>
    <a href="orders.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-shopping-cart fa-sm text-white-50"></i> Kelola Semua Pesanan
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Pesanan Hari Ini Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Pesanan Hari Ini</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menunggu Proses Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Menunggu Proses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Siap Diambil Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Siap Diambil</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $ready_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pendapatan Hari Ini Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Pendapatan Hari Ini</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?php echo number_format($today_revenue, 0, ',', '.'); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Navigation Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="orders.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-primary shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-cart fa-2x text-primary mb-3"></i>
                    <h6 class="font-weight-bold text-primary">Kelola Pesanan</h6>
                    <small class="text-muted"><?php echo $today_orders; ?> pesanan hari ini</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="payments.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-success shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-credit-card fa-2x text-success mb-3"></i>
                    <h6 class="font-weight-bold text-success">Kelola Pembayaran</h6>
                    <small class="text-muted">Proses pembayaran</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="orders.php?status=pending" class="text-decoration-none">
            <div class="card quick-nav-card border-left-warning shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-3"></i>
                    <h6 class="font-weight-bold text-warning">Pesanan Pending</h6>
                    <small class="text-muted"><?php echo $pending_orders; ?> menunggu</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="../customer/profile.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-info shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user fa-2x text-info mb-3"></i>
                    <h6 class="font-weight-bold text-info">Profil Saya</h6>
                    <small class="text-muted">Pengaturan akun</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Pesanan Hari Ini -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Pesanan Hari Ini</h6>
                <a href="orders.php" class="btn btn-sm btn-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
                                <th>Customer</th>
                                <th>Layanan</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($today_orders_list) > 0): ?>
                                <?php foreach($today_orders_list as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_number']; ?></td>
                                    <td><?php echo $order['customer_name']; ?></td>
                                    <td><?php echo $order['service_name']; ?></td>
                                    <td><?php echo date('H:i', strtotime($order['order_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch($order['status']) {
                                                case 'pending': echo 'warning'; break;
                                                case 'confirmed': echo 'info'; break;
                                                case 'in_progress': echo 'primary'; break;
                                                case 'completed': echo 'success'; break;
                                                case 'cancelled': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['payment_status'] == 'paid' ? 'success' : 
                                                 ($order['payment_status'] == 'pending' ? 'warning' : 'danger');
                                        ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Kelola</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada pesanan hari ini</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>