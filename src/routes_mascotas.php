<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'controladores/ctr_mascotas.php';

return function (App $app) {
    $container = $app->getContainer();

	//---------------------------- VISTAS ------------------------------------------------------
    $app->get('/mascotas', function($request, $response, $args) use ($container){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $args['administrador'] = $sesionActiva;
            $args['mascotas'] = ctr_mascotas::getMascotas();
            return $this->view->render($response, "mascotas.twig", $args);
        }
    })->setName('verMascotas');

    $app->get('/mascotasInactivasPendientes', function($request, $response, $args) use ($container){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $args['administrador'] = $sesionActiva;
            $args['mascotas'] = ctr_mascotas::getMascotasInactivasPendientes();
            return $this->view->render($response, "mascotasInactivasPendientes.twig", $args);
        }
    })->setName('verMascotasInactivasPendientes');

    $app->get('/newMascota', function($request, $response, $args) use ($container){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $args['administrador'] = $sesionActiva;
            $args['socios'] = ctr_usuarios::getSocios();
            return $this->view->render($response, "newMascota.twig", $args);
        }
    })->setName('newMascota');

    $app->get('/verMascota/{idMascota }', function($request, $response, $args) use ($container){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $args['administrador'] = $sesionActiva;
            $idMascota = $args['idMascota'];
            $args['info'] = ctr_mascotas::getMascotaCompleto($idMascota);
            $args['sociosNoVinculados'] = ctr_usuarios::sociosNoVinculados($idMascota);
            return $this->view->render($response, "verMascota.twig", $args);
        }
    })->setName("verMascota");

    $app->get('/editMascota/{idMascota}', function($request, $response, $args) use($container){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $args['administrador'] = $sesionActiva;
            $idMascota = $args['idMascota'];
            $args['mascota'] = ctr_mascotas::getMascota($idMascota);
            $args['duenio'] = ctr_usuarios::getSocioMascota($idMascota);
            $args['socios'] = ctr_usuarios::sociosNoVinculados($idMascota);

            return $this->view->render($response,"editMascota.twig", $args);
        }
    })->setName("editMascota");

    $app->get('/vencimientos', function($request, $response, $args) use($container){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $args['administrador'] = $sesionActiva;
            $args['fechaVencimiento'] = ctr_mascotas::getFechaActual();
            $args['infoVencimientos'] = ctr_mascotas::getInfoVencimientos();
            return $this->view->render($response, "vencimientos.twig", $args);
        }
    })->setName('vencimientos');

    //------------------------------------------------------------------------------------------
	//----------------------------- POST -------------------------------------------------------
    $app->post('/insertNewMascota', function(Request $request, Response $response){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {

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
        }
    });

    $app->post('/updateMascota', function(Request $request, Response $response){
        // $sesionActiva = $_SESSION['administrador'];
        // if (isset($sesionActiva)) {
        $data = $request->getParams();
        $idSocio = $data['idSocio'];
        $idMascota = $data['idMascota'];
        $nombre = $data['nombre'];
        $especie = $data['especie'];
        $raza = $data['raza'];
        $sexo = $data['sexo'];
        $color = $data['color'];
        $pedigree = $data['pedigree'];
        $fechaNacimiento = $data['fechaNacimiento'];
        $pelo = $data['pelo'];
        $chip = $data['chip'];
        $observaciones = $data['observaciones'];

        return json_encode(ctr_mascotas::updateMascota($idSocio, $idMascota, $nombre, $especie, $raza, $sexo, $color, $pedigree, $fechaNacimiento, $pelo, $chip, $observaciones));
        // }
    });

    $app->post('/activarDesactivarMascota', function(Request $request, Response $response){
        $data = $request->getParams();

        $idMascota = $data['idMascota'];
        return json_encode(ctr_mascotas::activarDesactivarMascota($idMascota));
    });

    $app->post('/updateVacuna', function(Request $request, Response $response){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $args['administrador'] = $sesionActiva;
            $data = $request->getParams();
            $nombre = $data['nombre'];
            $codigo = $data['codigo'];
            $fechaVencimiento = $data['fechaVencimiento'];
            $laboratorio = $data['laboratorio'];
        }
    });

    $app->post('/aplicarDosisVacuna', function(Request $request, Response $response){

        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $data = $request->getParams();
            $idVacunaMascota = $data['idVacunaMascota'];
            return json_encode(ctr_mascotas::aplicarDosisVacuna($idVacunaMascota));
        }
    });

    $app->post('/aplicarNuevaVacunaMascota', function(Request $request, Response $response){

        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            $nombreVacuna = $data['nombreVacuna'];
            $intervalo = $data['intervalo'];
            $fechaDosis = $data['fechaDosis'];
            $observaciones = $data['observaciones'];

            return json_encode(ctr_mascotas::aplicarNuevaVacunaMascota($idMascota, $nombreVacuna, $intervalo, $fechaDosis, $observaciones));
        }
    });

    $app->post('/insertEnfermedadMascota', function(Request $request, Response $response){

        $data = $request->getParams();
        $idMascota = $data['idMascota'];
        $nombre = $data['nombreEnfermedad'];
        $fechaDiagnostico = $data['fechaDiagnosticoEnfermedad'];
        $observaciones = $data['observacionesEnfermedad'];

        return json_encode(ctr_mascotas::insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnostico, $observaciones));
    });

    $app->post('/getEnfermedadMascota', function(Request $request, Response $response){

        $data = $request->getParams();
        $idEnfermedad = $data['idEnfermedad'];
        return json_encode(ctr_mascotas::getEnfermedadMascota($idEnfermedad));
    });

    $app->post('/updateEnfermedadMascota', function(Request $request, Response $response){

        $data = $request->getParams();
        $idEnfermedad = $data['idEnfermedad'];
        $nombre = $data['nombreEnfermedad'];
        $fechaDiagnostico = $data['fechaDiagnosticoEnfermedad'];
        $observaciones = $data['observacionesEnfermedad'];

        return json_encode(ctr_mascotas::updateEnfermedadMascota($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones));
    });


    $app->post('/vincularSocioMascota', function(Request $request, Response $response){

        // $sesionActiva = $_SESSION['administrador'];
        // if (isset($sesionActiva)) {
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            $idMascota = $data['idMascota'];
            return json_encode(ctr_mascotas::vincularSocioMascota($idSocio, $idMascota));
        // }
    });

	//------------------------------------------------------------------------------------------

}
?>