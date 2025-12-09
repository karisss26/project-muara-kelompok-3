<?php
$page_title = "Login";
include_once 'includes/header.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: kasir/index.php");
    }
    exit;
}

$error = '';
$username = ''; // Inisialisasi variabel agar tidak error di HTML value=""

// Proses Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']); 
    $password = $_POST['password'];
    
    // Validasi input kosong
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else { 
        // Cek kredensial user berdasarkan 'name' (username)
        $sql = "SELECT id, name, email, password, user_role FROM users WHERE name = ?"; 
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username); 
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verifikasi Password (Hash)
            if (password_verify($password, $user['password'])) {
                
                // Set Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['user_role'];
                
                // --- LOG AKTIVITAS (Fitur Baru) ---
                // Mencatat bahwa user ini baru saja login
                if (function_exists('logActivity')) {
                    logActivity($conn, $user['id'], 'Login', 'User berhasil login ke sistem');
                }
                // ----------------------------------

                // Redirect sesuai Role
                if ($user['user_role'] == 'admin') {
                    header("Location: admin/index.php");
                } else {
                    // Asumsi role selain admin adalah kasir
                    header("Location: kasir/index.php"); 
                }
                exit;
                
            } else {
                $error = "Invalid username or password";
            }
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i> Login ke Akun</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label> 
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember Me</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">Login</button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <a href="forgot_password.php" class="text-decoration-none">Lupa Password?</a>
                    </div>
                    
                </div>
            </div>
            
            <div class="text-center mt-3 text-muted">
                &copy; <?php echo date('Y'); ?> Barokah Laundry System
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>