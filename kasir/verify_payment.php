<?php
include '../includes/config.php';
include '../includes/auth.php';
checkRole(['kasir']);

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Parameter tidak valid.");
}

$id = intval($_GET['id']);
$status = sanitize($_GET['status']);

if (!in_array($status, ['success', 'failed'])) {
    die("Status tidak valid.");
}

$stmt = $pdo->prepare("UPDATE payments SET status = ? WHERE id = ?");
$stmt->execute([$status, $id]);

header("Location: payments.php?verified=1");
exit;
?>
