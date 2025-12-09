<?php
$page_title = "Order Details";
include_once 'includes/header.php';

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Invalid order ID";
    $_SESSION['message_type'] = "danger";
    header("Location: orders.php");
    exit;
}

$order_id = intval($_GET['id']);

// Get order details
$order_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows != 1) {
    $_SESSION['message'] = "Order not found";
    $_SESSION['message_type'] = "danger";
    header("Location: orders.php");
    exit;
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT oi.*, s.name, s.image 
              FROM order_items oi 
              JOIN services s ON oi.service_id = s.id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];

while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Order #<?php echo $order['id']; ?></h1>
    <a href="orders.php" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left me-2"></i> Back to Orders
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Order Status</h5>
            </div>
            <div class="card-body">                <div class="order-timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker <?php echo in_array($order['status'], ['pending', 'processing', 'completed']) ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h5>Order Placed</h5>
                            <p>Your order has been received and is being processed.</p>
                            <div class="timeline-date">
                                <i class="far fa-calendar-alt me-1"></i> <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker <?php echo in_array($order['status'], ['processing', 'completed']) ? 'active' : ''; ?>">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <div class="timeline-content">
                            <h5>Processing</h5>
                            <p>Your laundry is being processed.</p>
                            <?php if (in_array($order['status'], ['processing', 'completed'])): ?>
                                <div class="timeline-date">
                                    <i class="far fa-calendar-alt me-1"></i> <?php echo date('M d, Y', strtotime($order['pickup_date'])); ?> 
                                    <i class="far fa-clock ms-2 me-1"></i> <?php echo date('h:i A', strtotime($order['pickup_time'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker <?php echo in_array($order['status'], ['completed']) ? 'active' : ''; ?>">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <h5>Completed</h5>
                            <p>Your laundry has been completed and is ready for delivery or pickup.</p>
                            <?php if ($order['status'] == 'completed' && !empty($order['delivery_date'])): ?>
                                <div class="timeline-date">
                                    <i class="far fa-calendar-alt me-1"></i> <?php echo date('M d, Y h:i A', strtotime($order['delivery_date'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker <?php echo $order['status'] == 'cancelled' ? 'active' : ''; ?>">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="timeline-content">
                            <h5>Cancelled</h5>
                            <p>This order has been cancelled.</p>
                            <?php if ($order['status'] == 'cancelled'): ?>
                                <div class="timeline-date">
                                    <i class="far fa-calendar-alt me-1"></i> <?php echo date('M d, Y', strtotime($order['updated_at'])); ?> 
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Current Status:</strong>
                        <?php
                        switch ($order['status']) {
                            case 'pending':
                                echo '<span class="status-badge status-pending">Pending</span>';
                                break;
                            case 'processing':
                                echo '<span class="status-badge status-processing">Processing</span>';
                                break;
                            case 'completed':
                                echo '<span class="status-badge status-completed">Completed</span>';
                                break;
                            case 'cancelled':
                                echo '<span class="status-badge status-cancelled">Cancelled</span>';
                                break;                        default:
                                echo '<span class="status-badge">' . ucfirst($order['status']) . '</span>';
                        }
                        ?>
                    </div>
                    <div>
                        <strong>Payment Status:</strong>
                        <?php
                        if ($order['payment_status'] == 'paid') {
                            echo '<span class="badge bg-success">Paid</span>';
                        } else {
                            echo '<span class="badge bg-warning text-dark">Pending</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>                                    <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <img src="../assets/images/services/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                    <td>Rp. <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>Rp. <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th>Rp. <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Order ID:</span>
                        <span>#<?php echo $order['id']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Order Date:</span>
                        <span><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Total Items:</span>
                        <span><?php echo count($items); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Payment Method:</span>
                        <span><?php echo ucfirst($order['payment_method']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Total Amount:</span>
                        <span class="fw-bold">Rp. <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Pickup Details</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <span class="fw-bold">Date & Time:</span><br>
                        <?php echo date('M d, Y', strtotime($order['pickup_date'])); ?> at 
                        <?php echo date('h:i A', strtotime($order['pickup_time'])); ?>
                    </li>
                    <li class="list-group-item">
                        <span class="fw-bold">Address:</span><br>
                        <?php echo nl2br(htmlspecialchars($order['pickup_address'])); ?>
                    </li>
                </ul>
            </div>
        </div>
          <?php if ($order['status'] == 'completed' && $order['delivery_date']): ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Delivery Details</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <span class="fw-bold">Date & Time:</span><br>
                            <?php echo date('M d, Y', strtotime($order['delivery_date'])); ?> at 
                            <?php echo date('h:i A', strtotime($order['delivery_time'])); ?>
                        </li>
                        <li class="list-group-item">
                            <span class="fw-bold">Address:</span><br>
                            <?php echo nl2br(htmlspecialchars($order['delivery_address'] ?: $order['pickup_address'])); ?>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="d-grid gap-2">
            <?php if ($order['status'] == 'pending'): ?>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                    <i class="fas fa-times-circle me-2"></i> Cancel Order
                </button>
            <?php endif; ?>
            
            <a href="place_order.php" class="btn btn-primary">
                <i class="fas fa-redo me-2"></i> Place Similar Order
            </a>
        </div>
    </div>
</div>

<?php if ($order['status'] == 'pending'): ?>
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="cancel_order.php?id=<?php echo $order['id']; ?>" class="btn btn-danger">Cancel Order</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>