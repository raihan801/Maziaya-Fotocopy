<?php 
include '../includes/config.php';
include '../includes/auth.php';
checkRole(['customer']);

$page_title = "Pembayaran Berhasil Dikirim";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

if (!isset($_GET['order_id'])) {
    die("<h3>Order ID tidak ditemukan.</h3>");
}

$order_id = intval($_GET['order_id']);

// Ambil data pesanan
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name 
    FROM orders o 
    JOIN users u ON u.id = o.customer_id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("<h3>Pesanan tidak ditemukan.</h3>");
}
?>

<div class="container-fluid">

    <div class="text-center mt-5 mb-4">
        <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
        <h2 class="mt-3">Bukti Pembayaran Berhasil Dikirim!</h2>
        <p class="text-muted">Pesanan Anda akan diverifikasi oleh kasir.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Detail Pesanan</h5>
                </div>
                <div class="card-body">

                    <p><strong>No. Pesanan:</strong> <?php echo $order['order_number']; ?></p>
                    <p><strong>Nama Customer:</strong> <?php echo $order['full_name']; ?></p>
                    <p><strong>Total Pembayaran:</strong> 
                        Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?>
                    </p>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-clock me-2"></i>
                        Status: <strong>Menunggu Verifikasi Kasir</strong>
                    </div>

                    <a href="history.php" class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-arrow-left me-1"></i>
                        Riwayat Pesanan
                    </a>

                </div>
            </div>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
