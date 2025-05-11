<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sushi On</title>

    <!--bootstrap css-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!--font poppins-->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #c62828;
            --secondary-color: #121212;
            --text-color: #ffffff;
            --accent-color: #e0e0e0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
        }

        .navbar {
            background-color: var(--secondary-color) !important;
            border-bottom: 1px solid #333;
        }

        .nav-link {
            color: var(--text-color) !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .order-btn {
            background-color: var(--primary-color);
            border-radius: 20px;
            padding: 8px 15px !important;
        }

        .username-display {
            margin-right: 10px;
            font-weight: 600;
            color: var(--accent-color);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php?page=home">
                <img src="assets/images/logo.jpg" alt="Sushi On" height="60">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?page=home">Home</a></li>
                    <a class="nav-link order-btn" href="index.php?page=order">Order</a>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=information">Information</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=locations">Location</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?page=contact">Contact</a></li>

                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="nav-item">
                            <span class="nav-link username-display">Hi, <?= htmlspecialchars($_SESSION['username']) ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="confirmLogout(event)">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="views/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <script>
    function confirmLogout(event) {
        event.preventDefault();
        if (confirm("Apakah Anda yakin ingin logout?")) {
            window.location.href = "views/logout.php";
        }
    }
    </script>

    <main>