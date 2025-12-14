<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo defined('SITE_NAME') ? SITE_NAME : 'Maziaya Fotocopy'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php 
        // Determine CSS path based on current directory
        $css_path = 'assets/css/style.css';
        if (strpos($_SERVER['PHP_SELF'], 'admin/') !== false || 
            strpos($_SERVER['PHP_SELF'], 'kasir/') !== false || 
            strpos($_SERVER['PHP_SELF'], 'customer/') !== false) {
            $css_path = '../assets/css/style.css';
        }
        echo $css_path; 
    ?>" rel="stylesheet">
</head>
<body class="<?php echo (isLoggedIn() && !isset($no_sidebar)) ? 'bg-light' : ''; ?>">
    
    <?php 
    // Show main navbar only if not in dashboard pages
    $current_page = basename($_SERVER['PHP_SELF']);
    $is_dashboard = strpos($_SERVER['PHP_SELF'], 'admin/') !== false || 
                   strpos($_SERVER['PHP_SELF'], 'kasir/') !== false || 
                   strpos($_SERVER['PHP_SELF'], 'customer/') !== false;
    
    if (!isLoggedIn() || isset($no_sidebar) || (!$is_dashboard && $current_page == 'index.php')): 
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-print me-2"></i>
                <?php echo defined('SITE_NAME') ? SITE_NAME : 'Maziaya Fotocopy'; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#services">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about">Tentang Kami</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Kontak</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i> <?php echo $_SESSION['full_name']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if ($_SESSION['user_role'] == 'customer'): ?>
                                    <li><a class="dropdown-item" href="customer/index.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item" href="customer/order.php"><i class="fas fa-plus-circle me-2"></i>Pesan Layanan</a></li>
                                    <li><a class="dropdown-item" href="customer/history.php"><i class="fas fa-history me-2"></i>Riwayat Pesanan</a></li>
                                <?php elseif ($_SESSION['user_role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</a></li>
                                    <li><a class="dropdown-item" href="admin/orders.php"><i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan</a></li>
                                <?php elseif ($_SESSION['user_role'] == 'kasir'): ?>
                                    <li><a class="dropdown-item" href="kasir/index.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard Kasir</a></li>
                                    <li><a class="dropdown-item" href="kasir/orders.php"><i class="fas fa-shopping-cart me-2"></i>Kelola Pesanan</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="customer/profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main>
        <?php if (!isLoggedIn() || isset($no_sidebar) || (!$is_dashboard && $current_page == 'index.php')): ?>
        <div class="container mt-4">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
        <?php endif; ?>