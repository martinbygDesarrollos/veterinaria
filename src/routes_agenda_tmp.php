<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;


return function (App $app) {

	// $app->post('/prueba', function(Request $request, Response $response){
	// 	$responseSession = ctr_usuarios::validateSession();
	// 	if($responseSession->result == 2){
	// 		return json_encode(ctr_historiales::executeMigrateDB($responseSession->session));
	// 	}else return json_encode($responseSession);
	// });

	$app->get('/cirugia', function($request, $response, $args){
        $args['version'] = FECHA_ULTIMO_PUSH;
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			$responseGetQuota = ctr_historiales::getMontoCuotas();
			if($responseGetQuota->result == 2)
				$args['cuotas'] = $responseGetQuota->objectResult;

			$responseGetUsers = ctr_usuarios::getUsuarios();
			if($responseGetUsers->result == 2)
				$args['listUsuarios'] = $responseGetUsers->listResult;
			return $this->view->render($response, "cirugias.twig", $args);
		}else return $response->withRedirect('iniciar-sesion');
	})->setName("Cirugias");

	$app->get('/agenda/peluqueria', function($request, $response, $args){
        $args['version'] = FECHA_ULTIMO_PUSH;
        return $response->withRedirect('iniciar-sesion');
	})->setName("Peluqueria");

	$app->get('/agenda/notas', function($request, $response, $args){
        $args['version'] = FECHA_ULTIMO_PUSH;
		return $response->withRedirect('iniciar-sesion');
	})->setName("Notas");

}
?>