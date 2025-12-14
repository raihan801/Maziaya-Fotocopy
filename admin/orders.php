<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role admin
checkRole(['admin']);

$page_title = "Kelola Pesanan";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Ambil semua pesanan
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
    $query .= " AND (o.order_number LIKE ? OR u.full_name LIKE ? OR s.name LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
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

// Hapus pesanan
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $order_id = sanitize($_GET['id']);
    
    try {
        // Mulai transaction
        $pdo->beginTransaction();
        
        // 1. Hapus data pembayaran terkait
        $stmt = $pdo->prepare("DELETE FROM payments WHERE order_id = ?");
        $stmt->execute([$order_id]);
        
        // 2. Hapus file yang diupload (jika ada)
        $stmt = $pdo->prepare("SELECT file_path FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order && $order['file_path'] && file_exists($order['file_path'])) {
            unlink($order['file_path']);
        }
        
        // 3. Hapus pesanan
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        
        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            $_SESSION['message'] = "Pesanan berhasil dihapus!";
            $_SESSION['message_type'] = "success";
        } else {
            $pdo->rollBack();
            $_SESSION['message'] = "Pesanan tidak ditemukan.";
            $_SESSION['message_type'] = "danger";
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    redirect('orders.php');
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan</h5>
        <div>
            <a href="reports.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-bar me-1"></i>Laporan
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filter dan Pencarian -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan no. pesanan, customer, atau layanan..." value="<?php echo $search; ?>">
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
                            <td>
                                <div><?php echo $order['customer_name']; ?></div>
                                <small class="text-muted"><?php echo $order['customer_phone']; ?></small>
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
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>Hapus
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
                                            <p><strong>Status Pembayaran:</strong> 
                                                <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </p>
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
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
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

<script>
function confirmDelete(orderId) {
    if (confirm('Apakah Anda yakin ingin menghapus pesanan ini?')) {
        window.location.href = 'orders.php?action=delete&id=' + orderId;
    }
}

// Handle delete action from URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('action') === 'delete' && urlParams.get('id')) {
    confirmDelete(urlParams.get('id'));
}
</script>

<?php include '../includes/footer.php'; ?>