<?php

use Slim\App;
use Slim\Http\Response;

require_once '../src/utils/messages.php';

require_once '../src/controladores/ctr_mascotas.php';
require_once '../src/controladores/ctr_usuarios.php';
require_once '../src/controladores/ctr_whatsapp.php';

return function (App $app) {

    $routesH = require_once __DIR__ . "/../src/routes/routes_historial.php";
    $routesM = require_once __DIR__ . "/../src/routes/routes_mascotas.php";
    $routesU = require_once __DIR__ . "/../src/routes/routes_usuarios.php";
    $routesAg = require_once __DIR__ . "/../src/routes/routes_agenda.php";
    $routesArt = require_once __DIR__ . "/../src/routes/routes_articulos.php";
    $routesW = require_once __DIR__ . "/../src/routes/routes_whatsapp.php";


    $container = $app->getContainer();

    $routesU($app);
    $routesH($app);
    $routesM($app);
    $routesAg($app);
    $routesArt($app);
    $routesW($app);

    $app->get('/', function ($request, $response, $args) use ($container) {
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $args['hayVencimientos'] = ctr_mascotas::getFechasVacunasVencimiento();
            $args['responseVencimientosSocio'] = ctr_usuarios::getCuotasVencidas(0, null);

            //verificar session whatsapp
            $whatsappController = new ctr_whatsapp();
            $whatsappController->verifyStatus();
            
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("Inicio");
};
