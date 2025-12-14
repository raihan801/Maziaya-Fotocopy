<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role admin
checkRole(['admin']);

$page_title = "Dashboard Admin";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Hitung statistik
$stmt = $pdo->query("SELECT COUNT(*) as total_orders FROM orders");
$total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

$stmt = $pdo->query("SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'");
$total_customers = $stmt->fetch(PDO::FETCH_ASSOC)['total_customers'];

$stmt = $pdo->query("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

$stmt = $pdo->query("SELECT COUNT(*) as total_services FROM services WHERE status = 'active'");
$total_services = $stmt->fetch(PDO::FETCH_ASSOC)['total_services'];

$stmt = $pdo->query("SELECT SUM(total_price) as total_revenue FROM orders WHERE payment_status = 'paid'");
$total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

// Ambil pesanan terbaru
$stmt = $pdo->query("
    SELECT o.*, u.full_name as customer_name, s.name as service_name 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    JOIN services s ON o.service_id = s.id 
    ORDER BY o.order_date DESC 
    LIMIT 5
");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Admin</h1>
    <a href="reports.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
    </a>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Total Pesanan Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Pesanan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Customer Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Customer</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_customers; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Orders Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pesanan Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Revenue Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Pendapatan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></div>
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
    <div class="col-xl-2 col-md-4 mb-4">
        <a href="orders.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-primary shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-shopping-cart fa-2x text-primary mb-3"></i>
                    <h6 class="font-weight-bold text-primary">Kelola Pesanan</h6>
                    <small class="text-muted"><?php echo $total_orders; ?> pesanan</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <a href="customers.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-success shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x text-success mb-3"></i>
                    <h6 class="font-weight-bold text-success">Kelola Customer</h6>
                    <small class="text-muted"><?php echo $total_customers; ?> customer</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <a href="services.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-info shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-cog fa-2x text-info mb-3"></i>
                    <h6 class="font-weight-bold text-info">Kelola Layanan</h6>
                    <small class="text-muted"><?php echo $total_services; ?> layanan</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <a href="reports.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-warning shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-2x text-warning mb-3"></i>
                    <h6 class="font-weight-bold text-warning">Lihat Laporan</h6>
                    <small class="text-muted">Analisis data</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <a href="orders.php?status=pending" class="text-decoration-none">
            <div class="card quick-nav-card border-left-danger shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-danger mb-3"></i>
                    <h6 class="font-weight-bold text-danger">Pending Orders</h6>
                    <small class="text-muted"><?php echo $pending_orders; ?> menunggu</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-2 col-md-4 mb-4">
        <a href="../customer/profile.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-secondary shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user fa-2x text-secondary mb-3"></i>
                    <h6 class="font-weight-bold text-secondary">Profil Saya</h6>
                    <small class="text-muted">Pengaturan akun</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Pesanan Terbaru</h6>
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
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_orders) > 0): ?>
                                <?php foreach($recent_orders as $order): ?>
                                <tr>
                                    <td><?php echo $order['order_number']; ?></td>
                                    <td><?php echo $order['customer_name']; ?></td>
                                    <td><?php echo $order['service_name']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
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
                                    <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada pesanan</td>
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