<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../src/controladores/ctr_agenda.php';
require_once '../src/controladores/ctr_internado.php';

return function (App $app) {

	$calendarController = new ctr_agenda();
	$userController = new ctr_usuarios();
	$hospitalizedPetController = new ctr_internado();

	$app->get('/cirugia', function($request, $response, $args) use ($userController, $calendarController){
        $args['version'] = FECHA_ULTIMO_PUSH;
		$responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "cirugias.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("Cirugias");

	$app->get('/peluqueria', function($request, $response, $args) use ($userController){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "peluquerias.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("Peluqueria");

	$app->get('/domicilios', function($request, $response, $args) use ($userController){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "domicilios.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("Domicilios");

	$app->get('/calendario', function($request, $response, $args) use ($userController){
        $args['version'] = FECHA_ULTIMO_PUSH;
		$responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "calendar.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("Calendario");

	$app->get('/internacion', function($request, $response, $args) use ($userController){
        $args['version'] = FECHA_ULTIMO_PUSH;
		$responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "internacion.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("Internacion");

	$app->get('/guarderia', function($request, $response, $args) use ($userController){
        $args['version'] = FECHA_ULTIMO_PUSH;
		$responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "guarderia.twig", $args);
		}else return $response->withRedirect($request->getUri()->getBaseUrl());
	})->setName("Guarderia");

	$app->post('/getEventCalendarByDay', function(Request $request, Response $response) use ($userController, $calendarController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $day = $data['day'];
            $calendarType = $data['type'];

            $responseCalendar = $calendarController->getCalendarDataByDay($day, $calendarType);
			//var_dump("datos de agenda, categoria ",$calendarType, $responseCalendar);exit;

			return json_encode($responseCalendar);
        }else return json_encode($responseSession);
    });

    $app->post('/modifyEventCalendarByDay', function(Request $request, Response $response) use ($userController, $calendarController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $events = $data['event'];

            $idUser = $responseSession->session['IDENTIFICADOR'];
			return json_encode($calendarController->modifyNewEvent($events, $idUser));
        }else return json_encode($responseSession);
    });

    $app->post('/saveEventCalendarByDay', function(Request $request, Response $response) use ($userController, $calendarController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $newEvents = $data['event'];
            $type = $data['type'];
            $idUser = $responseSession->session['IDENTIFICADOR'];

			return json_encode($calendarController->saveNewEvent($newEvents, $idUser, $type));
        }else return json_encode($responseSession);
    });




	$app->post('/modifyGuarderiaByDay', function(Request $request, Response $response) use ($userController, $calendarController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idUser = $responseSession->session['IDENTIFICADOR'];
			return json_encode($calendarController->modifyNewEvent($idUser, $data));
        }else return json_encode($responseSession);
    });

    $app->post('/saveGuarderiaByDay', function(Request $request, Response $response) use ($userController, $calendarController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idUser = $responseSession->session['IDENTIFICADOR'];

			return json_encode($calendarController->saveNewGuarderia($idUser, $data));
        }else return json_encode($responseSession);
    });



    $app->post('/getHospitalizedPet', function(Request $request, Response $response) use ($userController, $hospitalizedPetController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $hospitalizedPlace = $data['hospitalizedPlace'];
            $lastId = $data['lastId'];

			return json_encode($hospitalizedPetController->getHospitalizedPet($hospitalizedPlace, $lastId));
        }else return json_encode($responseSession);
    });




    $app->post('/deleteEvent', function(Request $request, Response $response) use ($userController, $calendarController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idevent = $data['idEvent'];

			return json_encode($calendarController->deleteEvent($idevent));
        }else return json_encode($responseSession);
    });


    $app->post('/changeStatusEvent', function(Request $request, Response $response) use ($userController, $calendarController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idevent = $data['idEvent'];
            $status = $data['status'];

			return json_encode($calendarController->changeStatusEvent($idevent, $status));
        }else return json_encode($responseSession);
    });
}
?>