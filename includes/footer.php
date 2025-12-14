        <?php if (!isLoggedIn() || isset($no_sidebar) || (basename($_SERVER['PHP_SELF']) == 'index.php' && !strpos($_SERVER['PHP_SELF'], 'admin/') && !strpos($_SERVER['PHP_SELF'], 'kasir/') && !strpos($_SERVER['PHP_SELF'], 'customer/'))): ?>
        </div>
        <?php endif; ?>

        <?php 
        // Close main-content div if sidebar is used
        if (isLoggedIn() && !isset($no_sidebar) && (strpos($_SERVER['PHP_SELF'], 'admin/') !== false || strpos($_SERVER['PHP_SELF'], 'kasir/') !== false || strpos($_SERVER['PHP_SELF'], 'customer/') !== false)): 
        ?>
    </div> <!-- End main-content -->
    <?php endif; ?>
    </main>

    <?php if (!isLoggedIn() || isset($no_sidebar) || (basename($_SERVER['PHP_SELF']) == 'index.php' && !strpos($_SERVER['PHP_SELF'], 'admin/') && !strpos($_SERVER['PHP_SELF'], 'kasir/') && !strpos($_SERVER['PHP_SELF'], 'customer/'))): ?>
    <footer class="custom-footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-print me-2"></i> Maziaya Fotocopy</h5>
                    <p>Solusi percetakan dan fotokopi terpercaya di Kota Medan. Layanan cepat, berkualitas, dan harga terjangkau.</p>
                </div>
                <div class="col-md-4">
    <h5>Kontak Kami</h5>
    <div class="d-flex align-items-start mb-2">
        <i class="fas fa-map-marker-alt me-2"></i>
        <div>
            Jalan Abdul Hakim Kp. Susuk No. 125, Jl. Abdul Hakim Kp. Susuk No.125, 
            Padang Bulan, Kec. Medan Selayang, Kota Medan, Sumatera Utara 20155
        </div>
    </div>
    <div class="d-flex align-items-start">
        <i class="fas fa-phone me-2"></i>
        <div>
            0812-6990-8375<br>0823-6077-4677
        </div>
    </div>
</div>
                <div class="col-md-4">
                    <h5>Jam Operasional</h5>
                    <p>Senin - Jumat : 07.00 – 20.30</p>
                    <p>Sabtu : 10.00 – 20.30</p>
                    <p>Minggu : 13.00 – 21.00</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Maziaya Fotocopy. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php 
        // Determine JS path based on current directory
        $js_path = 'assets/js/script.js';
        if (strpos($_SERVER['PHP_SELF'], 'admin/') !== false || 
            strpos($_SERVER['PHP_SELF'], 'kasir/') !== false || 
            strpos($_SERVER['PHP_SELF'], 'customer/') !== false) {
            $js_path = '../assets/js/script.js';
        }
        echo $js_path; 
    ?>"></script>
</body>
</html>