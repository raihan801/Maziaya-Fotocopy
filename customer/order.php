<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role customer
checkRole(['customer']);

$page_title = "Pesan Layanan";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Ambil semua layanan aktif
$stmt = $pdo->query("SELECT * FROM services WHERE status = 'active'");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses form pesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = sanitize($_POST['service_id']);
    $page_count = sanitize($_POST['page_count']);
    $color_print = isset($_POST['color_print']) ? 1 : 0;
    $binding_type = sanitize($_POST['binding_type']);
    $special_instructions = sanitize($_POST['special_instructions']);
    
    // Upload file
    $file_upload = uploadFile($_FILES['document_file']);
    
    if ($file_upload['success']) {
        // Hitung total harga
        $total_price = calculatePrice($service_id, $page_count, $color_print, $binding_type);
        
        // Generate order number
        $order_number = generateOrderNumber();
        
        // Simpan pesanan
        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, customer_id, service_id, file_path, original_filename, page_count, color_print, binding_type, special_instructions, total_price) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
if ($stmt->execute([
    $order_number, 
    $_SESSION['user_id'], 
    $service_id, 
    $file_upload['file_path'], 
    $file_upload['original_name'], 
    $page_count, 
    $color_print, 
    $binding_type, 
    $special_instructions, 
    $total_price
])) {
    $new_order_id = $pdo->lastInsertId(); // <-- ambil ID order terbaru
    
    $_SESSION['message'] = "Pesanan berhasil dibuat! No. Pesanan: $order_number";
    $_SESSION['message_type'] = "success";
    
    redirect('payment.php?order_id=' . $new_order_id); // <-- kirim ke halaman payment
} else {
            $error = "Terjadi kesalahan saat membuat pesanan.";
        }
    } else {
        $error = $file_upload['message'];
    }
}

// Jika ada parameter service di URL
$selected_service = isset($_GET['service']) ? $_GET['service'] : '';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Pesan Layanan Baru</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="service_id" class="form-label">Pilih Layanan *</label>
                        <select class="form-select" id="service_id" name="service_id" required onchange="updatePriceEstimate()">
                            <option value=""> Pilih Layanan </option>
                            <?php foreach($services as $service): ?>
                                <option value="<?php echo $service['id']; ?>" 
                                    <?php echo ($selected_service == $service['id']) ? 'selected' : ''; ?>
                                    data-price="<?php echo $service['price_per_page']; ?>"
                                    data-color="<?php echo $service['color_print']; ?>">
                                    <?php echo $service['name']; ?> - Rp <?php echo number_format($service['price_per_page'], 0, ',', '.'); ?>/halaman
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="document_file" class="form-label">Upload File *</label>
                        <input type="file" class="form-control" id="document_file" name="document_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <div class="form-text">Format yang didukung: PDF, DOC, DOCX, JPG, JPEG, PNG (Maks. 10MB)</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="page_count" class="form-label">Jumlah Halaman *</label>
                                <input type="number" class="form-control" id="page_count" name="page_count" min="1" value="1" required onchange="updatePriceEstimate()">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Opsi Warna</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="color_print" name="color_print" onchange="updatePriceEstimate()">
                                    <label class="form-check-label" for="color_print">
                                        Cetak Berwarna (+ Rp 500/halaman)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="binding_type" class="form-label">Jenis Penjilidan (Opsional)</label>
                        <select class="form-select" id="binding_type" name="binding_type" onchange="updatePriceEstimate()">
                            <option value=""> Pilih Jenis Jilid </option>
                            <option value="soft_cover" data-price="5000">Soft Cover (+ Rp 5.000)</option>
                            <option value="hard_cover" data-price="10000">Hard Cover (+ Rp 10.000)</option>
                            <option value="spiral" data-price="7000">Spiral (+ Rp 7.000)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="special_instructions" class="form-label">Instruksi Khusus (Opsional)</label>
                        <textarea class="form-control" id="special_instructions" name="special_instructions" rows="3" placeholder="Contoh: Print bolak-balik, ukuran kertas A4, dll."></textarea>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6>Estimasi Harga</h6>
                            <div id="price_estimate" class="fs-4 fw-bold text-primary">Rp 0</div>
                            <small class="text-muted">* Harga dapat berubah setelah verifikasi file</small>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Buat Pesanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informasi Pesanan</h5>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <h6><i class="fas fa-info-circle me-2 text-primary"></i>Proses Pesanan</h6>
                    <ol class="small">
                        <li>Pilih layanan yang diinginkan</li>
                        <li>Upload file dokumen</li>
                        <li>Tentukan jumlah halaman dan opsi</li>
                        <li>Lakukan pembayaran</li>
                        <li>Tunggu konfirmasi dan ambil pesanan</li>
                    </ol>
                </div>
                
                <div class="info-item mt-3">
                    <h6><i class="fas fa-clock me-2 text-primary"></i>Waktu Pengerjaan</h6>
                    <ul class="small">
                        <li>Fotokopi: 1-2 jam</li>
                        <li>Print: 1-2 jam</li>
                        <li>Jilid: 2-3 jam</li>
                        <li>Laminasi: 1 jam</li>
                    </ul>
                </div>
                
                <div class="info-item mt-3">
                    <h6><i class="fas fa-file me-2 text-primary"></i>Format File</h6>
                    <ul class="small">
                        <li>PDF (Recommended)</li>
                        <li>DOC/DOCX</li>
                        <li>JPG/JPEG/PNG</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePriceEstimate() {
    const serviceSelect = document.getElementById('service_id');
    const pageCount = parseInt(document.getElementById('page_count').value) || 0;
    const colorCheck = document.getElementById('color_print');
    const bindingSelect = document.getElementById('binding_type');
    
    let total = 0;
    
    // Harga dasar dari layanan
    if (serviceSelect.value) {
        const servicePrice = parseFloat(serviceSelect.options[serviceSelect.selectedIndex].getAttribute('data-price'));
        total += servicePrice * pageCount;
        
        // Tambahan untuk warna
        if (colorCheck.checked) {
            total += 500 * pageCount;
        }
    }
    
    // Tambahan untuk jilid
    if (bindingSelect.value) {
        const bindingPrice = parseFloat(bindingSelect.options[bindingSelect.selectedIndex].getAttribute('data-price'));
        total += bindingPrice;
    }
    
    document.getElementById('price_estimate').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// Panggil fungsi pertama kali
updatePriceEstimate();
</script>

<?php include '../includes/footer.php'; ?>