<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role admin
checkRole(['admin']);

$page_title = "Kelola Customer";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Ambil semua customer
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT * FROM users WHERE role = 'customer'";
$params = [];

if (!empty($search)) {
    $query .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

$query .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total pesanan per customer
foreach($customers as $key => $customer) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE customer_id = ?");
    $stmt->execute([$customer['id']]);
    $customers[$key]['order_count'] = $stmt->fetch(PDO::FETCH_ASSOC)['order_count'];
}


// Hapus customer
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $customer_id = sanitize($_GET['id']);
    
    // Cek apakah customer memiliki pesanan
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $order_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($order_count > 0) {
        $_SESSION['message'] = "Tidak dapat menghapus customer karena memiliki $order_count pesanan. Hapus pesanan terlebih dahulu.";
        $_SESSION['message_type'] = "danger";
    } else {
        try {
            // Mulai transaction
            $pdo->beginTransaction();
            
            // Hapus customer
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'customer'");
            $stmt->execute([$customer_id]);
            
            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                $_SESSION['message'] = "Customer berhasil dihapus!";
                $_SESSION['message_type'] = "success";
            } else {
                $pdo->rollBack();
                $_SESSION['message'] = "Customer tidak ditemukan atau gagal dihapus.";
                $_SESSION['message_type'] = "danger";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }
    redirect('customers.php');
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Kelola Customer</h5>
        <div>
            <span class="badge bg-primary">Total: <?php echo count($customers); ?> Customer</span>
        </div>
    </div>
    <div class="card-body">
        <!-- Pencarian -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari customer..." value="<?php echo $search; ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-1"></i>Cari
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="customers.php" class="btn btn-outline-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Total Pesanan</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($customers) > 0): ?>
                        <?php foreach($customers as $index => $customer): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $customer['username']; ?></td>
                            <td><?php echo $customer['full_name']; ?></td>
                            <td><?php echo $customer['email']; ?></td>
                            <td><?php echo $customer['phone'] ?: '-'; ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo $customer['order_count']; ?> pesanan</span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        Aksi
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#customerModal<?php echo $customer['id']; ?>">
                                                <i class="fas fa-eye me-2"></i>Detail
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="orders.php?search=<?php echo urlencode($customer['username']); ?>">
                                                <i class="fas fa-shopping-cart me-2"></i>Lihat Pesanan
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?php echo $customer['id']; ?>)">
                                                <i class="fas fa-trash me-2"></i>Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Detail Customer -->
                        <div class="modal fade" id="customerModal<?php echo $customer['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Customer - <?php echo $customer['full_name']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <strong>Username:</strong> <?php echo $customer['username']; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Email:</strong> <?php echo $customer['email']; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Nama Lengkap:</strong> <?php echo $customer['full_name']; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Telepon:</strong> <?php echo $customer['phone'] ?: '-'; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Alamat:</strong> 
                                            <?php echo $customer['address'] ? nl2br($customer['address']) : '-'; ?>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Total Pesanan:</strong> 
                                            <span class="badge bg-primary"><?php echo $customer['order_count']; ?> pesanan</span>
                                        </div>
                                        <div class="mb-3">
                                            <strong>Tanggal Daftar:</strong> 
                                            <?php echo date('d/m/Y H:i', strtotime($customer['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p>Tidak ada customer ditemukan</p>
                                <?php if (!empty($search)): ?>
                                    <a href="customers.php" class="btn btn-primary">Lihat Semua Customer</a>
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
function confirmDelete(customerId) {
    if (confirm('Apakah Anda yakin ingin menghapus customer ini? Semua pesanan yang terkait juga akan dihapus.')) {
        window.location.href = 'customers.php?action=delete&id=' + customerId;
    }
}
</script>

<?php include '../includes/footer.php'; ?>