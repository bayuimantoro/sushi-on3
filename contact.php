<?php include('includes/header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" integrity="sha384-1g
<section class="py-16 px-5 bg-gray-800 text-gray-200">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-8 text-center">Hubungi Kami</h1>
        <form action="#" method="POST" class="max-w-lg mx-auto">
            <label for="name" class="block mb-2">Nama:</label>
            <input type="text" id="name" name="name" required class="w-full p-3 mb-4 rounded bg-gray-700 text-white border-none">

            <label for="email" class="block mb-2">Email:</label>
            <input type="email" id="email" name="email" required class="w-full p-3 mb-4 rounded bg-gray-700 text-white border-none">

            <label for="message" class="block mb-2">Pesan:</label>
            <textarea id="message" name="message" rows="5" required class="w-full p-3 mb-4 rounded bg-gray-700 text-white border-none"></textarea>

            <button type="submit" class="px-6 py-3 bg-primary text-white rounded font-medium hover:bg-red-700 transition-colors">Kirim</button>
        </form>
    </div>
</section>

<?php include('includes/footer.php'); ?>