<?php
namespace App\Core;

class Router {
    public function dispatch() {
        $controllerName = $_GET['controller'] ?? 'review';
        $actionName = $_GET['action'] ?? 'index';

        $controllerClass = "App\\Controllers\\" . ucfirst($controllerName) . "Controller";

        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
                return;
            }
        }

        http_response_code(404);
        echo "404 - Trang không tồn tại!";
    }
}