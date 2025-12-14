<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role customer
checkRole(['customer']);

$page_title = "Dashboard Customer";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Ambil data pesanan terbaru
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT o.*, s.name as service_name 
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    WHERE o.customer_id = ? 
    ORDER BY o.order_date DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE customer_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE customer_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

$stmt = $pdo->prepare("SELECT COUNT(*) as completed_orders FROM orders WHERE customer_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$completed_orders = $stmt->fetch(PDO::FETCH_ASSOC)['completed_orders'];

$stmt = $pdo->prepare("SELECT SUM(total_price) as total_spent FROM orders WHERE customer_id = ? AND payment_status = 'paid'");
$stmt->execute([$user_id]);
$total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'] ?? 0;
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Saya</h1>
    <a href="order.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Pesan Baru
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

    <!-- Pesanan Pending Card -->
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

    <!-- Pesanan Selesai Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Pesanan Selesai</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $completed_orders; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pengeluaran Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Pengeluaran</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></div>
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
    <div class="col-xl-4 col-md-6 mb-4">
        <a href="order.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-primary shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-plus-circle fa-2x text-primary mb-3"></i>
                    <h6 class="font-weight-bold text-primary">Pesan Layanan Baru</h6>
                    <small class="text-muted">Buat pesanan baru</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <a href="history.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-success shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-history fa-2x text-success mb-3"></i>
                    <h6 class="font-weight-bold text-success">Riwayat Pesanan</h6>
                    <small class="text-muted"><?php echo $total_orders; ?> total pesanan</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <a href="../customer/profile.php" class="text-decoration-none">
            <div class="card quick-nav-card border-left-info shadow h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user fa-2x text-info mb-3"></i>
                    <h6 class="font-weight-bold text-info">Profil Saya</h6>
                    <small class="text-muted">Kelola profil</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Pesanan Terbaru -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Pesanan Terbaru</h6>
                <a href="history.php" class="btn btn-sm btn-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No. Pesanan</th>
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
                                        <a href="history.php?view=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada pesanan</td>
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