<?php include('includes/header.php'); ?>

<!-- BANNER -->
<section class="position-relative" style="height: 400px;">
    <img src="assets/images/banner.jpg" alt="Sushi Banner" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0" style="opacity: 0.4;">
    <div class="position-absolute top-50 start-50 translate-middle text-center text-white">
        <h1 class="display-4 fw-bold">Selamat Datang Di Sushi On</h1>
        <p class="lead">Memanjakan lidah Anda dengan Sushi Kami.</p>
    </div>
</section>

<!-- PROMOTIONS -->
<section class="py-5 bg-dark text-white">
    <div class="container">
        <h2 class="mb-4 text-center">Promosi Terakhir</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm bg-dark text-white border-0">
                    <div class="overflow-hidden">
                        <img src="assets/images/sushi_prom.jpg" class="card-img-top img-fluid promo-hover" alt="Promo 1">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Penawaran Sushi Super</h5>
                        <p class="card-text">Nikmati diskon hingga 50% untuk item tertentu.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm bg-dark text-white border-0">
                    <div class="overflow-hidden">
                        <img src="assets/images/ramen.jpg" class="card-img-top img-fluid promo-hover" alt="Promo 2">
                    </div>
                    <div class="card-body text-center">
                        <h5 class="card-title">Top Ramen Mingguan</h5>
                        <p class="card-text">Beli 1 Gratis 1 untuk semua hidangan ramen.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAVORIT KAMI -->
<section class="py-5 bg-dark text-white">
    <div class="container text-center">
        <h2 class="mb-4 text-danger">Favorit Kami</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card bg-dark text-white border-0 shadow-sm">
                    <img src="assets/images/salmon_nigiri.jpg" class="card-img-top" alt="Salmon Nigiri">
                    <div class="card-body">
                        <p class="card-text fw-bold text-center">Salmon Nigiri</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card bg-dark text-white border-0 shadow-sm">
                    <img src="assets/images/unagi_roll.jpg" class="card-img-top" alt="Unagi Roll">
                    <div class="card-body">
                        <p class="card-text fw-bold text-center">Unagi Roll</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card bg-dark text-white border-0 shadow-sm">
                    <img src="assets/images/tempura_maki.jpg" class="card-img-top" alt="Tempura Maki">
                    <div class="card-body">
                        <p class="card-text fw-bold text-center">Tempura Maki</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Efek hover untuk gambar promo */
.promo-hover {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 10px;
}

.promo-hover:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 20px rgba(255, 255, 255, 0.2);
}
</style>
<?php include('includes/footer.php'); ?>