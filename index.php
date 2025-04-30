<?php
require_once 'controllers/PageController.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$controller = new PageController();

switch ($page) {
    case 'about':
        $controller->about();
        break;
    case 'contact':
        $controller->contact();
        break;

    case 'menu':
        $controller->menu();
        break;

    case 'order':
        require_once 'controllers/OrderController.php';
        $orderController = new OrderController();
        $orderController->index();
        break;

    case 'locations':
        $controller->locations();
        break;

    case 'information':
        $controller->information();
        break;
        
    case 'home':
    default:
        $controller->home();
        break;
}