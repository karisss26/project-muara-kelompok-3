<?php
$page_title = "Admin Dashboard";
include_once 'includes/header.php';

// Get statistics
$stats = [];

// 1. Total Users (Akun Sistem: Admin & Kasir)
$user_sql = "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN user_role = 'admin' THEN 1 ELSE 0 END) as total_admins
            FROM users";
$user_result = $conn->query($user_sql);
$user_stats = $user_result->fetch_assoc();
$stats['users'] = $user_stats;

// 2. Total Customers (Real: Dari Inputan Order)
$cust_sql = "SELECT COUNT(DISTINCT customer_name) as real_total_customers 
             FROM orders 
             WHERE customer_name IS NOT NULL 
             AND customer_name != '' 
             AND status != 'cancelled'";
$cust_result = $conn->query($cust_sql);
$cust_stats = $cust_result->fetch_assoc();
$stats['real_customers'] = $cust_stats['real_total_customers'];

// 3. Total Orders & Revenue
$order_sql = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as total_revenue
            FROM orders";
$order_result = $conn->query($order_sql);
$order_stats = $order_result->fetch_assoc();
$stats['orders'] = $order_stats;

// Get recent orders
$recent_orders_sql = "SELECT o.*, o.customer_name, u.name as account_name
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id
                      ORDER BY o.created_at DESC 
                      LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_sql);
$recent_orders = [];
while ($order = $recent_orders_result->fetch_assoc()) {
    $recent_orders[] = $order;
}

// Get recent users
$recent_users_sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users_result = $conn->query($recent_users_sql);
$recent_users = [];
while ($user_row = $recent_users_result->fetch_assoc()) {
    $recent_users[] = $user_row;
}

// --- 5. GET ACTIVITY LOGS (BARU) ---
$logs = [];
// Cek apakah tabel activity_logs ada (untuk menghindari error jika belum dibuat)
$check_logs_table = $conn->query("SHOW TABLES LIKE 'activity_logs'");
if ($check_logs_table->num_rows > 0) {
    $logs_sql = "SELECT l.*, u.name as user_name, u.user_role 
                 FROM activity_logs l 
                 LEFT JOIN users u ON l.user_id = u.id 
                 ORDER BY l.created_at DESC 
                 LIMIT 10";
    $logs_result = $conn->query($logs_sql);
    if ($logs_result) {
        while ($row = $logs_result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
}
?>

<h1 class="mb-4">Admin Dashboard</h1>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-stat-card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-count"><?php echo $stats['orders']['total_orders'] ?? 0; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-icon bg-white text-primary"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-stat-card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-count">Rp. <?php echo number_format($stats['orders']['total_revenue'] ?? 0, 0, ',', '.'); ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-icon bg-white text-success"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-stat-card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-count"><?php echo $stats['real_customers'] ?? 0; ?></div>
                        <div class="stat-label">Total Customers</div>
                    </div>
                    <div class="stat-icon bg-white text-info"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card dashboard-stat-card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-count"><?php echo $stats['orders']['pending_orders'] ?? 0; ?></div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                    <div class="stat-icon bg-white text-warning"><i class="fas fa-clock"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Order Status Overview</h5>
            </div>
            <div class="card-body">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6"><a href="orders.php" class="btn btn-primary w-100 py-3"><i class="fas fa-list-alt me-2"></i> Manage Orders</a></div>                  
                    <div class="col-6"><a href="services.php" class="btn btn-success w-100 py-3"><i class="fas fa-plus-circle me-2"></i> Add Service</a></div>
                    <div class="col-6"><a href="users.php" class="btn btn-primary w-100 py-3 text-white"><i class="fas fa-user-plus me-2"></i> Add User</a></div>
                    <div class="col-6"><a href="reports.php" class="btn btn-secondary w-100 py-3"><i class="fas fa-chart-line me-2"></i> View Reports</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>
                                    <?php 
                                        if(!empty($order['customer_name'])) {
                                            echo "<strong>".htmlspecialchars($order['customer_name'])."</strong>";
                                        } else {
                                            echo htmlspecialchars($order['account_name']) . " <small class='text-muted'>(Akun)</small>";
                                        }
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td>Rp. <?php echo number_format($order['total_amount'] ?? 0, 0, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    switch ($order['status']) {
                                        case 'pending': echo '<span class="badge bg-warning text-dark">Pending</span>'; break;
                                        case 'processing': echo '<span class="badge bg-primary">Processing</span>'; break;
                                        case 'completed': echo '<span class="badge bg-success">Completed</span>'; break;
                                        case 'cancelled': echo '<span class="badge bg-danger">Cancelled</span>'; break;
                                        default: echo '<span class="badge bg-secondary">' . ucfirst($order['status']) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 mb-4">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent System Users</h5>
                <a href="users.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($recent_users as $user_item): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">                                
                                <div>
                                    <h6 class="mb-0"><?php echo $user_item['name']; ?></h6>
                                    <small class="text-muted"><?php echo $user_item['email']; ?></small>
                                </div>
                                <span class="badge bg-<?php echo $user_item['user_role'] == 'admin' ? 'danger' : 'success'; ?>">
                                    <?php echo ucfirst($user_item['user_role']); ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history me-2"></i> Recent System Activities</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th width="15%">Time</th>
                                <th width="20%">User</th>
                                <th width="15%">Action</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Belum ada aktivitas tercatat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td class="small"><?php echo date('d M Y H:i', strtotime($log['created_at'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($log['user_name'] ?? 'Unknown'); ?></strong>
                                            <br>
                                            <span class="badge bg-secondary" style="font-size: 0.7rem;"><?php echo ucfirst($log['user_role'] ?? ''); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-white"><?php echo htmlspecialchars($log['action']); ?></span>
                                        </td>
                                        <td class="small text-muted">
                                            <?php echo htmlspecialchars($log['description']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('orderStatusChart').getContext('2d');
    var orderStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
            datasets: [{
                data: [
                    <?php echo $stats['orders']['pending_orders'] ?? 0; ?>,
                    <?php echo $stats['orders']['processing_orders'] ?? 0; ?>,
                    <?php echo $stats['orders']['completed_orders'] ?? 0; ?>,
                    <?php echo $stats['orders']['cancelled_orders'] ?? 0; ?>
                ],
                backgroundColor: ['#ffc107', '#0d6efd', '#198754', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
            cutout: '70%'
        }
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>