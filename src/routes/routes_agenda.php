<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../src/controladores/ctr_agenda.php';

return function (App $app) {

	$calendarController = new ctr_agenda();
	$userController = new ctr_usuarios();

	$app->get('/cirugia', function($request, $response, $args) use ($userController, $calendarController){
        $args['version'] = FECHA_ULTIMO_PUSH;
		$responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "cirugias.twig", $args);
		}else return $response->withRedirect('iniciar-sesion');
	})->setName("Cirugias");

	$app->get('/peluqueria', function($request, $response, $args) use ($userController){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = $userController->validateSession();
		if($responseSession->result == 2){
			$args['administrador'] = $responseSession->session;
			return $this->view->render($response, "peluquerias.twig", $args);
		}else return $response->withRedirect('iniciar-sesion');
	})->setName("Peluqueria");

	$app->get('/notas', function($request, $response, $args) use ($userController){
        $args['version'] = FECHA_ULTIMO_PUSH;
		return $response->withRedirect('iniciar-sesion');
	})->setName("Notas");

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
}
?>