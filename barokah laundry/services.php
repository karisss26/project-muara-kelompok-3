<?php
$page_title = "Layanan Barokah Laundry";
include_once 'includes/header.php';

// Fetch all active services
$sql = "SELECT * FROM services WHERE active = 1";
$result = $conn->query($sql);
$services = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
?>

<!-- Services Banner -->
<div class="bg--light-color py-5 mb-4">
    <div class="container">
        <h1 class="text-center">Layanan Barokah Laundry</h1>
        <p class="text-center lead">Temukan berbagai layanan yang disediakan Barokah Laundry</p>
    </div>
</div>

<!-- Services List -->
<div class="container">
    <div class="row">
        <?php if (count($services) > 0): ?>
            <?php foreach ($services as $service): ?>                <div class="col-md-4 mb-4">
                    <div class="card service-card">
                        <img src="assets/images/services/<?php echo $service['image']; ?>" class="card-img-top" alt="<?php echo $service['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $service['name']; ?></h5>
                            <p class="card-text"><?php echo $service['description']; ?></p>
                            <p class="card-text text-danger fw-bold">Rp <?php echo number_format($service['price'], 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <h3>Layanan tidak tersedia pada saat ini.</h3>
                <p>Periksa lagi nanti.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- FAQ Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Pertanyaan yang sering ditanyakan</h2>
    <div class="accordion" id="servicesFAQ">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Berapa lama biasanya proses cuci laundry disini?
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#servicesFAQ">
                <div class="accordion-body">
                    Waktu standar biasanya 48 jam. Jika anda ingin selesai lebih cepat, kami menawarkan layanan express dengan waktu 24 jam selesai.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Metode pembayaran apa yang diterima?
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#servicesFAQ">
                <div class="accordion-body">
                    Kami menerima pembayaran tunai, kartu kredit/debit, dan pembayaran online melalui Dana dan e-wallet lainnya. Anda bisa memilih metode pembayaran yang anda mau pada saat checkout.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    Bagaimana cara anda menangani barang-barang yang halus?
                </button>
            </h2>
            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#servicesFAQ">
                <div class="accordion-body">
                    Barang-barang rapuh seperti sutra, wol, dan bahan spesial lainnya diproses menggunakan layanan cuci kering gentle yang kami sediakan. Kami mengikuti instruksi untuk mencuci dan merawat bahan dari barang-barang rapuh tersebut untuk memastikan barang-barang itu diproses dengan lembut dan benar.
                </div>
            </div>
        </div>

<?php include_once 'includes/footer.php'; ?>