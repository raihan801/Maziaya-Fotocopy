<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role kasir
checkRole(['kasir']);

$page_title = "Kelola Pembayaran";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';


// ==============================
// 1. PERBAIKAN QUERY PEMBAYARAN
// ==============================

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$query = "
    SELECT 
        p.*, 
        o.order_number,
        u.full_name AS customer_name,
        u.phone AS customer_phone
    FROM payments p
    JOIN orders o ON p.order_id = o.id
    JOIN users u ON o.customer_id = u.id
    WHERE 1=1
";


$params = [];

if (!empty($search)) {
    $query .= " AND (o.order_number LIKE ? OR u.full_name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status_filter)) {
    $query .= " AND p.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY p.payment_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==============================
// 2. HITUNG TOTAL PEMBAYARAN
// ==============================

$stmt = $pdo->prepare("
    SELECT 
        SUM(amount) as total_received,
        COUNT(*) as total_payments
    FROM payments 
    WHERE status = 'success'
");
$stmt->execute();
$totals = $stmt->fetch(PDO::FETCH_ASSOC);


// ==============================
// 3. UPDATE STATUS PEMBAYARAN
// (Mengikuti struktur orders.php)
// ==============================

if (isset($_GET['id']) && isset($_GET['status'])) {

    $payment_id = sanitize($_GET['id']);
    $new_status = sanitize($_GET['status']);

    // Ambil data payment
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $pay = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pay) {

        // Update payments table
        $pdo->prepare("
            UPDATE payments 
            SET status = ?
            WHERE id = ?
        ")->execute([$new_status, $payment_id]);

        // Sinkronkan ke tabel orders
        $order_payment_status = ($new_status == 'success') ? 'paid' : $new_status;

        $pdo->prepare("
            UPDATE orders 
            SET payment_status = ?
            WHERE id = ?
        ")->execute([$order_payment_status, $pay['order_id']]);

        $_SESSION['message'] = "Status pembayaran berhasil diperbarui!";
        $_SESSION['message_type'] = "success";

        redirect('payments.php');
        exit();
    }
}

?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-credit-card me-2"></i>Kelola Pembayaran
        </h5>
        <div>
            <span class="badge bg-success me-2">
                Total: Rp <?php echo number_format($totals['total_received'] ?? 0, 0, ',', '.'); ?>
            </span>
            <span class="badge bg-primary">
                <?php echo $totals['total_payments'] ?? 0; ?> Transaksi
            </span>
        </div>
    </div>

    <div class="card-body">

        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search"
                               placeholder="Cari berdasarkan no. pesanan atau customer..."
                               value="<?php echo $search; ?>">

                        <select class="form-select" name="status" style="max-width: 150px;">
                            <option value="">Semua Status</option>
                            <option value="success" <?php echo $status_filter == 'success' ? 'selected' : ''; ?>>Success</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                        </select>

                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1"></i>Cari
                        </button>

                        <?php if (!empty($search) || !empty($status_filter)): ?>
                            <a href="payments.php" class="btn btn-outline-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel -->
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>No. Pesanan</th>
                        <th>Customer</th>
                        <th>Tanggal</th>
                        <th>Metode</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($payments) > 0): ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td>#<?php echo str_pad($payment['id'], 6, '0', STR_PAD_LEFT); ?></td>

                            <td>
                                <a href="orders.php?search=<?php echo urlencode($payment['order_number']); ?>" 
                                   class="text-decoration-none">
                                   <?php echo $payment['order_number']; ?>
                                </a>
                            </td>

                            <td><?php echo $payment['customer_name']; ?></td>

                            <td><?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></td>

                            <td><span class="badge bg-secondary"><?php echo ucfirst($payment['payment_method']); ?></span></td>

                            <td>Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?></td>

                            <td>
                                <span class="badge bg-<?php 
                                    echo $payment['status'] == 'success' ? 'success' :
                                         ($payment['status'] == 'pending' ? 'warning' : 'danger');
                                ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>

                            <!-- Bukti Pembayaran -->
                            <td>
                                <?php if (!empty($payment['proof_image'])): ?>
                                    <a href="../<?php echo $payment['proof_image']; ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Tombol Approve / Reject -->
                            <td>
                                <?php if ($payment['status'] == 'pending'): ?>

                                    <!-- Perbaikan: update langsung ke payments.php -->
                                    <a href="payments.php?id=<?php echo $payment['id']; ?>&status=success"
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i>
                                    </a>

                                    <a href="payments.php?id=<?php echo $payment['id']; ?>&status=failed"
                                       class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i>
                                    </a>

                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Catatan -->
                            <td>
                                <?php if ($payment['notes']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="<?php echo $payment['notes']; ?>">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                            <p>Tidak ada data pembayaran.</p>

                            <?php if (!empty($search) || !empty($status_filter)): ?>
                                <a href="payments.php" class="btn btn-primary">Lihat Semua</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Statistik -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4>Rp <?php echo number_format($totals['total_received'] ?? 0, 0, ',', '.'); ?></h4>
                        <p>Total Penerimaan</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h4><?php echo $totals['total_payments'] ?? 0; ?></h4>
                        <p>Total Transaksi</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4>
                            <?php 
                            $avg = $totals['total_payments'] > 0 
                                ? $totals['total_received'] / $totals['total_payments'] 
                                : 0;
                            echo 'Rp ' . number_format($avg, 0, ',', '.');
                            ?>
                        </h4>
                        <p>Rata-rata / Transaksi</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h4>
                            <?php
                            $pending = $pdo->query("SELECT COUNT(*) as c FROM payments WHERE status='pending'")
                                           ->fetch(PDO::FETCH_ASSOC)['c'];
                            echo $pending;
                            ?>
                        </h4>
                        <p>Pending</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
