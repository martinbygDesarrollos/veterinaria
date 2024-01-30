<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../src/controladores/ctr_historiales.php';
require_once '../src/controladores/ctr_mascotas.php';
require_once '../src/controladores/ctr_usuarios.php';

return function (App $app) {
	$container = $app->getContainer();
	$historialesController = new ctr_historiales();
	$userController = new ctr_usuarios();
	$petController = new ctr_mascotas();

    $fpdf = new Pdf();
    $utils = new Utils();


	// $app->post('/prueba', function(Request $request, Response $response){
	// 	$responseSession = ctr_usuarios::validateSession();
	// 	if($responseSession->result == 2){
	// 		return json_encode(ctr_historiales::executeMigrateDB($responseSession->session));
	// 	}else return json_encode($responseSession);
	// });

	/*$app->get('/eliminar', function(Request $request, Response $response){
	$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
	 		return json_encode(ctr_historiales::eliminarClientesBasura($responseSession->session['USUARIO']));
	 	}else return json_encode($responseSession);
	});*/

    //-------------------------- VISTAS ------------------------------------------

	$app->get('/settings', function($request, $response, $args) use ($container){
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
			return $this->view->render($response, "settings.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("settings");

	$app->get('/historialUsuario', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "historialUsuario.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("HistorialUsuario");

    //-----------------------------------------------------------------------------

    //--------------------------------POST-----------------------------------------

	$app->post('/getListHistorialSocio', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$lastId = $data['lastId'];
			$idSocio = $data['idSocio'];
			return json_encode(ctr_historiales::getListHistorialSocio($lastId, $idSocio));
		}else return json_encode($responseSession);
	});

	$app->post('/crearHistorialSocio', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$idSocio = $data['idSocio'];
			$idMascota = $data['idMascota'];
			$fecha = $data['fecha'];
			$asunto = $data['asunto'];
			$importe  = $data['importe'];
			$observaciones = $data['observaciones'];

			return json_encode(ctr_historiales::crearHistorialSocio($idSocio, $idMascota, $fecha, $asunto, $importe, $observaciones));
		}else return json_encode($responseSession);
	});

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
			$hora = $data['hora'];
			$motivoConsulta = $data['motivoConsulta'];
			$diagnostico = $data['diagnostico'];
			$observaciones = $data['observaciones'];
			$peso = $data['peso'];
			$temperatura = $data['temperatura'];
			$fc = $data['fc'];
			$fr = $data['fr'];
			$tllc = $data['tllc'];

			return json_encode(ctr_historiales::agregarHistoriaClinica($idMascota, $fecha, $hora, $motivoConsulta, $diagnostico, $observaciones, $peso, $temperatura, $fc, $fr, $tllc));
		}else return json_encode($responseSession);
	});

	$app->post('/modificarHistoriaClinica', function(Request $request, Response $response){
		$responseSession = ctr_usuarios::validateSession();
		if($responseSession->result == 2){
			$data = $request->getParams();
			$idHistoriaClinica = $data['idHistoriaClinica'];
			$fecha = $data['fecha'];
			$hora = $data['hora'];
			$motivoConsulta = $data['motivoConsulta'];
			$diagnostico = $data['diagnostico'];
			$observaciones = $data['observaciones'];
			$peso = $data['peso'];
			$temperatura = $data['temperatura'];
			$fc = $data['fc'];
			$fr = $data['fr'];
			$tllc = $data['tllc'];
			return json_encode(ctr_historiales::modificarHistoriaClinica($idHistoriaClinica, $fecha, $hora, $motivoConsulta, $diagnostico, $observaciones, $peso, $temperatura, $fc, $fr, $tllc));
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

    //-----------------------------------------------------------------------------




    $app->post('/getAllIdListHistory', function(Request $request, Response $response) use ($userController, $historialesController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data["idMascota"];

            $response = $historialesController->getAllIdListHistory($idMascota);
            return json_encode($response);
        }else return json_encode($responseSession);
    });


    $app->post('/downloadHistory', function(Request $request, Response $response) use ($userController, $historialesController, $utils, $fpdf){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $idMascota = $request->getParams()["idMascota"];
            $desde = $request->getParams()["desde"];
            $hasta = $request->getParams()["hasta"];

            $utils->clearCalendarPdfDir();
            $dataHistory = $historialesController->getHistoryDocument($idMascota, $desde, $hasta);
            if ($dataHistory->result === 2){

                return json_encode($fpdf->petHistoryDocument($dataHistory->listResult));

            }else{
                return json_encode($dataHistory);
            }
        }else return json_encode($responseSession);
    });


    $app->post('/downloadMedicine', function(Request $request, Response $response) use ($userController, $petController, $utils, $fpdf){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $idMascota = $request->getParams()["idMascota"];

            $utils->clearCalendarPdfDir();
            $dataMedicine = $petController->getMedicineToDocument($idMascota);
            if ($dataMedicine->result === 2){

                return json_encode($fpdf->petMedicineDocument($dataMedicine->listResult));

            }else{
                return json_encode($dataMedicine);
            }
        }else return json_encode($responseSession);
    });

}
?>