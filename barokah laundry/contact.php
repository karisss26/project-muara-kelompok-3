<?php
$page_title = "Contact Us & Feedback";
include_once 'includes/header.php';

$message = '';
$message_type = '';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
    
    // 1. Ambil data dari form
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $rating = (int)$_POST['rating']; // Pastikan jadi angka
    $subject = sanitize_input($_POST['subject']);
    $content = sanitize_input($_POST['message']);
    
    // 2. Validasi
    if (empty($name) || empty($email) || empty($subject) || empty($content) || empty($rating)) {
        $message = 'Mohon lengkapi semua kolom termasuk rating!';
        $message_type = 'danger';
    } else {
        
        // 3. Gabungkan Subject & Pesan agar tersimpan rapi di kolom komentar feedback
        $full_comment = "[Subject: $subject] \n$content";

        // 4. INSERT KE TABEL FEEDBACK (Supaya muncul di Dashboard Admin)
        // ⚠️ CATATAN: Sesuaikan nama kolom dengan database kamu!
        // Asumsi kolom: customer_name, email, rating, comment, created_at
        
        $sql = "INSERT INTO feedbacks (customer_name, email, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
        
        if ($stmt = $conn->prepare($sql)) {
            // ssis = string, string, integer, string
            $stmt->bind_param("ssis", $name, $email, $rating, $full_comment);
            
            if ($stmt->execute()) {
                $message = 'Terima kasih! Feedback dan pesan Anda telah terkirim.';
                $message_type = 'success';
                
                // Kosongkan form setelah sukses
                unset($name, $email, $rating, $subject, $content);
            } else {
                $message = 'Gagal menyimpan: ' . $conn->error;
                $message_type = 'danger';
            }
            $stmt->close();
        } else {
            $message = 'Database Error: ' . $conn->error;
            $message_type = 'danger';
        }
    }
}
?>

<div class="bg-light py-5 mb-4">
    <div class="container">
        <h1 class="text-center text-dark">Contact & Feedback</h1>
        <p class="text-center lead text-muted">Kirim pesan dan beri kami rating!</p>
    </div>
</div>

<div class="container my-5">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <h2 class="text-primary-pastel-accent">Kirim Masukan</h2>
            <p class="mb-4 text-muted">Pendapat Anda sangat berarti bagi kami.</p>
            
            <form method="post" action="" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>

                <div class="mb-3">
                    <label for="rating" class="form-label fw-bold text-danger">Beri Kami Rating</label>
                    <select class="form-select" id="rating" name="rating" required>
                        <option value="" selected disabled>-- Pilih Bintang --</option>
                        <option value="5" <?php echo (isset($rating) && $rating==5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ - Sangat Puas</option>
                        <option value="4" <?php echo (isset($rating) && $rating==4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ - Puas</option>
                        <option value="3" <?php echo (isset($rating) && $rating==3) ? 'selected' : ''; ?>>⭐⭐⭐ - Cukup</option>
                        <option value="2" <?php echo (isset($rating) && $rating==2) ? 'selected' : ''; ?>>⭐⭐ - Kurang</option>
                        <option value="1" <?php echo (isset($rating) && $rating==1) ? 'selected' : ''; ?>>⭐ - Sangat Kecewa</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="subject" class="form-label">Subjek</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="message" class="form-label">Pesan / Komentar</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                </div>
                
                <div class="d-grid">
                    <button type="submit" name="contact_submit" class="btn btn-danger btn-lg">Kirim Feedback</button>
                </div>
            </form>
        </div>
        
        <div class="col-lg-6">
            <h2 class="text-primary-pastel-accent ms-lg-4">Informasi Kontak</h2>
            <div class="ms-lg-4">
                <p class="mb-4 text-muted">Hubungi kami di kontak dibawah ini</p>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-dark"><i class="fas fa-map-marker-alt text-danger me-2"></i> Alamat</h5>
                        <p class="card-text text-muted">Kp. Ciloa, RT.06, RW.03, Desa padaasih, Kec. Cibogo</p>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-dark"><i class="fas fa-phone text-danger me-2"></i> Telepon</h5>
                        <p class="card-text text-muted"> +6285182591136</p>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-dark"><i class="fas fa-envelope text-danger me-2"></i> Email</h5>
                        <p class="card-text text-muted"> barokahlaundry@gmail.com</p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-dark"><i class="fas fa-clock text-danger me-2"></i> Jam Buka</h5>
                        <p class="card-text text-muted">Senin - Minggu: 8:00 AM - 9:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <div class="ratio ratio-16x9 border border-3 border-danger shadow-sm"> 
                <iframe src="https://maps.google.com/maps?q=Kp.+Ciloa,+RT.06,+RW.03,+Desa+padaasih,+Kec.+Cibogo,+Subang&t=&z=14&ie=UTF8&iwloc=&output=embed"
                    width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>