<?php

use Slim\App;
use Slim\Http\Response;

require_once '../src/controladores/ctr_mascotas.php';
require_once '../src/controladores/ctr_usuarios.php';

return function (App $app) {

    $routesH = require_once __DIR__ . "/../src/routes_historial.php";
    $routesM = require_once __DIR__ . "/../src/routes_mascotas.php";
    $routesU = require_once __DIR__ . "/../src/routes_usuarios.php";


    $container = $app->getContainer();

    $routesU($app);
    $routesH($app);
    $routesM($app);

    $app->get('/', function ($request, $response, $args) use ($container) {
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $args['hayVencimientos'] = ctr_mascotas::getFechasVacunasVencimiento();
            $args['responseVencimientosSocio'] = ctr_usuarios::getCuotasVencidas(0, null);

            $args['version'] = FECHA_ULTIMO_PUSH;
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("Inicio");
};
