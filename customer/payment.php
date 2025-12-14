<?php
include '../includes/config.php';
include '../includes/auth.php';
checkRole(['customer']); // kalau customer login

$page_title = "Pembayaran Digital";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Pembayaran Daring</h1>

    <div class="row">
        <!-- QRIS -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">QRIS Pembayaran</h6>
                </div>
                <div class="card-body text-center">
                    <img src="../assets/img/qris.png" alt="QRIS" style="width: 200px;">
                    <p class="mt-3">Scan QR untuk melakukan pembayaran via QRIS.</p>
                </div>
            </div>
        </div>

        <!-- Transfer Bank -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Transfer Bank</h6>
                </div>
                <div class="card-body">
                    <p><strong>Bank BCA</strong></p>
                    <p>Nomor Rekening: <strong>1234567890</strong></p>
                    <p>a.n Maziaya Fotocopy</p>

                    <hr>

                    <p><strong>Bank Mandiri</strong></p>
                    <p>Nomor Rekening: <strong>9876543210</strong></p>
                    <p>a.n Maziaya Fotocopy</p>
                </div>
            </div>
        </div>

        <!-- Upload Bukti Pembayaran -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Unggah Bukti Pembayaran</h6>
                </div>
                <div class="card-body">
                    <form action="upload_payment.php" method="POST" enctype="multipart/form-data">

    <div class="form-group">
        <label>ID Pesanan</label>
        <input type="text" name="order_id" class="form-control" 
               value="<?php echo isset($_GET['order_id']) ? $_GET['order_id'] : ''; ?>" 
               readonly>
    </div>

    <div class="form-group mt-3">
        <label>Metode Pembayaran</label>
        <select name="payment_method" class="form-control" required>
            <option value="qris">QRIS</option>
            <option value="transfer">Transfer Bank</option>
        </select>
    </div>

    <div class="form-group mt-3">
        <label>Catatan (opsional)</label>
        <textarea name="notes" class="form-control"></textarea>
    </div>

    <div class="form-group mt-3">
        <label>Upload Bukti Pembayaran</label>
        <input type="file" name="bukti" accept="image/*" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary mt-3 w-100">
        Kirim Bukti Pembayaran
    </button>

</form>

                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
