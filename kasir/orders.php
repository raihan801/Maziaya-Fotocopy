<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role kasir
checkRole(['kasir']);

$page_title = "Kelola Pesanan";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Ambil pesanan dengan filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$query = "
    SELECT o.*, u.full_name as customer_name, u.phone as customer_phone, s.name as service_name 
    FROM orders o 
    JOIN users u ON o.customer_id = u.id 
    JOIN services s ON o.service_id = s.id 
    WHERE 1=1
";

$params = [];

if (!empty($search)) {
    $query .= " AND (o.order_number LIKE ? OR u.full_name LIKE ?)";
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

// Update status pesanan
if (isset($_POST['update_status'])) {
    $order_id = sanitize($_POST['order_id']);
    $new_status = sanitize($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        $_SESSION['message'] = "Status pesanan berhasil diupdate!";
        $_SESSION['message_type'] = "success";
        redirect('orders.php');
    } else {
        $error = "Gagal mengupdate status pesanan.";
    }
}

// Update status pembayaran
if (isset($_POST['update_payment'])) {
    $order_id = sanitize($_POST['order_id']);
    $payment_status = sanitize($_POST['payment_status']);
    $payment_method = sanitize($_POST['payment_method']);
    
    $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, payment_method = ? WHERE id = ?");
    if ($stmt->execute([$payment_status, $payment_method, $order_id])) {
        
        // Catat pembayaran jika status paid
        if ($payment_status == 'paid') {
    // update payment table (yang sudah ada bukti)
    $pdo->prepare("
        UPDATE payments SET status='success', payment_method=? 
        WHERE order_id=? ORDER BY id DESC LIMIT 1
    ")->execute([$payment_method, $order_id]);
}

        
        $_SESSION['message'] = "Status pembayaran berhasil diupdate!";
        $_SESSION['message_type'] = "success";
        redirect('orders.php');
    } else {
        $error = "Gagal mengupdate status pembayaran.";
    }
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan</h5>
        <div>
            <a href="payments.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-credit-card me-1"></i>Pembayaran
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter dan Pencarian -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan no. pesanan atau customer..." value="<?php echo $search; ?>">
                        <select class="form-select" name="status" style="max-width: 200px;">
                            <option value="">Semua Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1"></i>Cari
                        </button>
                        <a href="orders.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No. Pesanan</th>
                        <th>Customer</th>
                        <th>Layanan</th>
                        <th>Tanggal</th>
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
                            <td>
                                <div><?php echo $order['customer_name']; ?></div>
                                <small class="text-muted"><?php echo $order['customer_phone']; ?></small>
                            </td>
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
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        Aksi
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                                <i class="fas fa-eye me-2"></i>Detail
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $order['id']; ?>">
                                                <i class="fas fa-edit me-2"></i>Update Status
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#paymentModal<?php echo $order['id']; ?>">
                                                <i class="fas fa-credit-card me-2"></i>Update Pembayaran
                                            </a>
                                        </li>
                                    </ul>
                                </div>
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
                                                <h6>Informasi Customer</h6>
                                                <p><strong>Nama:</strong> <?php echo $order['customer_name']; ?></p>
                                                <p><strong>Telepon:</strong> <?php echo $order['customer_phone']; ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Detail Pesanan</h6>
                                                <p><strong>Layanan:</strong> <?php echo $order['service_name']; ?></p>
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
                                            <h6>Informasi Harga</h6>
                                            <p><strong>Total Harga:</strong> Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></p>
                                        </div>
                                        <div class="mt-3">
    <h6>Bukti Pembayaran</h6>
    <?php
        $stmtPay = $pdo->prepare("SELECT proof_image FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1");
        $stmtPay->execute([$order['id']]);
        $pay = $stmtPay->fetch(PDO::FETCH_ASSOC);
    ?>

    <?php if ($pay && $pay['proof_image']): ?>
        <a href="../uploads/payments/<?php echo $pay['proof_image']; ?>" target="_blank">
            <img src="../uploads/payments/<?php echo $pay['proof_image']; ?>" 
                 alt="Bukti" style="max-width:150px; border-radius:5px; cursor:pointer;">
        </a>
    <?php else: ?>
        <p class="text-muted">Tidak ada bukti pembayaran</p>
    <?php endif; ?>
</div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Update Status -->
                        <div class="modal fade" id="statusModal<?php echo $order['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Status Pesanan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Status Saat Ini</label>
                                                <div>
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
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status Baru</label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="in_progress" <?php echo $order['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Update Pembayaran -->
                        <div class="modal fade" id="paymentModal<?php echo $order['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Status Pembayaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Status Saat Ini</label>
                                                <div>
                                                    <span class="badge bg-<?php 
                                                        echo $order['payment_status'] == 'paid' ? 'success' : 
                                                             ($order['payment_status'] == 'pending' ? 'warning' : 'danger');
                                                    ?>">
                                                        <?php echo ucfirst($order['payment_status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="payment_status" class="form-label">Status Pembayaran Baru</label>
                                                <select class="form-select" id="payment_status" name="payment_status" required>
                                                    <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="paid" <?php echo $order['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                                    <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="payment_method" class="form-label">Metode Pembayaran</label>
                                                <select class="form-select" id="payment_method" name="payment_method">
                                                    <option value="">Pilih Metode</option>
                                                    <option value="cash" <?php echo $order['payment_method'] == 'cash' ? 'selected' : ''; ?>>Cash</option>
                                                    <option value="transfer" <?php echo $order['payment_method'] == 'transfer' ? 'selected' : ''; ?>>Transfer Bank</option>
                                                    <option value="qris" <?php echo $order['payment_method'] == 'qris' ? 'selected' : ''; ?>>QRIS</option>
                                                    <option value="edc" <?php echo $order['payment_method'] == 'edc' ? 'selected' : ''; ?>>Kartu Debit/Kredit</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update_payment" class="btn btn-primary">Update Pembayaran</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p>Tidak ada pesanan ditemukan</p>
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <a href="orders.php" class="btn btn-primary">Lihat Semua Pesanan</a>
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