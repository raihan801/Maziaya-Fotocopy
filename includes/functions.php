<?php
// Cek apakah fungsi sudah dideklarasikan
if (!function_exists('redirect')) {

// Fungsi untuk redirect
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
}

// Fungsi untuk menampilkan pesan
function displayMessage($message, $type = 'info') {
    $class = '';
    switch($type) {
        case 'success':
            $class = 'alert-success';
            break;
        case 'error':
            $class = 'alert-danger';
            break;
        case 'warning':
            $class = 'alert-warning';
            break;
        default:
            $class = 'alert-info';
    }
    
    return "<div class='alert $class'>$message</div>";
}

// Fungsi untuk generate order number
function generateOrderNumber() {
    return 'MZ' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Fungsi untuk menghitung harga
function calculatePrice($service_id, $page_count, $color_print = false, $binding_type = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT price_per_page FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$service) return 0;
    
    $base_price = $service['price_per_page'] * $page_count;
    
    // Tambahan untuk print warna
    if ($color_print) {
        $base_price += 500 * $page_count; // Tambahan untuk warna
    }
    
    // Tambahan untuk jilid
    if ($binding_type) {
        switch($binding_type) {
            case 'soft_cover':
                $base_price += 5000;
                break;
            case 'hard_cover':
                $base_price += 10000;
                break;
            case 'spiral':
                $base_price += 7000;
                break;
        }
    }
    
    return $base_price;
}

// Fungsi untuk upload file
function uploadFile($file, $target_dir = "uploads/") {
    // Buat folder uploads jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . time() . '_' . basename($file["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check file size (max 10MB)
    if ($file["size"] > 10000000) {
        return ['success' => false, 'message' => 'Maaf, file terlalu besar. Maksimal 10MB.'];
    }
    
    // Allow certain file formats
    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileType, $allowed_types)) {
        return ['success' => false, 'message' => 'Maaf, hanya file PDF, DOC, DOCX, JPG, JPEG, PNG yang diizinkan.'];
    }
    
    // Try to upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'file_path' => $target_file, 'original_name' => $file["name"]];
    } else {
        return ['success' => false, 'message' => 'Maaf, terjadi kesalahan saat mengupload file.'];
    }
}

// Fungsi untuk mendapatkan nama user berdasarkan ID
function getUserName($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user ? $user['full_name'] : 'Unknown User';
}

// Fungsi untuk mendapatkan nama service berdasarkan ID
function getServiceName($service_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT name FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $service ? $service['name'] : 'Unknown Service';
}

} // End if !function_exists
?>