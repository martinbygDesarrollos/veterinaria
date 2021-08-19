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

		// return json_encode(ctr_historiales::insertarSociosOriginales());
		// return json_encode(ctr_historiales::insertarMascotasOriginales());
		 // return json_encode(ctr_historiales::insertarMascotasSinSociosOriginales());
		// return json_encode(ctr_historiales::insertarVacunasOriginales());
		// return json_encode(ctr_historiales::insertarHistorialClinicoOriginales());
		// return json_encode(ctr_historiales::insertarEnfermedadesOriginales());
		// return json_encode(ctr_historiales::insertarFechaDeCambioOriginales());
		// return json_encode(ctr_usuarios::actualizarEstadosSocios(1500));
	});

    //-------------------------- VISTAS ------------------------------------------

	$app->get('/settings', function($request, $response, $args) use ($container){
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$args['administrador'] = $sesion;
				if($_SESSION['administrador']->usuario == "admin"){
					$args['usuarios'] = ctr_usuarios::getUsuarios();
				}
				$args['cuotas'] =  ctr_historiales::getMontoCuotas();
				return $this->view->render($response, "settings.twig", $args);
			}
		}
		return $this->view->render($response, "index.twig", $args);
	})->setName("settings");

	$app->get('/historialUsuario', function($request, $response, $args) use ($container){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "historialUsuario.twig", $args);
		}else return $response->withRedirect('iniciar-sesion');
	})->setName("HistorialUsuario");

    //-----------------------------------------------------------------------------

    //--------------------------------POST-----------------------------------------

	$app->post('/getHistorialUsuario', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$lastId = $data['lastId'];
			return json_encode(ctr_historiales::getHistorialUsuario($lastId, $responseSession->session['IDENTIFICADOR']));
		}else return json_encode($responseSession);
	});

	$app->post('/getHistoriaClinicaMascota', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$lastId = $data['lastId'];
			$idMascota = $data['idMascota'];
			return json_encode(ctr_historiales::getHistoriaClinicaMascota($lastId, $idMascota));
		}else return json_encode($responseSession);
	});

	$app->post('/getHistoriaClinicaToShow', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$idHistoriaClinica = $data['idHistoriaClinica'];
			return json_encode(ctr_historiales::getHistoriaClinicaToShow($idHistoriaClinica));
		}else return json_encode($responseSession);
	});

	$app->post('/getHistoriaClinicaToEdit', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$idHistoria = $data['idHistoriaClinica'];
			return json_encode(ctr_historiales::getHistoriaClinicaToEdit($idHistoria));
		}else return json_encode($responseSession);
	});

	$app->post('/agregarHistoriaClinica', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$idMascota = $data['idMascota'];
			$fecha = $data['fecha'];
			$motivoConsulta = $data['motivoConsulta'];
			$diagnostico = $data['diagnostico'];
			$observaciones = $data['observaciones'];
			return json_encode(ctr_historiales::agregarHistoriaClinica($idMascota, $fecha, $motivoConsulta, $diagnostico, $observaciones));
		}else return json_encode($responseSession);
	});

	$app->post('/modificarHistoriaClinica', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$idHistoriaClinica = $data['idHistoriaClinica'];
			$fecha = $data['fecha'];
			$motivoConsulta = $data['motivoConsulta'];
			$diagnostico = $data['diagnostico'];
			$observaciones = $data['observaciones'];
			return json_encode(ctr_historiales::modificarHistoriaClinica($idHistoriaClinica, $fecha, $motivoConsulta, $diagnostico, $observaciones));
		}else return json_encode($responseSession);
	});

	$app->post('/borrarHistoriaClinica', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$idHistoriaClinica = $data['idHistoriaClinica'];
			return json_encode(ctr_historiales::borrarHistoriaClinica($idHistoriaClinica));
		}else return json_encode($responseSession);
	});

	$app->post('/updatePlazoDeuda', function(Request $request, Response $response){
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$data = $request->getParams();
				$plazoDeuda = $data['plazoDeuda'];
				return json_encode(ctr_historiales::updatePlazoDeuda($plazoDeuda));
			}
		}

		$response = new \stdClass();
		$response->retorno = false;
		$response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
		return json_encode($response);
	});

    //-----------------------------------------------------------------------------

}
?>