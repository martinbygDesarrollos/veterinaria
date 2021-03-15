<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'controladores/ctr_historiales.php';
require_once 'controladores/ctr_mascotas.php';

return function (App $app) {
	$container = $app->getContainer();

	$app->post('/prueba', function(Request $request, Response $response){
		$data = $request->getParams();
		$id = $data['id'];
		// return json_encode(ctr_historiales::getHistoriasClinica($id));
		return json_encode(ctr_historiales::levantarDB());
	});
    //-------------------------- VISTAS ------------------------------------------
	$app->get('/historiasClinica/{idMascota}', function($request, $response, $args) use ($container){
		$idMascota = $args['idMascota'];
		$args['info'] = ctr_mascotas::getMascota($idMascota);
		$historial = ctr_historiales::getHistoriasClinica($idMascota);

		if(sizeof($historial) > 0){
			$args['historias'] = $historial;
			return $this->view->render($response, "historiasClinica.twig", $args);
		}else{
			$args['mascotas'] = ctr_mascotas::getMascotas();
			return $this->view->render($response, "mascotas.twig", $args);
		}
	})->setName('historiasClinica');

    //-----------------------------------------------------------------------------

    //--------------------------------POST-----------------------------------------


    //-----------------------------------------------------------------------------

}
?>