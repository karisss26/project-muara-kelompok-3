<?php
$page_title = "Reset Password";
include_once 'includes/header.php';

// Redirect jika user "nyasar" ke sini padahal sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: customer/index.php");
    }
    exit;
}

$error = '';
$success = '';
$step = 1; // Default langkah pertama (Input Username)
$found_username = '';

// PROSES FORM
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- LOGIKA TAHAP 1: CEK USERNAME ---
    if (isset($_POST['check_username'])) {
        $username = sanitize_input($_POST['username']);

        if (empty($username)) {
            $error = "Masukkan username terlebih dahulu.";
        } else {
            // Cek apakah username ada di database
            $sql = "SELECT id, name FROM users WHERE name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                // Username ketemu! Lanjut ke Tahap 2
                $step = 2;
                $found_username = $username;
            } else {
                $error = "Username tidak ditemukan.";
            }
        }
    }
    
    // --- LOGIKA TAHAP 2: SIMPAN PASSWORD BARU ---
    elseif (isset($_POST['save_password'])) {
        $username = sanitize_input($_POST['username']); // Ambil dari hidden input
        $pass_new = $_POST['new_password'];
        $pass_confirm = $_POST['confirm_password'];

        // Validasi Password
        if (empty($pass_new) || empty($pass_confirm)) {
            $error = "Password tidak boleh kosong.";
            $step = 2; // Tetap di tahap 2
            $found_username = $username;
        } elseif ($pass_new !== $pass_confirm) {
            $error = "Konfirmasi password tidak cocok.";
            $step = 2; // Tetap di tahap 2
            $found_username = $username;
        } else {
            // Hash Password Baru
            $hashed_password = password_hash($pass_new, PASSWORD_DEFAULT);

            // Update Database
            $sql = "UPDATE users SET password = ? WHERE name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $hashed_password, $username);
            
            if ($stmt->execute()) {
                $success = "Password berhasil diubah! Silakan login dengan password baru.";
                $step = 3; // Tahap selesai
            } else {
                $error = "Terjadi kesalahan sistem.";
            }
        }
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="fas fa-lock me-2"></i> Reset Password</h4>
                </div>
                <div class="card-body p-4">

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success text-center">
                            <h4><i class="fas fa-check-circle"></i> Berhasil!</h4>
                            <p><?php echo $success; ?></p>
                            <a href="login.php" class="btn btn-danger mt-2">Login Sekarang</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($step == 1 && empty($success)): ?>
                        <p class="text-muted">Masukkan username akun Anda untuk mencari data.</p>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control" name="username" required autofocus>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="check_username" class="btn btn-danger">Cari Akun</button>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="login.php" class="text-decoration-none text-muted">Batal</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($step == 2 && empty($success)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-user"></i> Akun ditemukan: <strong><?php echo htmlspecialchars($found_username); ?></strong>
                        </div>
                        <form method="post" action="">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($found_username); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Password Baru</label>
                                <input type="password" class="form-control" name="new_password" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Konfirmasi Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="save_password" class="btn btn-success">Simpan Password Baru</button>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
            <div class="text-center mt-3 text-muted">
                &copy; <?php echo date('Y'); ?> Barokah Laundry System
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>