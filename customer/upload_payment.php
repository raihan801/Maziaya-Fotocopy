<?php
include '../includes/config.php';
include '../includes/auth.php';
checkRole(['customer']); // hanya customer

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validasi order_id
    if (!isset($_POST['order_id']) || empty($_POST['order_id'])) {
        die("Order ID tidak valid.");
    }

    $order_id = intval($_POST['order_id']);

    // Ambil total harga dari tabel orders
    $stmt = $pdo->prepare("SELECT total_price FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Pesanan tidak ditemukan.");
    }

    $amount = $order['total_price'];
    $payment_method = sanitize($_POST['payment_method']);
    $notes = sanitize($_POST['notes']);

    // Upload bukti pembayaran
    $proof_path = null;

    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == UPLOAD_ERR_OK) {

        $dir = "../uploads/payments/";
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext = pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION);
        $filename = "payment_" . time() . "_" . rand(1000,9999) . "." . $ext;

        $target = $dir . $filename;

        if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target)) {
            $proof_path = "uploads/payments/" . $filename;
        }
    }

    // Simpan ke tabel payments
    $stmt = $pdo->prepare("
    INSERT INTO payments (order_id, amount, payment_method, transaction_id, status, notes, proof_image)
    VALUES (?, ?, ?, ?, 'pending', ?, ?)
");

$stmt->execute([$order_id, $amount, $payment_method, $transaction_id, $notes, $proof_path]);


    // Redirect
    header("Location: payment_success.php?order_id=" . $order_id);
    exit;
}
?>
