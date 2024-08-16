<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../src/controladores/ctr_historiaArticulos.php';
require_once '../src/controladores/ctr_facturas.php';


return function (App $app) {

	$userController = new ctr_usuarios();
    $historiaArticulosController = new ctr_historiaArticulos();
    $facturasController = new ctr_facturas();

	/* $app->get('/cirugia', function($request, $response, $args) use ($userController, $calendarController){
        $args['version'] = FECHA_ULTIMO_PUSH;
		$responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "cirugias.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("Cirugias");

 */

	$app->post('/articulos', function(Request $request) use ($userController, $historiaArticulosController){
        
        $response = new stdClass();
        //$idUser = $responseSession->session['IDENTIFICADOR'];

        $data = $request->getParams();
        $token = $data['token'];
        $myToken = base64_encode(date("Ymd") . "gestcom1213");
        if(strcmp($token, $myToken) == 0){

            $file = $data['file'];
            $pathArticulos = PATH_ARCHIVOS."articulos.dbf";
            file_put_contents($pathArticulos, base64_decode($file));
            return json_encode($historiaArticulosController->altaArticulos($pathArticulos));
        }else{
            $response->result = 1;
            $response->message = "Token invalido.";
            return json_encode($response);
        }
    });
    // Se obtienen todas las historiasArticulos que tengan ese idCliente y su tipo, serie y numero sean NULL
	$app->get('/pendientes/{id}', function(Request $request, $response, $args) use ($userController, $historiaArticulosController){
        $response = new stdClass();
        $data = $request->getParams();
        $token = $data['token'];
        $myToken = base64_encode(date("Ymd") . "gestcom1213");
        // var_dump($myToken);
        if(strcmp($token, $myToken) == 0){
            $idClient = $args['id'];
            // file_put_contents($pathArticulos, base64_decode($file));
            return json_encode($historiaArticulosController->getArticulosPendientesByIdClient($idClient));
        }else{
            $response->result = 1;
            $response->message = "Token invalido.";
            return json_encode($response);
        }
    });

    $app->post('/pendientes', function(Request $request) use ($userController, $historiaArticulosController) {
        $response = new stdClass();
        $data = $request->getParsedBody(); // Use getParsedBody to handle JSON payload
        $token = $data['token'];
        $myToken = base64_encode(date("Ymd") . "gestcom1213");
        if (strcmp($token, $myToken) == 0) {
            $facturas = $data['facturas'];
            if (!is_array($facturas)) {
                $response->result = 1;
                $response->message = "Formato invalido";
                return json_encode($response);
            }
            $results = new stdClass();
            $results->exitos = [];
            $results->errores = [];
            $results->result = 1;
            foreach ($facturas as $factura) {
                $ids = $factura['ids']; 
                if (!is_array($ids)) {
                    $response->result = 1;
                    $response->message = "Formato invalido en ids";
                    return json_encode($response);
                }
                $tipo = $factura['tipo'];
                $serie = $factura['serie'];
                $numero = $factura['numero'];
                $resultAux = $historiaArticulosController->updateHistoriaArticulo($ids, $tipo, $serie, $numero);
                $results->exitos = array_merge($results->exitos, $resultAux->exitos);
                $results->errores = array_merge($results->errores, $resultAux->errores);
            }
            //Analizar $result para preparar respuesta
            if(count($results->errores) > 0)
                $results->result = 1;
            else
                $results->result = 2;
            return json_encode($results);
        } else {
            $response->result = 1;
            $response->message = "Token invalido.";
            return json_encode($response);
        }
    });

    $app->post('/facturasPendientes', function(Request $request) use ($userController, $facturasController){
        
        $response = new stdClass();
        $data = $request->getParsedBody(); // Use getParsedBody to handle JSON payload
        $token = $data['token'];
        $myToken = base64_encode(date("Ymd") . "gestcom1213");
        if(strcmp($token, $myToken) == 0){
            $facturas = $data['facturas'];
            if (!is_array($facturas)) {
                $response->result = 1;
                $response->message = "Formato invalido";
                return json_encode($response);
            }
            foreach ($facturas as $factura) {
                if(!isset($factura['tipo']) || !isset($factura['serie']) || !isset($factura['numero']) || !isset($factura['importe']) ){ // Si falta alguno de esos datos devuelvo error de formato
                    $response->result = 1;
                    $response->message = "Formato invalido";
                    return json_encode($response);
                }
            }
            return json_encode($facturasController->updateFacturaspendientes($facturas));
        } else {
            $response->result = 1;
            $response->message = "Token invalido.";
            return json_encode($response);
        }
    });

    $app->post('/searchArticuloByDescOrCodeBar', function(Request $request, Response $response) use ($userController, $historiaArticulosController) {

		$responseSession = $userController->validateSession();
		if($responseSession->result == 2){
            $response = new stdClass();
            $data = $request->getParsedBody();
            $textToSearch = $data["textToSearch"];

            return json_encode($historiaArticulosController->searchArticuloByDescOrCodeBar($textToSearch));

        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    });


    $app->post('/matchArticulosHistoria', function(Request $request, Response $response) use ($userController, $historiaArticulosController) {

		$responseSession = $userController->validateSession();
		if($responseSession->result == 2){
            $response = new stdClass();
            $data = $request->getParsedBody();
            $art = $data["art"];
            $idHist = $data["idHist"];
            $idMascota = $data["idMascota"];

            return json_encode($historiaArticulosController->matchArticulosHistoria($art, $idHist, $idMascota));

        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    });
}
?>