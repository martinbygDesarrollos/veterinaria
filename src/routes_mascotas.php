<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'controladores/ctr_mascotas.php';

return function (App $app) {
    $container = $app->getContainer();

	//---------------------------- VISTAS ------------------------------------------------------
    $app->get('/mascotas', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                return $this->view->render($response, "mascotas.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName('verMascotas');

    $app->get('/mascotasInactivasPendientes', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                return $this->view->render($response, "mascotasInactivasPendientes.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName('verMascotasInactivasPendientes');

    $app->get('/newMascota', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                return $this->view->render($response, "newMascota.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName('nuevaMascota');

    $app->get('/verMascota/{idMascota }', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                $idMascota = $args['idMascota'];
                $args['info'] = ctr_mascotas::getMascotaCompleto($idMascota);
                return $this->view->render($response, "verMascota.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("verMascota");

    $app->get('/editMascota/{idMascota}', function($request, $response, $args) use($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                $idMascota = $args['idMascota'];
                $args['mascota'] = ctr_mascotas::getMascota($idMascota);
                $args['duenio'] = ctr_usuarios::getSocioMascota($idMascota);
                return $this->view->render($response,"editMascota.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("editarMascota");

    $app->get('/vencimientos', function($request, $response, $args) use($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                $args['fechaVencimiento'] = ctr_mascotas::getFechaActual();
                return $this->view->render($response, "vencimientos.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName('vencimientos');

    //------------------------------------------------------------------------------------------
	//----------------------------- POST -------------------------------------------------------

    $app->post('/getVencimientosVacunaPagina', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $ultimoID = $data['ultimoID'];
                return json_encode(ctr_mascotas::getVencimientosVacunaPagina($ultimoID));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getMascotasPagina', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $ultimoID = $data['ultimoID'];
                $estadoMascota = $data['estadoMascota'];
                return json_encode(ctr_mascotas::getMascotasPagina($ultimoID, $estadoMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/buscadorDeMascotas', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $nombreMascota = $data['nombreMascota'];
                $estadoMascota = $data['estadoMascota'];
                return json_encode(ctr_mascotas::buscadorMascotaNombre($nombreMascota, $estadoMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getVacunasPagina', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $ultimoID = $data['ultimoID'];
                $idMascota = $data['idMascota'];
                return json_encode(ctr_mascotas::getVacunasPagina($ultimoID, $idMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getEnfermedadesPagina', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $ultimoID = $data['ultimoID'];
                $idMascota = $data['idMascota'];
                return json_encode(ctr_mascotas::getEnfermedadesPagina($ultimoID, $idMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getAnalisisPagina', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $ultimoID = $data['ultimoID'];
                $idMascota = $data['idMascota'];
                return json_encode(ctr_mascotas::getAnalisisPagina($ultimoID, $idMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/insertNewMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
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
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/updateMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
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
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/activarDesactivarMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idMascota = $data['idMascota'];
                return json_encode(ctr_mascotas::activarDesactivarMascota($idMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getVacunaMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idVacunaMascota = $data['idVacunaMascota'];
                return json_encode(ctr_mascotas::getVacunaMascota($idVacunaMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/updateVacunaMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idVacunaMascota = $data['idVacunaMascota'];
                $nombre = $data['nombre'];
                $intervalo = $data['intervalo'];
                $fechaUltimaDosis = $data['fechaUltimaDosis'];
                $observaciones = $data['observaciones'];
                return json_encode(ctr_mascotas::updateVacunaMascota($idVacunaMascota, $nombre, $intervalo, $fechaUltimaDosis, $observaciones));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/aplicarDosisVacuna', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idVacunaMascota = $data['idVacunaMascota'];
                return json_encode(ctr_mascotas::aplicarDosisVacuna($idVacunaMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/aplicarNuevaVacunaMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idMascota = $data['idMascota'];
                $nombreVacuna = $data['nombreVacuna'];
                $intervalo = $data['intervalo'];
                $fechaDosis = $data['fechaDosis'];
                $observaciones = $data['observaciones'];
                return json_encode(ctr_mascotas::aplicarNuevaVacunaMascota($idMascota, $nombreVacuna, $intervalo, $fechaDosis, $observaciones));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/insertEnfermedadMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idMascota = $data['idMascota'];
                $nombre = $data['nombreEnfermedad'];
                $fechaDiagnostico = $data['fechaDiagnosticoEnfermedad'];
                $observaciones = $data['observacionesEnfermedad'];
                return json_encode(ctr_mascotas::insertEnfermedadMascota($idMascota, $nombre, $fechaDiagnostico, $observaciones));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getEnfermedadMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idEnfermedad = $data['idEnfermedad'];
                return json_encode(ctr_mascotas::getEnfermedadMascota($idEnfermedad));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/updateEnfermedadMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idEnfermedad = $data['idEnfermedad'];
                $nombre = $data['nombreEnfermedad'];
                $fechaDiagnostico = $data['fechaDiagnosticoEnfermedad'];
                $observaciones = $data['observacionesEnfermedad'];
                return json_encode(ctr_mascotas::updateEnfermedadMascota($idEnfermedad, $nombre, $fechaDiagnostico, $observaciones));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });


    $app->post('/vincularSocioMascota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idSocio = $data['idSocio'];
                $idMascota = $data['idMascota'];
                return json_encode(ctr_mascotas::vincularSocioMascota($idSocio, $idMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/insertNewAnalisis', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idMascota = $data['idMascota'];
                $fechaAnalisis = $data['fechaAnalisis'];
                $nombreAnalisis = $data['nombreAnalisis'];
                $detalleAnalisis = $data['detalleAnalisis'];
                $resultadoAnalisis = $data['resultadoAnalisis'];
                return json_encode(ctr_mascotas::insertNewAnalisis($idMascota, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/updateAnalisis', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idAnalisis = $data['idAnalisis'];
                $fechaAnalisis = $data['fechaAnalisis'];
                $nombreAnalisis = $data['nombreAnalisis'];
                $detalleAnalisis = $data['detalleAnalisis'];
                $resultadoAnalisis = $data['resultadoAnalisis'];
                return json_encode(ctr_mascotas::updateAnalisis($idAnalisis, $nombreAnalisis, $fechaAnalisis, $detalleAnalisis, $resultadoAnalisis));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getAnalisis', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idAnalisis = $data['idAnalisis'];
                return json_encode(ctr_mascotas::getAnalisis($idAnalisis));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

	//------------------------------------------------------------------------------------------

}
?>