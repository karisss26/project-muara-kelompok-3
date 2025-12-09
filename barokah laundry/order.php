<?php
$page_title = "Place an Order";
include_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Please login to place an order";
    $_SESSION['message_type'] = "warning";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT address, phone FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$error = '';
$success = '';
$services = [];
$package = null;

// --- LOGIC GET SERVICES ---
if (isset($_GET['service_id'])) {
    $service_id = $_GET['service_id'];
    $sql = "SELECT * FROM services WHERE id = ? AND active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) $services[] = $result->fetch_assoc();
} elseif (isset($_GET['package'])) {
    $package_code = $_GET['package'];
    $package_sql = "SELECT * FROM packages WHERE code = ? AND active = 1";
    $package_stmt = $conn->prepare($package_sql);
    $package_stmt->bind_param("s", $package_code);
    $package_stmt->execute();
    $package_result = $package_stmt->get_result();
    
    if ($package_result->num_rows > 0) {
        $package = $package_result->fetch_assoc();
        $included_services = explode(',', $package['includes_services']);
        $services_sql = "SELECT * FROM services WHERE name IN (" . str_repeat('?,', count($included_services) - 1) . "?) AND active = 1";
        $services_stmt = $conn->prepare($services_sql);
        $services_stmt->bind_param(str_repeat('s', count($included_services)), ...$included_services);
        $services_stmt->execute();
        $services_result = $services_stmt->get_result();
        if ($services_result->num_rows > 0) {
            while ($row = $services_result->fetch_assoc()) $services[] = $row;
        }
    } else {
        $sql = "SELECT * FROM services WHERE active = 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) $services[] = $row;
        }
    }
} else {
    $sql = "SELECT * FROM services WHERE active = 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) $services[] = $row;
    }
}

// --- PROCESS ORDER ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = sanitize_input($_POST['customer_name']);
    $pickup_date = sanitize_input($_POST['pickup_date']);
    $pickup_time = sanitize_input($_POST['pickup_time']);
    $pickup_address = sanitize_input($_POST['pickup_address']);
    $payment_method = sanitize_input($_POST['payment_method']);
    $total_amount = floatval(sanitize_input($_POST['total_amount']));
    $package_id = isset($_POST['package_id']) ? intval(sanitize_input($_POST['package_id'])) : null;
    
    // Validasi Item
    $has_items = false;
    if ($package_id) {
        $has_items = true; 
    } else {
        foreach ($_POST['quantity'] as $service_id => $quantity) {
            if (intval($quantity) > 0) { $has_items = true; break; }
        }
    }
    
    if (empty($customer_name) || empty($pickup_date) || empty($pickup_time) || empty($pickup_address)) {
        $error = "Mohon isi semua data yang diperlukan.";
    } elseif ($total_amount <= 0) {
        $error = "Total pesanan harus lebih dari 0.";
    } elseif (!$has_items) {
        $error = "Pilih minimal satu layanan.";
    } else {
        $conn->begin_transaction();
        try {
            // Jika metode online, kita anggap sudah lunas (karena kasir sudah verifikasi visual)
            // Atau tetap 'pending' jika admin perlu cek mutasi. Di sini saya set 'pending' biar aman.
            $payment_status = 'pending'; 

            // Insert Order (TANPA proof_image)
            if ($package_id) {
                $order_sql = "INSERT INTO orders (user_id, customer_name, total_amount, status, payment_status, payment_method, pickup_date, pickup_time, pickup_address, package_id) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)";
                $order_stmt = $conn->prepare($order_sql);
                $order_stmt->bind_param("isdsssssi", $user_id, $customer_name, $total_amount, $payment_status, $payment_method, $pickup_date, $pickup_time, $pickup_address, $package_id);
            } else {
                $order_sql = "INSERT INTO orders (user_id, customer_name, total_amount, status, payment_status, payment_method, pickup_date, pickup_time, pickup_address) VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?)";
                $order_stmt = $conn->prepare($order_sql);
                $order_stmt->bind_param("isdsssss", $user_id, $customer_name, $total_amount, $payment_status, $payment_method, $pickup_date, $pickup_time, $pickup_address);
            }
            
            if (!$order_stmt->execute()) throw new Exception("Error creating order: " . $order_stmt->error);
            $order_id = $conn->insert_id;
            
            // Insert Order Items
            $has_valid_items = false;
            foreach ($_POST['quantity'] as $service_id => $quantity) {
                $quantity = intval($quantity);
                if ($quantity > 0) {
                    $price_sql = "SELECT price FROM services WHERE id = ?";
                    $price_stmt = $conn->prepare($price_sql);
                    $price_stmt->bind_param("i", $service_id);
                    $price_stmt->execute();
                    $price_result = $price_stmt->get_result();
                    if ($price_result->num_rows === 0) throw new Exception("Service not found");
                    
                    $service_price = $price_result->fetch_assoc()['price'];
                    $item_sql = "INSERT INTO order_items (order_id, service_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $item_stmt = $conn->prepare($item_sql);
                    $item_stmt->bind_param("iiid", $order_id, $service_id, $quantity, $service_price);
                    if (!$item_stmt->execute()) throw new Exception("Error adding item");
                    $has_valid_items = true;
                }
            }
            if (!$has_valid_items) throw new Exception("No items selected");
            
            $conn->commit();
            // --- LOG AKTIVITAS ---
            logActivity($conn, $_SESSION['user_id'], 'New Order', 'Membuat pesanan baru #' . $order_id . ' atas nama ' . $customer_name);
            // ---------------------
            $_SESSION['message'] = "Pesanan berhasil dibuat! Order ID: " . $order_id;
            $_SESSION['message_type'] = "success";
            header("Location: kasir/order_details.php?id=" . $order_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<div class="container my-5">
    <h1 class="mb-4">Place an Order</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (count($services) > 0): ?>
        <form method="post" action="" id="orderForm">
            <?php if ($package): ?>
                <div class="alert alert-success mb-4">
                    <h5><i class="fas fa-tag me-2"></i> <?php echo $package['name']; ?> Selected</h5>
                    <p class="mb-0"><?php echo $package['description']; ?></p>
                    <p class="mb-0 mt-2"><strong>Package Price:</strong> Rp. <?php echo number_format($package['price'], 0, ',', '.'); ?></p>
                    <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                    <input type="hidden" name="package_price" value="<?php echo $package['price']; ?>">
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Data Pelanggan</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Nama Pelanggan *</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required placeholder="Masukkan nama pelanggan..." autofocus>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Select Services</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Price</th>
                                            <th style="width: 150px;">Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($services as $service): ?>
                                            <tr class="service-item">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <img src="assets/images/services/<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded">
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo $service['name']; ?></h6>
                                                            <small class="text-muted"><?php echo $service['description']; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="service-price" data-price="<?php echo $service['price']; ?>">
                                                    Rp. <?php echo number_format($service['price'], 0, ',', '.'); ?>
                                                    <br><small class="text-muted">/ <?php echo isset($service['unit']) ? $service['unit'] : 'kg'; ?></small>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary" onclick="this.nextElementSibling.value = Math.max(0, parseInt(this.nextElementSibling.value) - 1); updateTotalsNow();">-</button>
                                                        <input type="number" name="quantity[<?php echo $service['id']; ?>]" class="form-control text-center" value="0" min="0" onchange="updateTotalsNow()">
                                                        <button type="button" class="btn btn-outline-secondary" onclick="this.previousElementSibling.value = parseInt(this.previousElementSibling.value) + 1; updateTotalsNow();">+</button>
                                                        <span class="input-group-text bg-light text-secondary"><?php echo isset($service['unit']) ? $service['unit'] : 'kg'; ?></span>
                                                    </div>
                                                </td>
                                                <td class="item-total fw-bold">Rp. 0</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Pickup Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="pickup_date" class="form-label">Pickup Date *</label>
                                    <input type="date" class="form-control" id="pickup_date" name="pickup_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="pickup_time" class="form-label">Pickup Time *</label>
                                    <input type="time" class="form-control" id="pickup_time" name="pickup_time" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="pickup_address" class="form-label">Pickup Address *</label>
                                <textarea class="form-control" id="pickup_address" name="pickup_address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Metode Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="cash" checked>
                                <label class="form-check-label" for="payment_cash">
                                    <i class="fas fa-money-bill-wave me-2"></i> Cash (Bayar di Tempat)
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_online" value="online">
                                <label class="form-check-label" for="payment_online">
                                    <i class="fas fa-qrcode me-2"></i> Online (QRIS / Transfer)
                                </label>
                            </div>
                            
                            <div id="onlinePaymentDetails" class="d-none p-3 border rounded bg-light">
                                <h6 class="fw-bold mb-3">Informasi Pembayaran:</h6>
                                
                                <div class="row align-items-center">
                                    <div class="col-md-4 text-center mb-3 mb-md-0">
                                        <img src="assets/images/qris.jpeg" alt="Scan QRIS" class="img-fluid border rounded" style="max-height: 150px;">
                                        <p class="small text-muted mt-1">Scan QRIS (Dana/Gopay/Shopee)</p>
                                    </div>
                                    <div class="col-md-8">
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><strong>BCA:</strong> 123-456-7890 (A.n Barokah Laundry)</li>
                                            <li class="mb-2"><strong>Mandiri:</strong> 987-000-1234 (A.n Barokah Laundry)</li>
                                            <li class="mb-3"><strong>Dana/OVO:</strong> 0812-3456-7890</li>
                                        </ul>
                                        
                                        <div class="alert alert-warning small mb-0">
                                            <i class="fas fa-info-circle me-1"></i> 
                                            Silakan lakukan pembayaran. Kasir akan memverifikasi pembayaran Anda secara manual.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 20px; z-index: 100;">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal:</span>
                                <span id="orderSubtotal">Rp. 0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3 fw-bold">
                                <span>Total:</span>
                                <span id="orderTotal">Rp. 0</span>
                            </div>
                            <input type="hidden" name="total_amount" id="totalAmount" value="0">
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-danger btn-lg">Place Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="alert alert-info">
            <p>No services available at the moment. Please check back later.</p>
            <a href="services.php" class="btn btn-danger mt-3">View All Services</a>
        </div>
    <?php endif; ?>
</div>

<script>
function updateTotalsNow() {
    let subtotal = 0;
    
    const packagePriceElement = document.querySelector('input[name="package_price"]');
    const isPackage = packagePriceElement !== null;
    
    if (isPackage) {
        subtotal = parseFloat(packagePriceElement.value);
        document.querySelectorAll('.service-item').forEach(item => {
            const price = parseFloat(item.querySelector('.service-price').dataset.price);
            const input = item.querySelector('input[type="number"]');
            const quantity = parseInt(input.value) || 0;
            const itemTotal = price * quantity;
            item.querySelector('.item-total').textContent = 'Rp. ' + itemTotal.toLocaleString('id-ID');
        });
    } else {
        document.querySelectorAll('.service-item').forEach(item => {
            const price = parseFloat(item.querySelector('.service-price').dataset.price);
            const input = item.querySelector('input[type="number"]');
            const quantity = parseInt(input.value) || 0;
            const itemTotal = price * quantity;
            item.querySelector('.item-total').textContent = 'Rp. ' + itemTotal.toLocaleString('id-ID');
            subtotal += itemTotal;
        });
    }
    
    const total = subtotal;
    document.getElementById('orderSubtotal').textContent = 'Rp. ' + subtotal.toLocaleString('id-ID');
    document.getElementById('orderTotal').textContent = 'Rp. ' + total.toLocaleString('id-ID');
    document.getElementById('totalAmount').value = total;
}

document.addEventListener('DOMContentLoaded', function() {
    updateTotalsNow();
    
    // Logic Toggle Payment Method (Show/Hide Info Only)
    const onlineDetails = document.getElementById('onlinePaymentDetails');
    
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'online') {
                onlineDetails.classList.remove('d-none');
            } else {
                onlineDetails.classList.add('d-none');
            }
        });
    });
    
    const today = new Date();
    const dd = String(today.getDate()).padStart(2, '0');
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const yyyy = today.getFullYear();
    const minDate = yyyy + '-' + mm + '-' + dd;
    const pickupDateInput = document.getElementById('pickup_date');
    if (pickupDateInput) {
        pickupDateInput.min = minDate;
    }
});
</script>

<?php include_once 'includes/footer.php'; ?>