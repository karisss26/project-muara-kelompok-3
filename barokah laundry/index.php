<?php
$page_title = "Home";
include_once 'includes/header.php';

// --- BAGIAN OOP (Definisi Class) ---

class ServiceManager {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getFeaturedServices($limit = 3) {
        $services = [];
        $sql = "SELECT * FROM services WHERE active = 1 LIMIT ?";
        
        // Menggunakan Prepared Statement agar lebih aman (Best Practice OOP)
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
        }
        return $services;
    }
}

class TestimonialManager {
    public function getAllTestimonials() {
        return [
            [
                'name' => 'Ashan Perera',
                'comment' => 'Excellent service! My clothes have never been this clean. Will use again.',
                'rating' => 5
            ],
            [
                'name' => 'Nadeeka Fernando',
                'comment' => 'Very fast pickup and delivery. The quality of cleaning is top-notch.',
                'rating' => 4
            ],
            [
                'name' => 'Chaminda Jayasinghe',
                'comment' => 'Very professional service. The online ordering system is super easy!',
                'rating' => 5
            ],
            [
                'name' => 'Dilani Silva',
                'comment' => 'Affordable prices and friendly staff. Highly recommended!',
                'rating' => 5
            ],
            [
                'name' => 'Ruwan Gunasekara',
                'comment' => 'Quick turnaround and my clothes smell amazing.',
                'rating' => 4
            ],
            [
                'name' => 'Sajith Weerasinghe',
                'comment' => 'Convenient and reliable. I appreciate the attention to detail.',
                'rating' => 4
            ]
        ];
    }
}

// --- INISIALISASI OBJEK & PENGAMBILAN DATA ---

// 1. Siapkan data Services
// Asumsi variabel $conn tersedia dari 'includes/header.php'
$serviceManager = new ServiceManager($conn);
$services = $serviceManager->getFeaturedServices(3);

// 2. Siapkan data Testimonials
$testimonialManager = new TestimonialManager();
$dummyTestimonials = $testimonialManager->getAllTestimonials();

?>

<section class="hero">
    <div class="container">
        <h1>Welcome to Barokah Laundry</h1>
        <p>Solusi untuk semua kebutuhan laundry mu. Kami menawarkan jasa profesional untuk cuci biasa, cuci kering, dan setrika dengan harga terjangkau.</p>
        <a href="services.php" class="btn btn-danger btn-lg">View Our Services</a>
    </div>
</section>

<section class="container py-5">
    <h2 class="text-center mb-4 text-dark">Layanan Kami</h2>
    <div class="row">
        <?php foreach ($services as $service): ?>
            <div class="col-md-4">
                <div class="card service-card h-100"> 
                    <img src="assets/images/services/<?php echo htmlspecialchars($service['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($service['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title text-dark"><?php echo htmlspecialchars($service['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($service['description']); ?></p>
                        <p class="card-text text-danger fw-bold">Rp <?php echo number_format($service['price'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
        <a href="services.php" class="btn btn-outline-danger">Lihat Semua Layanan</a>
    </div>
</section>

<section class="bg-light py-5 mb-5">
    <div class="container">
        <h2 class="text-center mb-4 text-dark">Apa yang dikatakan pelanggan kami</h2>
        <div class="row">
            <?php
            // Looping data yang sudah diambil dari Class TestimonialManager
            foreach ($dummyTestimonials as $testimonial) {
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <?php
                                // Display star rating
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $testimonial['rating']) {
                                        // text-warning dipertahankan karena warnanya cocok dengan pastel/warning
                                        echo '<i class="fas fa-star text-warning"></i>'; 
                                    } else {
                                        // Menggunakan warna abu-abu lembut untuk bintang kosong
                                        echo '<i class="far fa-star text-muted"></i>'; 
                                    }
                                }
                                ?>
                            </div>
                            <p class="card-text text-dark">"<?php echo htmlspecialchars($testimonial['comment']); ?>"</p>
                            <p class="card-text text-end fw-bold text-dark">- <?php echo htmlspecialchars($testimonial['name']); ?></p>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>