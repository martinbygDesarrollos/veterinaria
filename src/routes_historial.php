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
	$app->get('/historiaClinica/{idMascota}', function($request, $response, $args) use ($container){
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$args['administrador'] = $sesion;
				$idMascota = $args['idMascota'];
				$args['mascota'] = ctr_mascotas::getMascota($idMascota);
				return $this->view->render($response, "historiaClinica.twig", $args);
			}
		}
		return $this->view->render($response, "index.twig", $args);
	})->setName('historiaClinica');

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
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$args['administrador'] = $sesion;
				return $this->view->render($response, "historialUsuario.twig", $args);
			}
		}
		return $this->view->render($response, "index.twig", $args);
	})->setName("settings");
    //-----------------------------------------------------------------------------

    //--------------------------------POST-----------------------------------------

	$app->post('/getHistoriaClinicaPagina', function(Request $request, Response $response){
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$data = $request->getParams();
				$ultimoID = $data['ultimoID'];
				$idMascota = $data['idMascota'];
				return json_encode(ctr_historiales::getHistoriaClinicaPagina($ultimoID, $idMascota));
			}
		}

		$response = new \stdClass();
		$response->retorno = false;
		$response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
		return json_encode($response);
	});

	$app->post('/getHistorialUsuariosPagina', function(Request $request, Response $response){
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$data = $request->getParams();
				$ultimoID = $data['ultimoID'];
				return json_encode(ctr_historiales::getHistorialUsuariosPagina($ultimoID));
			}
		}

		$response = new \stdClass();
		$response->retorno = false;
		$response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
		return json_encode($response);
	});

	$app->post('/insertHistoriaMascota', function(Request $request, Response $response){
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$data = $request->getParams();
				$idMascota = $data['idMascota'];
				$motivoConsulta = $data['motivoConsulta'];
				$diagnostico = $data['diagnostico'];
				$observaciones = $data['observaciones'];
				return json_encode(ctr_historiales::insertHistoriaMascota($idMascota, $motivoConsulta, $diagnostico, $observaciones));
			}
		}

		$response = new \stdClass();
		$response->retorno = false;
		$response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
		return json_encode($response);
	});

	$app->post('/getHistoriaCompleta', function(Request $request, Response $response){
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$data = $request->getParams();
				$idHistoria = $data['idHistoria'];
				return json_encode(ctr_historiales::getHistoriaCompleta($idHistoria));
			}
		}

		$response = new \stdClass();
		$response->retorno = false;
		$response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
		return json_encode($response);
	});

	$app->post('/updateCuotaSocio', function(Request $request, Response $response){
		if (isset($_SESSION['administrador'])) {
			$sesion = $_SESSION['administrador'];
			$result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
			if($result){
				$data = $request->getParams();
				$cuotaUna = $data['cuotaUna'];
				$cuotaDos = $data['cuotaDos'];
				$cuotaExtra = $data['cuotaExtra'];
				return json_encode(ctr_historiales::updateCuotaSocio($cuotaUna, $cuotaDos, $cuotaExtra));
			}
		}

		$response = new \stdClass();
		$response->retorno = false;
		$response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
		return json_encode($response);
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