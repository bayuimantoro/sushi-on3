<?php

class PageController {
    public function home() {
        include 'views/partials/header.php';
        include 'views/home.php';
        include 'views/partials/footer.php';
    }
    public function about() {
        include 'views/partials/header.php';
        include 'views/about.php';
        include 'views/partials/footer.php';
    }

    public function contact() {
        include 'views/partials/header.php';
        include 'views/contact.php';
        include 'views/partials/footer.php';
    }

    public function menu() {
        include 'views/partials/header.php';
        include 'views/menu.php';
        include 'views/partials/footer.php';
    }

    public function order() {
        include 'views/partials/header.php';
        include 'views/order.php';
        include 'views/partials/footer.php';
    }

    public function locations() {
        include 'views/partials/header.php';
        include 'views/locations.php';
        include 'views/partials/footer.php';
    }

    public function information() {
        include 'views/partials/header.php';
        include 'views/information.php';
        include 'views/partials/footer.php';
    }

    public function loginI() {
        include 'views/partials/header.php';
        include 'views/login.php';
        include 'views/partials/footer.php';
    }
}
?>
