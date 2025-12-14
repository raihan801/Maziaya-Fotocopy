<?php
include '../includes/config.php';
include '../includes/auth.php';

// Cek role admin
checkRole(['admin']);

// Handle Export - HARUS sebelum include header apapun
if (isset($_GET['export'])) {
    $export_type = sanitize($_GET['export']);
    $start_date = sanitize($_GET['start_date']);
    $end_date = sanitize($_GET['end_date']);
    
    // Query data untuk export
    $stmt = $pdo->prepare("
        SELECT 
            o.order_number,
            u.full_name as customer_name,
            s.name as service_name,
            o.page_count,
            o.color_print,
            o.binding_type,
            o.total_price,
            o.status,
            o.payment_status,
            o.order_date,
            o.completed_at
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        JOIN services s ON o.service_id = s.id 
        WHERE DATE(o.order_date) BETWEEN ? AND ?
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $export_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($export_type == 'pdf') {
        exportToPDF($export_data, $start_date, $end_date);
    } elseif ($export_type == 'excel') {
        exportToCSV($export_data, $start_date, $end_date);
    }
    exit; // Pastikan exit setelah export
}

// Fungsi Export ke PDF dengan TCPDF
function exportToPDF($data, $start_date, $end_date) {
    // Pastikan tidak ada output sebelum ini
    if (ob_get_length()) ob_clean();
    
    require_once('../tcpdf/tcpdf.php');
    
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Maziaya Fotocopy');
    $pdf->SetAuthor('Maziaya Fotocopy');
    $pdf->SetTitle('Laporan Pesanan');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Content
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'LAPORAN MAZIAYA FOTOCOPY', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 8, 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)), 0, 1, 'C');
    
    $pdf->Ln(10);
    
    // Simple table
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(30, 8, 'No. Pesanan', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Customer', 1, 0, 'C');
    $pdf->Cell(40, 8, 'Layanan', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Halaman', 1, 0, 'C');
    $pdf->Cell(20, 8, 'Total', 1, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 9);
    $total_revenue = 0;
    
    foreach($data as $row) {
        $pdf->Cell(30, 8, $row['order_number'], 1, 0, 'L');
        $pdf->Cell(40, 8, $row['customer_name'], 1, 0, 'L');
        $pdf->Cell(40, 8, $row['service_name'], 1, 0, 'L');
        $pdf->Cell(20, 8, $row['page_count'], 1, 0, 'C');
        $pdf->Cell(20, 8, 'Rp ' . number_format($row['total_price'], 0, ',', '.'), 1, 1, 'R');
        $total_revenue += $row['total_price'];
    }
    
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Total Pendapatan: Rp ' . number_format($total_revenue, 0, ',', '.'), 0, 1, 'L');
    $pdf->Cell(0, 10, 'Total Pesanan: ' . count($data), 0, 1, 'L');
    
    // Output PDF - langsung download
    $pdf->Output('laporan_maziaya_' . $start_date . '_' . $end_date . '.pdf', 'D');
    exit;
}

// Fungsi Export ke CSV
function exportToCSV($data, $start_date, $end_date) {
    if (ob_get_length()) ob_clean();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_' . $start_date . '_' . $end_date . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header
    fputcsv($output, ['No. Pesanan', 'Customer', 'Layanan', 'Halaman', 'Warna', 'Total', 'Status', 'Tanggal']);
    
    // Data
    foreach($data as $row) {
        fputcsv($output, [
            $row['order_number'],
            $row['customer_name'],
            $row['service_name'],
            $row['page_count'],
            $row['color_print'] ? 'Ya' : 'Tidak',
            $row['total_price'],
            $row['status'],
            date('d/m/Y', strtotime($row['order_date']))
        ]);
    }
    
    fclose($output);
    exit;
}

$page_title = "Laporan";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/topbar.php';

// Filter tanggal
$start_date = isset($_GET['start_date']) ? sanitize($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize($_GET['end_date']) : date('Y-m-t');

// Statistik umum
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_price) as total_revenue,
        AVG(total_price) as avg_order_value
    FROM orders 
    WHERE DATE(order_date) BETWEEN ? AND ? AND payment_status = 'paid'
");
$stmt->execute([$start_date, $end_date]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Pesanan per status
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as count 
    FROM orders 
    WHERE DATE(order_date) BETWEEN ? AND ?
    GROUP BY status
");
$stmt->execute([$start_date, $end_date]);
$status_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Layanan terpopuler
$stmt = $pdo->prepare("
    SELECT s.name, COUNT(*) as order_count, SUM(o.total_price) as revenue
    FROM orders o
    JOIN services s ON o.service_id = s.id
    WHERE DATE(o.order_date) BETWEEN ? AND ?
    GROUP BY s.id, s.name
    ORDER BY order_count DESC
    LIMIT 5
");
$stmt->execute([$start_date, $end_date]);
$popular_services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pendapatan per bulan (untuk chart)
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') as month,
        SUM(total_price) as revenue
    FROM orders 
    WHERE payment_status = 'paid'
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
");
$stmt->execute();
$monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
$monthly_revenue = array_reverse($monthly_revenue); // Urut dari terlama
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Laporan</h5>
    </div>
    <div class="card-body">
        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-5">
                            <label for="start_date" class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-5">
                            <label for="end_date" class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <a href="reports.php?export=pdf&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>Export PDF
                </a>
                <a href="reports.php?export=excel&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-1"></i>Export Excel
                </a>
            </div>
        </div>

        <!-- Statistik Utama -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?php echo $stats['total_orders'] ?? 0; ?></h3>
                        <p>Total Pesanan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body text-center">
                        <h3>Rp <?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body text-center">
                        <h3>Rp <?php echo number_format($stats['avg_order_value'] ?? 0, 0, ',', '.'); ?></h3>
                        <p>Rata-rata per Pesanan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Distribusi Status Pesanan</h6>
                    </div>
                    <div class="card-body">
                        <?php if (count($status_stats) > 0): ?>
                            <?php foreach($status_stats as $stat): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><?php echo ucfirst(str_replace('_', ' ', $stat['status'])); ?></span>
                                    <span><?php echo $stat['count']; ?></span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-<?php 
                                        switch($stat['status']) {
                                            case 'pending': echo 'warning'; break;
                                            case 'confirmed': echo 'info'; break;
                                            case 'in_progress': echo 'primary'; break;
                                            case 'completed': echo 'success'; break;
                                            case 'cancelled': echo 'danger'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>" style="width: <?php echo ($stat['count'] / array_sum(array_column($status_stats, 'count'))) * 100; ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">Tidak ada data</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Layanan Terpopuler</h6>
                    </div>
                    <div class="card-body">
                        <?php if (count($popular_services) > 0): ?>
                            <?php foreach($popular_services as $service): ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span><?php echo $service['name']; ?></span>
                                    <span><?php echo $service['order_count']; ?> pesanan</span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>Pendapatan:</span>
                                    <span>Rp <?php echo number_format($service['revenue'], 0, ',', '.'); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">Tidak ada data</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Pendapatan -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Trend Pendapatan 6 Bulan Terakhir</h6>
            </div>
            <div class="card-body">
                <?php if (count($monthly_revenue) > 0): ?>
                    <div style="height: 300px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">Tidak ada data untuk ditampilkan</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (count($monthly_revenue) > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php echo implode(',', array_map(function($item) { 
            return "'" . date('M Y', strtotime($item['month'] . '-01')) . "'"; 
        }, $monthly_revenue)); ?>],
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: [<?php echo implode(',', array_column($monthly_revenue, 'revenue')); ?>],
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Pendapatan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>