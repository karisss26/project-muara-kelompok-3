<?php
$page_title = "Kasir Dashboard";
include_once 'includes/header.php';

// ---------------------------------------------------------
// 1. LOGIC UPDATE STATUS
// ---------------------------------------------------------
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    // Update status di database
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);
    
    if ($update_stmt->execute()) {
        // --- LOG AKTIVITAS ---
        // Pastikan fungsi logActivity ada di config.php
        if (function_exists('logActivity')) {
            logActivity($conn, $_SESSION['user_id'], 'Update Status', 'Mengubah status Order #' . $order_id . ' menjadi ' . $new_status);
        }
        // ---------------------
        
        $success_msg = "Status pesanan #$order_id berhasil diperbarui!";
    } else {
        $error_msg = "Gagal memperbarui status.";
    }
}

// ---------------------------------------------------------
// 2. QUERY STATISTICS
// ---------------------------------------------------------
$stats_sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders
            FROM orders";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// ---------------------------------------------------------
// 3. QUERY RECENT ORDERS (DIPERBARUI)
// ---------------------------------------------------------
// Kita ambil 'o.customer_name' (manual) dan 'u.name' (akun)
$recent_orders_sql = "SELECT o.*, 
                        o.customer_name as manual_name, 
                        u.name as account_name,
                        SUM(oi.quantity) as total_items 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      LEFT JOIN order_items oi ON o.id = oi.order_id 
                      GROUP BY o.id 
                      ORDER BY o.created_at DESC 
                      LIMIT 10"; 
$recent_orders_result = $conn->query($recent_orders_sql);
$recent_orders = [];

while ($order = $recent_orders_result->fetch_assoc()) {
    $recent_orders[] = $order;
}
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Kasir Dashboard</h1>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Halo, <?php echo $user['name']; ?>!</h5>
            <p class="card-text">Selamat bekerja. Berikut adalah ringkasan pesanan masuk.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h3 mb-0"><?php echo $stats['total_orders'] ?: 0; ?></div>
                        <div class="small">Total Orders</div>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h3 mb-0"><?php echo $stats['pending_orders'] ?: 0; ?></div>
                        <div class="small">Pending Orders</div>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h3 mb-0"><?php echo $stats['processing_orders'] ?: 0; ?></div>
                        <div class="small">Processing</div>
                    </div>
                    <i class="fas fa-sync-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h3 mb-0"><?php echo $stats['completed_orders'] ?: 0; ?></div>
                        <div class="small">Completed</div>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Pesanan Masuk (Terbaru)
        </div>
        <div class="card-body">
            <?php if (count($recent_orders) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Pelanggan</th> 
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status Saat Ini</th>
                                <th>Aksi (Update)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    
                                    <td>
                                        <?php 
                                        if (!empty($order['manual_name'])) {
                                            // Tampilkan nama manual jika ada
                                            echo "<strong>" . htmlspecialchars($order['manual_name']) . "</strong>";
                                        } else {
                                            // Fallback ke nama akun jika kosong
                                            echo htmlspecialchars($order['account_name']) . " <small class='text-muted'>(Akun)</small>";
                                        }
                                        ?>
                                    </td>
                                    
                                    <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                    
                                    <td>Rp. <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                    
                                    <td>
                                        <?php
                                        $status_class = match ($order['status']) {
                                            'pending' => 'bg-warning text-dark',
                                            'processing' => 'bg-info text-white',
                                            'completed' => 'bg-success text-white',
                                            'cancelled' => 'bg-danger text-white',
                                            default => 'bg-secondary text-white'
                                        };
                                        ?>
                                        <span class="badge <?php echo $status_class; ?> rounded-pill">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $order['id']; ?>" title="Ubah Status">
                                                <i class="fas fa-edit"></i> Ubah Status
                                            </button>
                                        </div>

                                        <div class="modal fade" id="updateModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Update Status Order #<?php echo $order['id']; ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Pilih Status Baru:</label>
                                                                <select name="status" class="form-select">
                                                                    <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                                    <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Processing (Sedang Dikerjakan)</option>
                                                                    <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>Completed (Selesai)</option>
                                                                    <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled (Dibatalkan)</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="update_status" class="btn btn-primary">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    Belum ada pesanan masuk.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>