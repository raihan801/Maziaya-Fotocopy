<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role customer
checkRole(['customer', 'admin', 'kasir']);

$page_title = "Profil Saya";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ======================================
// ============ UPDATE PROFIL ===========
// ======================================
if (isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $email     = sanitize($_POST['email']);
    $phone     = sanitize($_POST['phone']);
    $address   = sanitize($_POST['address']);

    // --- HANDLE UPLOAD FOTO PROFIL ---
    $profile_photo = $user['profile_photo']; // default

    if (!empty($_FILES['profile_photo']['name'])) {

        $allowed = ['jpg','jpeg','png','gif'];
        $file_name = $_FILES['profile_photo']['name'];
        $file_tmp  = $_FILES['profile_photo']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {

            $newName = "profile_" . $user_id . "_" . time() . "." . $ext;
            $uploadPath = "../uploads/profile/" . $newName;

            if (!is_dir("../uploads/profile")) {
                mkdir("../uploads/profile", 0777, true);
            }

            // Hapus foto lama
            if (!empty($user['profile_photo']) && file_exists("../uploads/profile/" . $user['profile_photo'])) {
                unlink("../uploads/profile/" . $user['profile_photo']);
            }

            // Upload baru
            move_uploaded_file($file_tmp, $uploadPath);
            $profile_photo = $newName;
        } else {
            $error = "Format foto tidak valid. Gunakan JPG/PNG/GIF.";
        }
    }

    // --- CEK EMAIL DUPLIKAT ---
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);

    if ($stmt->rowCount() > 0) {
        $error = "Email sudah digunakan oleh user lain.";
    } else {

        $stmt = $pdo->prepare("UPDATE users 
            SET full_name = ?, email = ?, phone = ?, address = ?, profile_photo = ?
            WHERE id = ?");

        if ($stmt->execute([$full_name, $email, $phone, $address, $profile_photo, $user_id])) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['message'] = "Profil berhasil diupdate!";
            $_SESSION['message_type'] = "success";
            redirect('profile.php');
        } else {
            $error = "Gagal mengupdate profil.";
        }
    }
}

// ======================================
// ========= UPDATE PASSWORD =============
// ======================================
if (isset($_POST['update_password'])) {

    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $user['password'])) {
        $password_error = "Password saat ini salah.";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "Password baru dan konfirmasi tidak sama.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stmt->execute([$hashed_password, $user_id])) {
            $_SESSION['message'] = "Password berhasil diubah!";
            $_SESSION['message_type'] = "success";
            redirect('profile.php');
        } else {
            $password_error = "Gagal mengubah password.";
        }
    }
}
?>

<style>
/* — Modern Photo Upload — */
.profile-photo-wrapper {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    margin: 20px auto;
    border: 4px solid #e5e7eb;
    transition: .3s;
}

.profile-photo-wrapper:hover {
    border-color: #3b82f6;
    box-shadow: 0 0 12px rgba(0,0,0,.15);
}

.profile-photo-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.45);
    color: white;
    opacity: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: .25s ease;
    font-size: 32px;
}

.profile-photo-wrapper:hover .upload-overlay {
    opacity: 1;
}

#profile_photo_input {
    display: none;
}
</style>

<div class="row">
    
    <!-- =================== PROFIL =================== -->
    <div class="col-md-6">
        <div class="card">

            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Profil</h5>
            </div>

            <div class="card-body">

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">

                    <!-- Foto Profil -->
                    <div class="text-center">
                        <div class="profile-photo-wrapper" onclick="document.getElementById('profile_photo_input').click();">
                            <img id="preview_photo"
                                 src="../uploads/profile/<?php echo $user['profile_photo'] ?: 'default.png'; ?>">
                            <div class="upload-overlay">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>

                        <input type="file" id="profile_photo_input" name="profile_photo" accept="image/*">
                        <p class="text-muted mb-3">Klik foto untuk mengganti</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username (tidak bisa diubah)</label>
                        <input type="text" class="form-control" value="<?php echo $user['username']; ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" name="full_name"
                               value="<?php echo $user['full_name']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email"
                               value="<?php echo $user['email']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" name="phone"
                               value="<?php echo $user['phone']; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary w-100">Update Profil</button>
                </form>
            </div>
        </div>
    </div>

    <!-- =================== PASSWORD =================== -->
    <div class="col-md-6">

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Ubah Password</h5>
            </div>

            <div class="card-body">

                <?php if (isset($password_error)): ?>
                    <div class="alert alert-danger"><?php echo $password_error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Password Saat Ini *</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru *</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password *</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>

                    <button type="submit" name="update_password" class="btn btn-primary w-100">Ubah Password</button>
                </form>

            </div>
        </div>

        <!-- Statistik -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Saya</h5>
            </div>
            <div class="card-body">

                <?php
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE customer_id = ?");
                $stmt->execute([$user_id]);
                $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

                $stmt = $pdo->prepare("SELECT COUNT(*) as completed_orders FROM orders WHERE customer_id = ? AND status = 'completed'");
                $stmt->execute([$user_id]);
                $completed_orders = $stmt->fetch(PDO::FETCH_ASSOC)['completed_orders'];

                $stmt = $pdo->prepare("SELECT SUM(total_price) as total_spent FROM orders WHERE customer_id = ? AND payment_status = 'paid'");
                $stmt->execute([$user_id]);
                $total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'] ?? 0;
                ?>

                <div class="list-group">
                    <div class="list-group-item d-flex justify-content-between">
                        Total Pesanan <span class="badge bg-primary"><?php echo $total_orders; ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        Pesanan Selesai <span class="badge bg-success"><?php echo $completed_orders; ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        Total Pengeluaran <span class="badge bg-info">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        Member Sejak <span class="text-muted"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
// Preview foto saat pilih file
document.getElementById("profile_photo_input").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => document.getElementById("preview_photo").src = ev.target.result;
    reader.readAsDataURL(file);
});
</script>

<?php include '../includes/footer.php'; ?>
