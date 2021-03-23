<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'controladores/ctr_historiales.php';
require_once 'controladores/ctr_mascotas.php';
require_once 'controladores/ctr_usuarios.php';

return function (App $app) {
	$container = $app->getContainer();

	$app->post('/prueba', function(Request $request, Response $response){
		$data = $request->getParams();

		return json_encode(ctr_historiales::levantarDB());
		// return json_encode(ctr_historiales::updateCuotaSocio($cuotaUna, $cuotaDos, $cuotaExtra));
		// return json_encode(ctr_historiales::insertarVacunaMascota($id));
		// return json_encode(ctr_mascotas::getInfoVencimientos());
		// return json_encode(ctr_usuarios::notificarSocio($idSocio,$idMascota));
	});

    //-------------------------- VISTAS ------------------------------------------
	$app->get('/historiaClinica/{idMascota}', function($request, $response, $args) use ($container){
		$sesionActiva = $_SESSION['administrador'];
		if (isset($sesionActiva)) {
			$args['administrador'] = $sesionActiva;
			$idMascota = $args['idMascota'];
			$args['mascota'] = ctr_mascotas::getMascota($idMascota);
			$historial = ctr_historiales::getHistoriasClinica($idMascota);

			if(sizeof($historial) > 0){
				$args['historias'] = $historial;
				return $this->view->render($response, "historiaClinica.twig", $args);
			}
		}
	})->setName('historiasClinica');

	$app->get('/settings', function($request, $response, $args) use ($container){
		$sesionActiva = $_SESSION['administrador'];
		if (isset($sesionActiva)) {
			$args['administrador'] = $sesionActiva;
			// if($sesionActiva == "admin"){
				$args['usuarios'] = ctr_usuarios::getUsuarios();
			// }
			$args['cuotas'] =  ctr_historiales::getMontoCuotas();
			return $this->view->render($response, "settings.twig", $args);
		}
	})->setName("settings");

    //-----------------------------------------------------------------------------

    //--------------------------------POST-----------------------------------------

	$app->post('/insertHistoriaMascota', function(Request $request, Response $response){
		$sesionActiva = $_SESSION['administrador'];
		if (isset($sesionActiva)) {
			$args['administrador'] = $sesionActiva;
			$data = $request->getParams();
			$idMascota = $data['idMascota'];
			$motivoConsulta = $data['motivoConsulta'];
			$diagnostico = $data['diagnostico'];
			$observaciones = $data['observaciones'];

			return json_encode(ctr_historiales::insertHistoriaMascota($idMascota, $motivoConsulta, $diagnostico, $observaciones));
		}
	});

	$app->post('/getHistoriaCompleta', function(Request $request, Response $response){
		$sesionActiva = $_SESSION['administrador'];
		if (isset($sesionActiva)) {
			$args['administrador'] = $sesionActiva;
			$data = $request->getParams();
			$idHistoria = $data['idHistoria'];
			return json_encode(ctr_historiales::getHistoriaCompleta($idHistoria));
		}
	});

	$app->post('/updateCuotaSocio', function(Request $request, Response $response){
		$sesionActiva = $_SESSION['administrador'];
		if (isset($sesionActiva)) {
			$args['administrador'] = $sesionActiva;
			$data = $request->getParams();

			$cuotaUna = $data['cuotaUna'];
			$cuotaDos = $data['cuotaDos'];
			$cuotaExtra = $data['cuotaExtra'];

			return json_encode(ctr_historiales::updateCuotaSocio($cuotaUna, $cuotaDos, $cuotaExtra));
		}
	});


    //-----------------------------------------------------------------------------

}
?>