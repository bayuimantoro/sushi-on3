<?php include('includes/header.php'); ?>

<section class="page-section">
    <div class="container">
        <h1>Hubungi Kami</h1>
        <form action="#" method="POST">
            <label for="name">Nama:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Pesan:</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit">Kirim</button>
        </form>
    </div>
</section>

<?php include('includes/footer.php'); ?>
