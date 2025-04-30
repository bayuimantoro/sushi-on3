<section class="py-5" style="background-color: #121212; min-height: 100vh;">
    <div class="container text-white">
        <h1 class="text-center mb-5" style="color: #c62828; font-weight: bold;">Hubungi Kami</h1>

        <form action="#" method="POST" class="row g-4">
            <!--kolom nama-->
            <div class="col-md-6">
                <label for="name" class="form-label">Nama:</label>
                <input type="text" id="name" name="name" required class="form-control bg-dark text-white border-0">
            </div>

            <!--kolom email-->
            <div class="col-md-6">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" required class="form-control bg-dark text-white border-0">
            </div>

            <!--kolom pesan-->
            <div class="col-12">
                <label for="message" class="form-label">Pesan:</label>
                <textarea id="message" name="message" rows="5" required class="form-control bg-dark text-white border-0"></textarea>
            </div>

            <!--tombol kirim-->
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-danger px-5 py-2 rounded-pill">Kirim</button>
            </div>
        </form>
    </div>
</section>