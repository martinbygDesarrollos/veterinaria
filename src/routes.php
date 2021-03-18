<?php

use Slim\App;
use Slim\Http\Response;

return function (App $app) {

    $routesH = require_once __DIR__ . "/../src/routes_historial.php";
    $routesM = require_once __DIR__ . "/../src/routes_mascotas.php";
    $routesU = require_once __DIR__ . "/../src/routes_usuarios.php";


    $container = $app->getContainer();

    $routesU($app);
    $routesH($app);
    $routesM($app);

    $app->get('/', function ($request, $response, $args) use ($container) {
        if (isset($_SESSION['administrador'])) {
            $args['administrador'] = $_SESSION['administrador'];
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("Inicio");
};
