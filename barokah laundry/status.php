<?php
$page_title = "Status Pesanan"; 
include_once 'includes/header.php';

// Inisialisasi variabel
$tracking_status = null;
$order_details = null;
$order_items = []; 
$error_message = '';

// Proses form tracking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($conn) || !function_exists('sanitize_input')) {
        $error_message = "Sistem gagal terhubung ke database atau fungsi sanitasi tidak tersedia.";
    } else {
        $order_code = isset($_POST['order_code']) ? sanitize_input($_POST['order_code']) : '';

        if (empty($order_code)) {
            $error_message = "Mohon masukkan Kode Order Anda.";
        } else {
            // 1. AMBIL DATA UTAMA ORDER
            $sql = "SELECT 
                        o.id, o.user_id, o.status, o.total_amount, o.created_at, 
                        o.pickup_date, o.pickup_time, o.package_id,
                        o.customer_name as manual_name,
                        u.name as account_name, u.email,
                        p.name as package_name
                    FROM orders o
                    LEFT JOIN users u ON o.user_id = u.id
                    LEFT JOIN packages p ON o.package_id = p.id
                    WHERE o.id = ?"; 

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_code); 
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $order_details = $result->fetch_assoc();
                $tracking_status = $order_details['status'];

                // 2. AMBIL DETAIL ITEM (PERBAIKAN DI SINI: Hapus s.unit)
                $item_sql = "SELECT oi.quantity, oi.price, s.name as service_name 
                             FROM order_items oi 
                             JOIN services s ON oi.service_id = s.id 
                             WHERE oi.order_id = ?";
                $item_stmt = $conn->prepare($item_sql);
                $item_stmt->bind_param("i", $order_code);
                $item_stmt->execute();
                $item_result = $item_stmt->get_result();
                
                while ($row = $item_result->fetch_assoc()) {
                    $order_items[] = $row;
                }
                
            } else {
                $error_message = "Kode Order **#" . htmlspecialchars($order_code) . "** tidak ditemukan atau tidak valid.";
            }
        }
    }
}
?>

<div class="bg-light py-5 mb-4">
    <div class="container">
        <h1 class="text-center">🔍 Lacak Status Cucian Anda</h1>
        <p class="text-center lead">Masukkan Kode Order (ID Pesanan) di bawah ini.</p>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="post" action="status.php">
                        <div class="input-group input-group-lg">
                            <input 
                                type="text" 
                                class="form-control" 
                                name="order_code" 
                                placeholder="Masukkan Kode Order (Contoh: 1)" 
                                required
                                value="<?php echo isset($_POST['order_code']) ? htmlspecialchars($_POST['order_code']) : ''; ?>"
                            >
                            <button type="submit" class="btn btn-danger">Lacak Status</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger text-center">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($order_details): ?>
                
                <h2 class="mt-5 mb-3 text-primary">Status Order #<?php echo $order_details['id']; ?></h2>
                
                <?php 
                    // Logic Progress Bar & Warna
                    $current_status = $order_details['status'];
                    $status_map = [
                        'pending' => 'Menunggu Diproses',
                        'processing' => 'Sedang Dicuci/Diproses',
                        'ready_for_pickup' => 'Siap Diambil',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan'
                    ];
                    $status_text = $status_map[$current_status] ?? ucfirst($current_status);
                    $bg_class = get_status_class($current_status);
                    
                    // Sederhanakan step untuk progress bar
                    $steps = ['pending', 'processing', 'completed'];
                    $progress_percentage = 0;
                    
                    if ($current_status == 'cancelled') {
                        $progress_percentage = 100;
                    } else {
                        // Cek apakah status ada di steps
                        $key = array_search($current_status, $steps);
                        if ($key !== false) {
                            $progress_percentage = ($key + 1) * (100 / count($steps));
                        } else {
                            // Jika status 'ready_for_pickup' atau lainnya yang tidak ada di array steps sederhana
                            if ($current_status == 'ready_for_pickup') $progress_percentage = 75;
                        }
                    }
                ?>

                <div class="card shadow mb-4">
                    <div class="card-header bg-<?php echo $bg_class; ?> text-white">
                        <h5 class="mb-0">Status: <?php echo $status_text; ?></h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="progress mb-4" style="height: 25px;">
                            <div 
                                class="progress-bar progress-bar-striped progress-bar-animated bg-<?php echo $bg_class; ?>" 
                                role="progressbar" 
                                style="width: <?php echo $progress_percentage; ?>%;" 
                                aria-valuenow="<?php echo $progress_percentage; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"
                            >
                                <?php echo $status_text; ?>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Informasi Pelanggan</h6>
                                <p class="fw-bold mb-1">
                                    <?php 
                                    if (!empty($order_details['manual_name'])) {
                                        echo htmlspecialchars($order_details['manual_name']);
                                    } else {
                                        echo htmlspecialchars($order_details['account_name']) . " (Akun)";
                                    }
                                    ?>
                                </p>
                                <p class="text-muted small">Order ID: #<?php echo $order_details['id']; ?></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h6 class="text-muted">Jadwal Pickup (Penjemputan)</h6>
                                <p class="fw-bold mb-0">
                                    <i class="fas fa-calendar-alt me-1"></i> 
                                    <?php echo date('d M Y', strtotime($order_details['pickup_date'])); ?>
                                </p>
                                <p class="fw-bold text-primary">
                                    <i class="fas fa-clock me-1"></i> 
                                    <?php echo date('H:i', strtotime($order_details['pickup_time'])); ?> WIB
                                </p>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">Rincian Item</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Layanan</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($order_details['package_name'])): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-info me-2">Paket</span>
                                                <?php echo htmlspecialchars($order_details['package_name']); ?>
                                            </td>
                                            <td class="text-center">1 Paket</td>
                                            <td class="text-end">-</td>
                                            <td class="text-end">Rp. <?php echo number_format($order_details['total_amount'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php elseif (count($order_items) > 0): ?>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['service_name']); ?></td>
                                                <td class="text-center">
                                                    <?php echo $item['quantity']; ?>
                                                </td>
                                                <td class="text-end">
                                                    Rp. <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                                </td>
                                                <td class="text-end fw-bold">
                                                    Rp. <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Detail item tidak tersedia.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total Biaya</td>
                                        <td class="text-end fw-bold text-success fs-5">
                                            Rp. <?php echo number_format($order_details['total_amount'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <?php if ($current_status == 'cancelled'): ?>
                            <div class="alert alert-danger mt-3 text-center">
                                <strong>Perhatian:</strong> Pesanan ini telah dibatalkan. Silakan hubungi kami untuk info lebih lanjut.
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php 
// Fungsi bantu untuk mendapatkan kelas warna Bootstrap berdasarkan status
function get_status_class($status) {
    switch ($status) {
        case 'pending': return 'warning text-dark';
        case 'processing': return 'info text-white';
        case 'ready_for_pickup': return 'primary';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}
include_once 'includes/footer.php'; 
?>