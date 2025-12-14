<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role admin
checkRole(['admin']);

$page_title = "Kelola Layanan";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Ambil semua layanan
$stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hapus layanan
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $service_id = sanitize($_GET['id']);
    
    // Cek apakah layanan digunakan di pesanan
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE service_id = ?");
    $stmt->execute([$service_id]);
    $order_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($order_count > 0) {
        $_SESSION['message'] = "Tidak dapat menghapus layanan karena sudah digunakan dalam $order_count pesanan.";
        $_SESSION['message_type'] = "error";
    } else {
        // Hapus layanan
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        if ($stmt->execute([$service_id])) {
            $_SESSION['message'] = "Layanan berhasil dihapus!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal menghapus layanan.";
            $_SESSION['message_type'] = "error";
        }
    }
    redirect('services.php');
}

// Tambah layanan baru
if (isset($_POST['add_service'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price_per_page = sanitize($_POST['price_per_page']);
    $color_print = isset($_POST['color_print']) ? 1 : 0;
    $min_pages = sanitize($_POST['min_pages']);
    $max_pages = sanitize($_POST['max_pages']);
    $turnaround_time = sanitize($_POST['turnaround_time']);
    
    $stmt = $pdo->prepare("
        INSERT INTO services (name, description, price_per_page, color_print, min_pages, max_pages, turnaround_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$name, $description, $price_per_page, $color_print, $min_pages, $max_pages, $turnaround_time])) {
        $_SESSION['message'] = "Layanan berhasil ditambahkan!";
        $_SESSION['message_type'] = "success";
        redirect('services.php');
    } else {
        $error = "Gagal menambahkan layanan.";
    }
}

// Update layanan
if (isset($_POST['update_service'])) {
    $service_id = sanitize($_POST['service_id']);
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price_per_page = sanitize($_POST['price_per_page']);
    $color_print = isset($_POST['color_print']) ? 1 : 0;
    $min_pages = sanitize($_POST['min_pages']);
    $max_pages = sanitize($_POST['max_pages']);
    $turnaround_time = sanitize($_POST['turnaround_time']);
    $status = sanitize($_POST['status']);
    
    $stmt = $pdo->prepare("
        UPDATE services SET 
        name = ?, description = ?, price_per_page = ?, color_print = ?, 
        min_pages = ?, max_pages = ?, turnaround_time = ?, status = ? 
        WHERE id = ?
    ");
    
    if ($stmt->execute([$name, $description, $price_per_page, $color_print, $min_pages, $max_pages, $turnaround_time, $status, $service_id])) {
        $_SESSION['message'] = "Layanan berhasil diupdate!";
        $_SESSION['message_type'] = "success";
        redirect('services.php');
    } else {
        $error = "Gagal mengupdate layanan.";
    }
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    <?php echo isset($_GET['edit']) ? 'Edit Layanan' : 'Tambah Layanan'; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php
                $editing_service = null;
                if (isset($_GET['edit'])) {
                    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
                    $stmt->execute([$_GET['edit']]);
                    $editing_service = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                ?>

                <form method="POST" action="">
                    <?php if ($editing_service): ?>
                        <input type="hidden" name="service_id" value="<?php echo $editing_service['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Layanan *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo $editing_service ? $editing_service['name'] : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $editing_service ? $editing_service['description'] : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="price_per_page" class="form-label">Harga per Halaman *</label>
                        <input type="number" class="form-control" id="price_per_page" name="price_per_page" 
                               value="<?php echo $editing_service ? $editing_service['price_per_page'] : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="color_print" name="color_print" 
                                   <?php echo ($editing_service && $editing_service['color_print']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="color_print">
                                Layanan Warna
                            </label>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_pages" class="form-label">Min. Halaman</label>
                                <input type="number" class="form-control" id="min_pages" name="min_pages" 
                                       value="<?php echo $editing_service ? $editing_service['min_pages'] : '1'; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_pages" class="form-label">Max. Halaman</label>
                                <input type="number" class="form-control" id="max_pages" name="max_pages" 
                                       value="<?php echo $editing_service ? $editing_service['max_pages'] : '1000'; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="turnaround_time" class="form-label">Waktu Pengerjaan</label>
                        <input type="text" class="form-control" id="turnaround_time" name="turnaround_time" 
                               value="<?php echo $editing_service ? $editing_service['turnaround_time'] : ''; ?>" 
                               placeholder="Contoh: 1-2 jam">
                    </div>
                    
                    <?php if ($editing_service): ?>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php echo $editing_service['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $editing_service['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid">
                        <?php if ($editing_service): ?>
                            <button type="submit" name="update_service" class="btn btn-primary">Update Layanan</button>
                            <a href="services.php" class="btn btn-outline-secondary mt-2">Batal</a>
                        <?php else: ?>
                            <button type="submit" name="add_service" class="btn btn-primary">Tambah Layanan</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Layanan</h5>
                <span class="badge bg-primary">Total: <?php echo count($services); ?> Layanan</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nama Layanan</th>
                                <th>Harga</th>
                                <th>Warna</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($services) > 0): ?>
                                <?php foreach($services as $service): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $service['name']; ?></strong>
                                        <?php if ($service['description']): ?>
                                            <br><small class="text-muted"><?php echo $service['description']; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>Rp <?php echo number_format($service['price_per_page'], 0, ',', '.'); ?>/hal</td>
                                    <td>
                                        <?php if ($service['color_print']): ?>
                                            <span class="badge bg-info">Warna</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Hitam Putih</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $service['turnaround_time'] ?: '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $service['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($service['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="services.php?edit=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $service['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                        <p>Belum ada layanan</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(serviceId) {
    if (confirm('Apakah Anda yakin ingin menghapus layanan ini?')) {
        window.location.href = 'services.php?action=delete&id=' + serviceId;
    }
}
</script>

<?php include '../includes/footer.php'; ?>