<?php
$page_title = "My Orders";
include_once 'includes/header.php';

// --- LOGIC UPDATE PEMBAYARAN ---
if (isset($_POST['update_payment'])) {
    $order_id = $_POST['order_id'];
    $payment_status = $_POST['payment_status'];
    
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $payment_status, $order_id);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success alert-dismissible fade show' style='margin: 20px;'>Status pembayaran diperbarui! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// Pagination setup
$records_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

// Filter by status
$status_filter = '';
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $conn->real_escape_string($_GET['status']);
    $status_filter = "AND o.status = '$status'";
}

// Get total orders count for pagination
$count_sql = "SELECT COUNT(*) as total FROM orders o WHERE o.user_id = ? $status_filter";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get orders with pagination
$orders_sql = "SELECT o.*, 
                SUM(oi.quantity) as total_items 
              FROM orders o 
              LEFT JOIN order_items oi ON o.id = oi.order_id 
              WHERE o.user_id = ? $status_filter
              GROUP BY o.id 
              ORDER BY o.created_at DESC 
              LIMIT ?, ?";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("iii", $user_id, $offset, $records_per_page);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders = [];

while ($order = $orders_result->fetch_assoc()) {
    $orders[] = $order;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>My Orders</h1>
    <a href="place_order.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> Place New Order
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Filter by Status</label>                <select class="form-select" id="status" name="status">
                    <option value="">All Orders</option>
                    <option value="pending" <?php echo isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo isset($_GET['status']) && $_GET['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="completed" <?php echo isset($_GET['status']) && $_GET['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo isset($_GET['status']) && $_GET['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <?php if (isset($_GET['status']) && !empty($_GET['status'])): ?>
                    <a href="orders.php" class="btn btn-outline-secondary ms-2">Clear Filter</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Order History</h5>
    </div>
    <div class="card-body">
        <?php if (count($orders) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo $order['total_items']; ?> items</td>
                                
                                <td>Rp. <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>                               
                                <td>
                                    <?php
                                    switch ($order['status']) {
                                        case 'pending':
                                            echo '<span class="badge bg-warning text-dark">Pending</span>';
                                            break;
                                        case 'processing':
                                            echo '<span class="badge bg-info text-white">Processing</span>';
                                            break;
                                        case 'completed':
                                            echo '<span class="badge bg-success">Completed</span>';
                                            break;
                                        case 'cancelled':
                                            echo '<span class="badge bg-danger">Cancelled</span>';
                                            break;
                                        default:
                                            echo '<span class="badge bg-secondary">' . ucfirst($order['status']) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($order['payment_status'] == 'paid') {
                                        echo '<span class="badge bg-success">Paid</span>';
                                    } else {
                                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                                    }
                                    echo ' <small class="text-muted">(' . ucfirst($order['payment_method']) . ')</small>';
                                    ?>
                                </td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success ms-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#payModal<?php echo $order['id']; ?>" 
                                            title="Update Pembayaran">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>

                                    <div class="modal fade" id="payModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Pembayaran Order #<?php echo $order['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Total Tagihan</label>
                                                            <input type="text" class="form-control" value="Rp. <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>" readonly>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">Status Pembayaran Saat Ini</label>
                                                            <select name="payment_status" class="form-select">
                                                                <option value="pending" <?php echo ($order['payment_status'] == 'pending') ? 'selected' : ''; ?>>Pending (Belum Lunas)</option>
                                                                <option value="paid" <?php echo ($order['payment_status'] == 'paid') ? 'selected' : ''; ?>>Paid (Lunas)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="update_payment" class="btn btn-success">Simpan</button>
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
            
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Orders pagination">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-basket fa-3x text-muted mb-3"></i>
                <h4>No orders found</h4>
                <p class="text-muted">You haven't placed any orders yet or no orders match your filter.</p>
                <a href="place_order.php" class="btn btn-primary mt-3">Place Your First Order</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>