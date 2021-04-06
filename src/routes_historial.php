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

		// $operacion = $data['operacion'];
		// $observaciones = $data['observaciones'];
		// return json_encode(ctr_historiales::insertHistorialUsuario($operacion, $observaciones));
		return json_encode(ctr_historiales::levantarDB());
		// return json_encode(ctr_historiales::updateCuotaSocio($cuotaUna, $cuotaDos, $cuotaExtra));
		// return json_encode(ctr_historiales::insertarVacunaMascota($id));
		// return json_encode(ctr_usuarios::obtenerBusqueda($idSocio));
		// return json_encode(ctr_usuarios::notificarSocio($idSocio,$idMascota));
		// return json_encode(ctr_usuarios::actualizarEstadosSocios(90));
	});

    //-------------------------- VISTAS ------------------------------------------
	$app->get('/historiaClinica/{idMascota}', function($request, $response, $args) use ($container){

		if (isset($_SESSION['administrador'])) {
			$args['administrador'] = $_SESSION['administrador'];
			$idMascota = $args['idMascota'];
			$args['mascota'] = ctr_mascotas::getMascota($idMascota);
			return $this->view->render($response, "historiaClinica.twig", $args);
		}
	})->setName('historiasClinica');

	$app->get('/settings', function($request, $response, $args) use ($container){
		if (isset($_SESSION['administrador'])) {
			$args['administrador'] = $_SESSION['administrador'];
			if($_SESSION['administrador']->usuario == "admin"){
				$args['usuarios'] = ctr_usuarios::getUsuarios();
			}
			$args['cuotas'] =  ctr_historiales::getMontoCuotas();
			return $this->view->render($response, "settings.twig", $args);
		}
	})->setName("settings");

	$app->get('/historialUsuario', function($request, $response, $args) use ($container){
		if (isset($_SESSION['administrador'])) {
			$args['administrador'] = $_SESSION['administrador'];
			if($_SESSION['administrador']->usuario == 'admin'){
				$args['operaciones'] = ctr_historiales::getHistorialUsuarios();
			}else{
				$args['operaciones'] = ctr_historiales::getHistorialUsuarios();
			}

			return $this->view->render($response, "historialUsuario.twig", $args);
		}
	})->setName("settings");
    //-----------------------------------------------------------------------------

    //--------------------------------POST-----------------------------------------

	$app->post('/getHistoriaClinicaPagina', function(Request $request, Response $response){
		$data = $request->getParams();
		$ultimoID = $data['ultimoID'];
		$idMascota = $data['idMascota'];
		return json_encode(ctr_historiales::getHistoriaClinicaPagina($ultimoID, $idMascota));
	});

	$app->post('/getHistorialUsuariosPagina', function(Request $request, Response $response){
		$data = $request->getParams();
		$ultimoID = $data['ultimoID'];
		return json_encode(ctr_historiales::getHistorialUsuariosPagina($ultimoID));
	});

	$app->post('/insertHistoriaMascota', function(Request $request, Response $response){

		if (isset($_SESSION['administrador'])) {
			$data = $request->getParams();
			$idMascota = $data['idMascota'];
			$motivoConsulta = $data['motivoConsulta'];
			$diagnostico = $data['diagnostico'];
			$observaciones = $data['observaciones'];
			return json_encode(ctr_historiales::insertHistoriaMascota($idMascota, $motivoConsulta, $diagnostico, $observaciones));
		}
	});

	$app->post('/getHistoriaCompleta', function(Request $request, Response $response){

		if (isset($_SESSION['administrador'])) {
			$data = $request->getParams();
			$idHistoria = $data['idHistoria'];
			return json_encode(ctr_historiales::getHistoriaCompleta($idHistoria));
		}
	});

	$app->post('/updateCuotaSocio', function(Request $request, Response $response){

		if (isset($_SESSION['administrador'])) {
			$data = $request->getParams();
			$cuotaUna = $data['cuotaUna'];
			$cuotaDos = $data['cuotaDos'];
			$cuotaExtra = $data['cuotaExtra'];
			return json_encode(ctr_historiales::updateCuotaSocio($cuotaUna, $cuotaDos, $cuotaExtra));
		}
	});

	$app->post('/updatePlazoDeuda', function(Request $request, Response $response){

		if (isset($_SESSION['administrador'])) {
			$data = $request->getParams();
			$plazoDeuda = $data['plazoDeuda'];
			return json_encode(ctr_historiales::updatePlazoDeuda($plazoDeuda));
		}
	});

    //-----------------------------------------------------------------------------

}
?>