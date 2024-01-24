<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../src/controladores/ctr_mascotas.php';

return function (App $app) {
    $container = $app->getContainer();
    //---------------------------- CONTROLADORES Y CLASES ------------------------------------------------------
    $userController = new ctr_usuarios();
    $mascotaController = new ctr_mascotas();

	//---------------------------- VISTAS ------------------------------------------------------
    $app->get('/mascotas', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            return $this->view->render($response, "mascotas.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
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
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName('NuevaMascota');

    $app->get('/ver-mascota/{idMascota}', function($request, $response, $args) use ($container, $mascotaController){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;

            $idMascota = $args['idMascota'];
            $args['SocioMascota'] = $mascotaController->getMascotaWithSocio($idMascota);
            if ( $args['SocioMascota'] ){
                $args['rowColorClientType'] = "rowSocio";
                if ( isset($args['SocioMascota']->socio) ){
                    if ( $args['SocioMascota']->socio->tipoSocio == 0 ){ //NO SOCIO
                        $args['rowColorClientType'] = "rowNosocio";
                    }else if ( $args['SocioMascota']->socio->tipoSocio == 1 ){ //SOCIO
                        if ( $args['SocioMascota']->socio->deudor )
                            $args['rowColorClientType'] = "rowWarning";
                    }else if ( $args['SocioMascota']->socio->tipoSocio == 3 ){ //EX SOCIO
                        if ( $args['SocioMascota']->socio->deudor )
                            $args['rowColorClientType'] = "rowExsocioWarning";
                        else
                            $args['rowColorClientType'] = "rowExsocio";
                    }
                }else{
                    $args['rowColorClientType'] = "rowNosocio";
                }
            }

            $args['responseVacunas'] = $mascotaController->getVacunasMascota($idMascota);
            $args['responseEnfermedades'] = $mascotaController->getEnfermedadesMascota($idMascota);
            $args['responseAnalisis'] = $mascotaController->getAnalisisMascota($idMascota);

            $args['responseListVacunas'] = array();
            $listVac = $mascotaController->getListadoVacunas();
            if ( $listVac->result == 2 ){
                $args['responseListVacunas'] = $listVac->listResult;
            }

            return $this->view->render($response, "verMascota.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("verMascota");

    $app->get('/vencimientos', function($request, $response, $args) use($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $args['responseVencimientos'] = ctr_mascotas::getFechasVacunasVencimiento();
            return $this->view->render($response, "vencimientos.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
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
            $peso = $data['peso'];
            return json_encode(ctr_mascotas::insertNewMascota($idSocio, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones, $peso));
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
            $peso = $data['peso'];
            return json_encode(ctr_mascotas::modificarMascota($idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $muerte, $pelo, $chip, $observaciones, $peso));
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
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $dateVencimiento = $data['desde'];
            $dateVencimiento2 = $data['hasta'];
            $lastid = $data['lastid'];
            return json_encode(ctr_mascotas::getVacunasVencidas($dateVencimiento, $dateVencimiento2, $lastid));
        }else return json_encode($responseSession);
    });

    $app->post('/getSocioPorMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            return json_encode(ctr_mascotas::getMascotaWithSocio($idMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/getVacunasByInput', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $value = $data['value'];
            return json_encode(ctr_mascotas::getVacunasByInput($value));
        }else return json_encode($responseSession);
    });

    $app->post('/getVacunasByName', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $value = $data['value'];
            return json_encode(ctr_mascotas::getVacunasByName($value));
        }else return json_encode($responseSession);
    });

    $app->post('/getVacunasSinNotificar', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $value = $data['id'];
            return json_encode(ctr_mascotas::getVacunasSinNotificar($value));
        }else return json_encode($responseSession);
    });

    $app->post('/searchPetClientByName', function(Request $request, Response $response) use ($userController, $mascotaController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $value = $data['value'];
            $client = $data['client'];

            if ( isset($client) && $client != "")
                return json_encode( $mascotaController->getMascotasSocioByName($value, $client));
            else if( isset($value) && $value != "" )
                return json_encode( $mascotaController->getMascotaByName($value));
            else return json_encode($response->result = 1);

        }else return json_encode($responseSession);
    });

    $app->post('/getClientOrPetByInput', function(Request $request, Response $response) use ($userController, $mascotaController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $value = $data['value'];
            $indexLimit = $data['indexLimit'];

            if ( isset($value) && $value != "")
                return json_encode( $mascotaController->getClientOrPetByInput($value, $indexLimit));
            else return json_encode($response->result = 1);

        }else return json_encode($responseSession);
    });

    //------------------------------------------------------------------------------------------


    $app->post('/newPetHospitalized', function(Request $request, Response $response) use ($userController, $mascotaController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $place = $data['place'];
            $date = $data['date'];
            $hour = $data['hour'];
            $idMascota = $data['idMascota'];


            $responseInsert = $mascotaController->newPetHospitalized($idMascota, $place);
            if ( $responseInsert->result == 2 ) {

                $modalidad = "";
                if ( $place == "vet" ){
                    $modalidad = "mascota internada en veterinaria.";
                }else if ($place == "casa"){
                    $modalidad = "se realiza seguimiento.";
                }

                $responseHistorial = ctr_historiales::agregarHistoriaClinica($idMascota, $date, $hour, "Mascota internada", null, "Modalidad: ".$modalidad, null, null, null, null, null);

                if ( $responseHistorial->result != 2 ){
                    return json_encode($responseHistorial);
                }else return json_encode($responseInsert);
            }
            return json_encode($responseInsert);
        }else return json_encode($responseSession);
    });


    $app->post('/petHospitalizedOut', function(Request $request, Response $response) use ($userController, $mascotaController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();

            $date = $data['date'];
            $hour = $data['hour'];
            $idMascota = $data['idMascota'];

            $responseInsert = $mascotaController->petHospitalizedOut($idMascota);
            if ( $responseInsert->result == 2 ) {

                $responseHistorial = ctr_historiales::agregarHistoriaClinica($idMascota, $date, $hour, "Alta de internación", null, null, null, null, null, null, null);

                if ( $responseHistorial->result != 2 ){
                    return json_encode($responseHistorial);
                }else return json_encode($responseInsert);
            }
            return json_encode($responseInsert);
        }else return json_encode($responseSession);
    });




    $app->post('/unifyPetCards', function(Request $request, Response $response) use ($userController, $mascotaController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){

            $data = $request->getParams();
            $petone = $data['nameUnifyPetOne'];
            $pettwo = $data['nameUnifyPetTwo'];

            $response = $mascotaController->unifyPetCards($petone, $pettwo);
            return json_encode($response);
        }else return json_encode($responseSession);
    });



    $app->post('/changeNotifyVacuna', function(Request $request, Response $response) use ($userController, $mascotaController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){

            $data = $request->getParams();
            $vacuna = $data['vacuna'];
            $estado = $data['estado'];

            $response = $mascotaController->changeNotifyVacuna($vacuna, $estado);
            return json_encode($response);
        }else return json_encode($responseSession);
    });
}
?>