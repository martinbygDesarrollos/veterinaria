<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../src/utils/whatsapp.php';
require_once '../src/controladores/ctr_whatsapp.php';


return function (App $app) {
    $container = $app->getContainer();

    //---------------------------- CONTROLADORES Y CLASES ------------------------------------------------------
    $whatsappClass = new whatsapp();
    $whatsappController= new ctr_whatsapp();
    $userController = new ctr_usuarios();


    $app->post('/whatsappGetNewQr', function(Request $request, Response $response) use ($userController, $whatsappClass){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){

            return $whatsappClass->nuevoQr();
        }
        else return json_encode($responseSession);
    });


    $app->post('/whatsappVerifyStatus', function(Request $request, Response $response) use ($userController, $whatsappController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){

            return json_encode($whatsappController->verifyStatus());
        }
        else return json_encode($responseSession);
    });



    $app->post('/getAllWhatsappClientByType', function(Request $request, Response $response) use ($userController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $type = $data['type'];
            return json_encode($userController->getAllWhatsappClientByType($type));
        }
        else return json_encode($responseSession);
    });



    $app->post('/enviarWhatsapp', function(Request $request, Response $response) use ($userController, $whatsappClass){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){

            $data = $request->getParams();
            $content = $data['message'];
            $phone = $data['to'];

            $path = 'message/txt';

            return json_encode($whatsappClass->enviarWhatsapp($path, $content, $phone));
        }
        else return json_encode($responseSession);
    });


}
?>