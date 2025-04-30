
<style>
    .order-section {
        background-color: var(--secondary-color);
        color: var(--text-color);
        padding: 60px 20px;
        min-height: 100vh;
    }

    .order-section h1 {
        color: var(--primary-color);
        font-weight: bold;
        margin-bottom: 40px;
    }

    .form-label {
        color: var(--accent-color);
        font-weight: 500;
    }

    .form-control {
        background-color: #1e1e1e;
        border: 1px solid var(--accent-color);
        color: var(--text-color);
    }

    .form-control:focus {
        background-color: #1e1e1e;
        color: var(--text-color);
        border-color: var(--primary-color);
        box-shadow: none;
    }

    .btn-danger {
        background-color: var(--primary-color);
        border: none;
        padding: 10px 30px;
        font-weight: bold;
        border-radius: 30px;
    }

    .btn-danger:hover {
        background-color: #a91d1d;
    }

    /*styling menu sushi*/
    .menu-gallery {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        margin-top: 30px;
    }

    .menu-item {
        background-color: #1e1e1e;
        border-radius: 12px;
        overflow: hidden;
        text-align: center;
        width: 180px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        transition: transform 0.3s;
    }

    .menu-item:hover {
        transform: scale(1.05);
    }

    .menu-item img {
        width: 100%;
        height: auto;
        display: block;
    }

    .menu-item h3 {
        color: white;
        margin: 10px 0;
        font-size: 1rem;
    }
</style>

<section class="order-section container">
    <h1 class="text-center">Order Your Sushi!</h1>

    <div class="menu-gallery">
        <div class="menu-item">
            <img src="assets/images/salmon_nigiri.jpg" alt="Salmon Nigiri">
            <h3>Salmon Nigiri</h3>
        </div>
        <div class="menu-item">
            <img src="assets/images/unagi_roll.jpg" alt="Unagi Roll">
            <h3>Unagi Roll</h3>
        </div>
        <div class="menu-item">
            <img src="assets/images/tempura_maki.jpg" alt="Tempura Maki">
            <h3>Tempura Maki</h3>
        </div>
        <div class="menu-item">
            <img src="assets/images/spicy_tuna_roll.jpg" alt="Spicy Tuna Roll">
            <h3>Spicy Tuna Roll</h3>
        </div>
        <div class="menu-item">
            <img src="assets/images/dragon_roll.jpg" alt="Dragon Roll">
            <h3>Dragon Roll</h3>
        </div>
    </div>

    <form action="process_order.php" method="POST" class="row g-4 mt-5">
        <div class="col-md-6">
            <label for="name" class="form-label">Nama</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="col-12">
            <label for="order" class="form-label">Detail Pesanan</label>
            <textarea class="form-control" id="order" name="order_details" rows="5" required></textarea>
        </div>
        <div class="col-12 text-center">
            <button type="submit" class="btn btn-danger">Kirim Pesanan</button>
        </div>
    </form>
</section>