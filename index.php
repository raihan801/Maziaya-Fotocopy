<?php
include 'includes/config.php';
$page_title = "Beranda";
include 'includes/header.php';

// Ambil layanan aktif
$stmt = $pdo->query("SELECT * FROM services WHERE status = 'active' LIMIT 6");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-content">
            <h1>Percetakan & Fotokopi <span class="highlight">Terpercaya</span> di Medan</h1>
            <p>Dapatkan hasil cetak berkualitas tinggi dengan harga terjangkau. Pesan online, upload file, dan lacak pesanan Anda dengan mudah.</p>
            <div class="hero-buttons">
                <?php if (isLoggedIn() && $_SESSION['user_role'] == 'customer'): ?>
                    <a href="customer/order.php" class="btn-primary">Pesan Sekarang</a>
                <?php elseif (isLoggedIn() && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="admin/index.php" class="btn-primary">Dashboard</a>
                <?php elseif (isLoggedIn() && $_SESSION['user_role'] == 'kasir'): ?>
                    <a href="kasir/index.php" class="btn-primary">Dashboard</a>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="register.php" class="btn-primary">Daftar Sekarang</a>
                    <a href="login.php" class="btn-secondary">Login</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-image">
            <div class="floating-card">
                <i class="fas fa-print"></i>
                <h3>Cetak Cepat</h3>
            </div>
            <div class="floating-card">
                <i class="fas fa-credit-card"></i>
                <h3>All Payment</h3>
            </div>
            <div class="floating-card">
                <i class="fas fa-file-upload"></i>
                <h3>Upload File</h3>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services" id="services">
    <div class="container">
        <div class="section-header">
            <h2>Layanan Kami</h2>
            <p>Berbagai layanan percetakan dan fotokopi untuk kebutuhan Anda</p>
        </div>
        <div class="services-grid">
            <?php foreach($services as $service): ?>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-print"></i>
                </div>
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                <div class="service-price">
                    <strong>Rp <?php echo number_format($service['price_per_page'], 0, ',', '.'); ?>/halaman</strong>
                </div>
                <?php if (isLoggedIn() && $_SESSION['user_role'] == 'customer'): ?>
                    <a href="customer/order.php?service=<?php echo $service['id']; ?>" class="service-link">Pesan <i class="fas fa-arrow-right"></i></a>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="register.php" class="service-link">Daftar untuk Pesan <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2>Cara Memesan</h2>
            <p>Hanya dengan 4 langkah mudah, pesanan Anda siap dikirim</p>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Daftar/Login</h3>
                    <p>Buat akun atau login ke akun Anda</p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Pilih Layanan</h3>
                    <p>Tentukan jenis layanan, kertas, dan opsi finishing yang diinginkan</p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Upload File</h3>
                    <p>Upload file yang ingin dicetak melalui website atau aplikasi</p>
                </div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>Bayar & Ambil</h3>
                    <p>Lakukan pembayaran dan ambil pesanan Anda</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about" id="about">
    <div class="container">
        <div class="section-header">
            <h2>Tentang Maziaya Fotocopy</h2>
        </div>
        <div class="about-content">
            <div class="about-text">
                <p>Maziaya Fotocopy telah melayani kebutuhan percetakan dan fotokopi masyarakat Medan. Kami berkomitmen untuk memberikan layanan terbaik dengan kualitas tinggi dan harga yang terjangkau.</p>
                <ul class="about-features">
                    <li><i class="fas fa-check"></i> Layanan cepat dan berkualitas</li>
                    <li><i class="fas fa-check"></i> Harga terjangkau</li>
                    <li><i class="fas fa-check"></i> Berpengalaman lebih dari 10 tahun</li>
                    <li><i class="fas fa-check"></i> Lokasi strategis di pusat kota Medan</li>
                </ul>
            </div>
            <div class="about-image">
    <div class="image-placeholder">
        <img src="uploads/foto.jpg" alt="Toko Maziaya Fotocopy" 
             style="max-width: 50%; max-height: 50%; border-radius: 10px;">
    </div>
</div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact" id="contact">
    <div class="container">
        <div class="section-header">
            <h2>Hubungi Kami</h2>
            <p>Butuh bantuan? Hubungi kami melalui informasi di bawah ini</p>
        </div>
        <div class="contact-info">
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <div class="contact-details">
                    <h4>Alamat</h4>
                    <p>Jalan Abdul Hakim Kp. Susuk No. 125, Jl. Abdul Hakim Kp. Susuk No.125, Padang Bulan, Kec. Medan Selayang, Kota Medan, Sumatera Utara 20155</p>
                </div>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <div class="contact-details">
                    <h4>Telepon</h4>
                    <p>0812-6990-8375<br>0823-6077-4677</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Siap Mencetak?</h2>
            <p>Upload file Anda sekarang dan dapatkan hasil cetakan terbaik dengan harga terjangkau</p>
            <?php if (isLoggedIn() && $_SESSION['user_role'] == 'customer'): ?>
                <a href="customer/order.php" class="btn-primary">Pesan Sekarang</a>
            <?php elseif (!isLoggedIn()): ?>
                <a href="register.php" class="btn-primary">Daftar Sekarang</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>