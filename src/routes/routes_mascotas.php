<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../src/controladores/ctr_mascotas.php';

return function (App $app) {
    $container = $app->getContainer();

	//---------------------------- VISTAS ------------------------------------------------------
    $app->get('/mascotas', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            return $this->view->render($response, "mascotas.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName('Mascotas');

    $app->get('/nueva-mascota/{idSocio}', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $idSocio = $args['idSocio'];
            $responseGetSocio = ctr_usuarios::getSocio($idSocio);
            if($responseGetSocio->result == 2)
                $args['socio'] = $responseGetSocio->socio;
            return $this->view->render($response, "newMascota.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName('NuevaMascota');

    $app->get('/ver-mascota/{idMascota}', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;

            $idMascota = $args['idMascota'];
            $args['SocioMascota'] = ctr_mascotas::getMascotaWithSocio($idMascota);
            $args['responseVacunas'] = ctr_mascotas::getVacunasMascota($idMascota);
            $args['responseEnfermedades'] = ctr_mascotas::getEnfermedadesMascota($idMascota);
            $args['responseAnalisis'] = ctr_mascotas::getAnalisisMascota($idMascota);
            return $this->view->render($response, "verMascota.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName("verMascota");

    $app->get('/vencimientos', function($request, $response, $args) use($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $args['responseVencimientos'] = ctr_mascotas::getFechasVacunasVencimiento();
            return $this->view->render($response, "vencimientos.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName('VacunasVencidas');

    //------------------------------------------------------------------------------------------
	//----------------------------- POST -------------------------------------------------------

    $app->post('/getMascotaToEdit', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            return json_encode(ctr_mascotas::getMascotaToEdit($idMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/getMascotasPagina', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $lastId = $data['lastId'];
            $textToSearch = $data['textToSearch'];
            $stateMascota = $data['stateMascota'];
            return json_encode(ctr_mascotas::getMascotas($lastId, $textToSearch, $stateMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/insertNewMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            $nombre = $data['nombre'];
            $especie = $data['especie'];
            $raza = $data['raza'];
            $sexo = $data['sexo'];
            $color = $data['color'];
            $pedigree  = $data['pedigree'];
            $fechaNacimiento = $data['nacimiento'];
            $pelo = $data['pelo'];
            $chip = $data['chip'];
            $observaciones = $data['observaciones'];
            return json_encode(ctr_mascotas::insertNewMascota($idSocio, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones));
        }else return json_encode($responseSession);
    });

    $app->post('/modificarMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            $nombre = $data['nombre'];
            $especie = $data['especie'];
            $raza = $data['raza'];
            $sexo = $data['sexo'];
            $color = $data['color'];
            $pedigree = $data['pedigree'];
            $fechaNacimiento = $data['fechaNacimiento'];
            $muerte = $data['fechaFallecimiento'];
            $pelo = $data['pelo'];
            $chip = $data['chip'];
            $observaciones = $data['observaciones'];
            return json_encode(ctr_mascotas::modificarMascota($idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $muerte, $pelo, $chip, $observaciones));
        }else return json_encode($responseSession);
    });

    $app->post('/activarDesactivarMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            return json_encode(ctr_mascotas::activarDesactivarMascota($idMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/getVacunaMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idVacunaMascota = $data['idVacunaMascota'];
            return json_encode(ctr_mascotas::getVacunaMascota($idVacunaMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/getVacunaMascotaToShow', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idVacunaMascota = $data['idVacunaMascota'];
            return json_encode(ctr_mascotas::getVacunaMascotaToShowView($idVacunaMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/updateVacunaMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idVacunaMascota = $data['idVacunaMascota'];
            $nombre = $data['nombre'];
            $intervalo = $data['intervalo'];
            $fechaUltimaDosis = $data['fechaUltimaDosis'];
            $observaciones = $data['observaciones'];
            return json_encode(ctr_mascotas::updateVacunaMascota($idVacunaMascota, $nombre, $intervalo, $fechaUltimaDosis, $observaciones));
        }else return json_encode($responseSession);
    });

    $app->post('/aplicarNuevaVacunaMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            $nombreVacuna = $data['nombreVacuna'];
            $intervalo = $data['intervalo'];
            $fechaDosis = $data['fechaDosis'];
            $observaciones = $data['observaciones'];
            return json_encode(ctr_mascotas::aplicarNuevaVacunaMascota($idMascota, $nombreVacuna, $intervalo, $fechaDosis, $observaciones));
        }else return json_encode($responseSession);
    });

    $app->post('/borrarVacunaMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idVacunaMascota = $data['idVacunaMascota'];
            return json_encode(ctr_mascotas::borrarVacunaMascota($idVacunaMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/aplicarDosisVacuna', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idVacunaMascota = $data['idVacunaMascota'];
            $dateDosis = $data['dateDosis'];
            return json_encode(ctr_mascotas::aplicarDosisVacuna($idVacunaMascota, $dateDosis));
        }else return json_encode($responseSession);
    });

    $app->post('/insertEnfermedadMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            $nombre = $data['nombre'];
            $fechaDiagnostico = $data['fechaDiagnostico'];
            $observaciones = $data['observaciones'];
            return json_encode(ctr_mascotas::insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnostico, $observaciones));
        }else return json_encode($responseSession);
    });

    $app->post('/deleteEnfermedad', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idEnfermedad = $data['idEnfermedad'];
            return json_encode(ctr_mascotas::deleteEnfermedad($idEnfermedad));
        }else return json_encode($responseSession);
    });

    $app->post('/getEnfermedad', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idEnfermedad = $data['idEnfermedad'];
            return json_encode(ctr_mascotas::getEnfermedadMascota($idEnfermedad));
        }else return json_encode($responseSession);
    });

    $app->post('/getEnfermedadToShow', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idEnfermedad = $data['idEnfermedad'];
            return json_encode(ctr_mascotas::getEnfermedadMascotaToShow($idEnfermedad));
        }else return json_encode($responseSession);
    });

    $app->post('/updateEnfermedad', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idEnfermedad = $data['idEnfermedad'];
            $nombre = $data['nombre'];
            $fechaDiagnostico = $data['fechaDiagnostico'];
            $observaciones = $data['observaciones'];
            return json_encode(ctr_mascotas::updateEnfermedad($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones));
        }else return json_encode($responseSession);
    });

    $app->post('/insertAnalisis', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            $fechaAnalisis = $data['fecha'];
            $nombreAnalisis = $data['nombre'];
            $detalleAnalisis = $data['detalle'];
            $resultadoAnalisis = $data['resultado'];
            return json_encode(ctr_mascotas::insertAnalisis($idMascota, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis));
        }else return json_encode($responseSession);
    });

    $app->post('/updateAnalisis', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idAnalisis = $data['idAnalisis'];
            $fechaAnalisis = $data['fecha'];
            $nombreAnalisis = $data['nombre'];
            $detalleAnalisis = $data['detalle'];
            $resultadoAnalisis = $data['resultado'];
            return json_encode(ctr_mascotas::updateAnalisis($idAnalisis, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis));
        }else return json_encode($responseSession);
    });

    $app->post('/deleteAnalisis', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idAnalisis = $data['idAnalisis'];
            return json_encode(ctr_mascotas::deleteAnalisis($idAnalisis));
        }else return json_encode($responseSession);
    });

    $app->post('/getAnalisis', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idAnalisis = $data['idAnalisis'];
            return json_encode(ctr_mascotas::getAnalisis($idAnalisis));
        }else return json_encode($responseSession);
    });

    $app->post('/getAnalisisToShow', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idAnalisis = $data['idAnalisis'];
            return json_encode(ctr_mascotas::getAnalisisToShow($idAnalisis));
        }else return json_encode($responseSession);
    });

    $app->post('/getMascotasNoSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $textToSearch = $data['textToSearch'];
            return json_encode(ctr_mascotas::getMascotasNoSocio($textToSearch));
        }else return json_encode($responseSession);
    });

    $app->post('/getVacunasVencidas', function(Request $request, Response $response){
        $data = $request->getParams();
        $dateVencimiento = $data['dateVencimiento'];
        return json_encode(ctr_mascotas::getVacunasVencidas($dateVencimiento));
    });

    $app->post('/getSocioPorMascota', function(Request $request, Response $response){
        $data = $request->getParams();
        $idMascota = $data['idMascota'];
        return json_encode(ctr_mascotas::getMascotaWithSocio($idMascota));
    });

    $app->post('/getVacunasByInput', function(Request $request, Response $response){
        $data = $request->getParams();
        $value = $data['value'];
        return json_encode(ctr_mascotas::getVacunasByInput($value));
    });

    $app->post('/getVacunasByName', function(Request $request, Response $response){
        $data = $request->getParams();
        $value = $data['value'];
        return json_encode(ctr_mascotas::getVacunasByName($value));
    });

	//------------------------------------------------------------------------------------------

}
?>