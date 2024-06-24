<?php

use Slim\App;
use Slim\Http\Request;
//use Slim\Http\Response;

require_once '../src/controladores/ctr_historiaArticulos.php';


return function (App $app) {

	$userController = new ctr_usuarios();
    $historiaArticulosController = new ctr_historiaArticulos();

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
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $response = new stdClass();
            $idUser = $responseSession->session['IDENTIFICADOR'];

            $data = $request->getParams();
            $token = $data['token'];
            $myToken = base64_encode(date("Ymd") . "gestcom1213");
		    if(strcmp($token, $myToken) == 0){
                return json_encode($historiaArticulosController->altaArticulos());
            }else{
                $response->result = 1;
                $response->message = "Token invalido.";
                return json_encode($response);
            }
        }else return json_encode($responseSession);
    });

}
?>