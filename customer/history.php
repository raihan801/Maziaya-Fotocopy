<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role customer
checkRole(['customer']);

$page_title = "Riwayat Pesanan";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

$user_id = $_SESSION['user_id'];

// Ambil semua pesanan customer
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$query = "
    SELECT o.*, s.name as service_name 
    FROM orders o 
    JOIN services s ON o.service_id = s.id 
    WHERE o.customer_id = ?
";

$params = [$user_id];

if (!empty($search)) {
    $query .= " AND (o.order_number LIKE ? OR s.name LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term]);
}

if (!empty($status_filter)) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

$query .= " ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Pesanan</h5>
        <a href="order.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Pesan Baru
        </a>
    </div>
    <div class="card-body">
        <!-- Filter dan Pencarian -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan no. pesanan atau layanan..." value="<?php echo $search; ?>">
                        <select class="form-select" name="status" style="max-width: 200px;">
                            <option value="">Semua Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1"></i>Cari
                        </button>
                        <?php if (!empty($search) || !empty($status_filter)): ?>
                            <a href="history.php" class="btn btn-outline-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Layanan</th>
                        <th>Tanggal</th>
                        <th>Jml Halaman</th>
                        <th>Status</th>
                        <th>Pembayaran</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>
                                <strong><?php echo $order['order_number']; ?></strong>
                                <?php if ($order['color_print']): ?>
                                    <span class="badge bg-info ms-1">Warna</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $order['service_name']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo $order['page_count']; ?> hal</td>
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
                            <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                    Detail
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Detail Pesanan -->
                        <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Pesanan - <?php echo $order['order_number']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Informasi Pesanan</h6>
                                                <p><strong>No. Pesanan:</strong> <?php echo $order['order_number']; ?></p>
                                                <p><strong>Layanan:</strong> <?php echo $order['service_name']; ?></p>
                                                <p><strong>Tanggal Pesan:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                                                <?php if ($order['estimated_completion']): ?>
                                                    <p><strong>Estimasi Selesai:</strong> <?php echo date('d/m/Y H:i', strtotime($order['estimated_completion'])); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Detail Cetakan</h6>
                                                <p><strong>Jumlah Halaman:</strong> <?php echo $order['page_count']; ?></p>
                                                <p><strong>Warna:</strong> <?php echo $order['color_print'] ? 'Ya' : 'Tidak'; ?></p>
                                                <?php if ($order['binding_type']): ?>
                                                    <p><strong>Jilid:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['binding_type'])); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if ($order['special_instructions']): ?>
                                            <div class="mt-3">
                                                <h6>Instruksi Khusus</h6>
                                                <p><?php echo $order['special_instructions']; ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mt-3">
                                            <h6>File</h6>
                                            <?php if ($order['file_path']): ?>
                                                <a href="../<?php echo $order['file_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-download me-1"></i>Download File
                                                </a>
                                                <small class="text-muted ms-2"><?php echo $order['original_filename']; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Tidak ada file</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-3">
                                            <h6>Status</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Status Pesanan:</strong> 
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
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Status Pembayaran:</strong> 
                                                        <span class="badge bg-<?php 
                                                            echo $order['payment_status'] == 'paid' ? 'success' : 
                                                                 ($order['payment_status'] == 'pending' ? 'warning' : 'danger');
                                                        ?>">
                                                            <?php echo ucfirst($order['payment_status']); ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <h6>Informasi Harga</h6>
                                            <p><strong>Total Harga:</strong> Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></p>
                                            <?php if ($order['payment_method']): ?>
                                                <p><strong>Metode Pembayaran:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($order['completed_at']): ?>
                                            <div class="mt-3">
                                                <div class="alert alert-success">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    Pesanan selesai pada: <?php echo date('d/m/Y H:i', strtotime($order['completed_at'])); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <?php if ($order['status'] == 'completed' && $order['payment_status'] == 'paid'): ?>
                                            <a href="../<?php echo $order['file_path']; ?>" class="btn btn-primary" download>
                                                <i class="fas fa-download me-1"></i>Download Hasil
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p>Belum ada pesanan</p>
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <a href="history.php" class="btn btn-primary">Lihat Semua Pesanan</a>
                                <?php else: ?>
                                    <a href="order.php" class="btn btn-primary">Pesan Layanan Pertama</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>