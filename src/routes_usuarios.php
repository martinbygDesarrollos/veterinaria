<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'controladores/ctr_usuarios.php';
require_once 'controladores/ctr_mascotas.php';

return function (App $app) {
    $container = $app->getContainer();

    //---------------------------- VISTAS ------------------------------------------------------
    $app->get('/login', function ($request, $response, $args) use ($container) {
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                $args['hayVencimientos'] = ctr_mascotas::getVencimientosVacunaPagina(0);
                $args['hayVencimientoSocio'] = ctr_usuarios::haySociosConCuotasVencidas();
                return $this->view->render($response, "index.twig", $args);
            }
        }
        return $this->view->render($response, "login.twig");
    })->setName("login");

    $app->get('/cerrarSesion', function ($request, $response, $args) use ($container) {
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                session_destroy();
            }
        }
        return $this->view->render($response, "login.twig");
    })->setName("login");

    $app->get('/newSocio', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                return $this->view->render($response, "newSocio.twig", $args);
            }
        }

        return $this->view->render($response, "index.twig", $args);
    })->setName("newSocio");

    $app->get('/verSociosActivos', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                return $this->view->render($response, "sociosActivos.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("sociosActivos");

    $app->get('/verSociosInactivos', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                return $this->view->render($response, "sociosInactivos.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("sociosInactivos");

    $app->get('/asignarSocioMascota/{idMascota}', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                $idMascota = $args['idMascota'];
                $args['mascota'] = ctr_mascotas::getMascota($idMascota);
                return $this->view->render($response, "asignarSocioMascota.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("asignarSocioMascota");

    $app->get('/verSocio/{idSocio}', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                $idSocio = $args['idSocio'];
                $args['socio'] = ctr_usuarios::getSocio($idSocio);
                return $this->view->render($response, "verSocio.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("verScoio");

    $app->get('/editSocio/{idSocio}', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;
                $idSocio = $args['idSocio'];
                $args['socio'] = ctr_usuarios::getSocio($idSocio);
                return $this->view->render($response, "editSocio.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("editarScoio");

    $app->get('/cuotasVencidas', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $args['administrador'] = $sesion;;
                $args['fechaVencimiento'] = ctr_mascotas::getFechaActual();
                return $this->view->render($response, "vencimientosCuota.twig", $args);
            }
        }
        return $this->view->render($response, "index.twig", $args);
    })->setName("cuotasVencidas");

    //------------------------------------------------------------------------------------------

    //------------------------------ POST ------------------------------------------------------

    $app->post('/buscadorDeSociosVencimientoCuota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $nombreSocio = $data['nombreSocio'];
                return json_encode(ctr_usuarios::buscadorDeSociosVencimientoCuota($nombreSocio));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getVencimientosCuotaPagina', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $ultimoID = $data['ultimoID'];
                return json_encode(ctr_usuarios::getVencimientosCuotaPagina($ultimoID));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/iniciarSesion', function(Request $request, Response $response){
        if (!isset($_SESSION['administrador'])) {
            $data = $request->getParams();
            $usuario = $data['usuario'];
            $pass = $data['pass'];
            return json_encode(ctr_usuarios::signIn($usuario, sha1($pass)));
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Usted ya cuenta con una sesión activa, cierre sesión si desea ingresar con otro usuario.";
        return json_encode($response);
    });

    $app->post('/insertNewUsuario', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $nombre = $data['nombre'];
                $email = $data['email'];

                return json_encode(ctr_usuarios::insertNewUsuario($nombre, $email));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/updateUsuario', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idUsuario = $data['idUsuario'];
                $nombre = $data['nombre'];
                $email = $data['email'];
                return json_encode(ctr_usuarios::updateUsuario($idUsuario, $nombre, $email));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getUsuario', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idUsuario = $data['idUsuario'];
                return json_encode(ctr_usuarios::getUsuario($idUsuario));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/updatePassAdministrador',function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $nombre = $data['nombre'];
                $passActual = $data['passActual'];
                $pass1 = $data['pass1'];
                return json_encode(ctr_usuarios::updatePassAdministrador($nombre, sha1($passActual), sha1($pass1)));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/insertNewSocio', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $cedula = $data['cedula'];
                $nombre = $data['nombre'];
                $telefono = $data['telefono'];
                $telefax = $data['telefax'];
                $direccion = $data['direccion'];
                $fechaPago =  $data['fechaPago'];
                $lugarPago = $data['lugarPago'];
                $email = $data['email'];
                $rut = $data['rut'];
                $tipoSocio = $data['tipoSocio'];
                return json_encode(ctr_usuarios::insertNewSocio($cedula, $nombre, $telefono, $telefax, $direccion, $fechaPago, $lugarPago, $email, $rut, $tipoSocio));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/updateSocio', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idSocio = $data['idSocio'];
                $nombre = $data['nombre'];
                $cedula = $data['cedula'];
                $direccion = $data['direccion'];
                $telefono = $data['telefono'];
                $fechaPago =  $data['fechaPago'];
                $lugarPago = $data['lugarPago'];
                $fechaIngreso = $data['fechaIngreso'];
                $email = $data['email'];
                $rut = $data['rut'];
                $telefax = $data['telefax'];
                $tipoSocio = $data['tipo'];

                return json_encode(ctr_usuarios::updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $fechaIngreso, $email, $rut, $telefax, $tipoSocio));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/activarDesactivarSocio', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idSocio = $data['idSocio'];

                return json_encode(ctr_usuarios::activarDesactivarSocio($idSocio));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/notificarSocioVacuna', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idSocio = $data['idSocio'];
                $idMascota = $data['idMascota'];
                return json_encode(ctr_usuarios::notificarSocioVacuna($idSocio, $idMascota));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/notificarSocioCuota', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $idSocio = $data['idSocio'];
                return json_encode(ctr_usuarios::notificarSocioCuota($idSocio));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });

    $app->post('/getSociosPagina', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $ultimoID = $data['ultimoID'];
                $estadoSocio = $data['estadoSocios'];
                return json_encode(ctr_usuarios::getSociosPagina($ultimoID, $estadoSocio));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });
    //------------------------------------------------------------------------------------------

    $app->post('/buscadorDeSocios', function(Request $request, Response $response){
        if (isset($_SESSION['administrador'])) {
            $sesion = $_SESSION['administrador'];
            $result = usuarios::validarSesionActiva($sesion->usuario, $sesion->token);
            if($result){
                $data = $request->getParams();
                $nombreSocio = $data['nombreSocio'];
                $estadoSocio = $data['estadoSocio'];
                return json_encode(ctr_usuarios::buscadorSocioNombre($nombreSocio, $estadoSocio));
            }
        }

        $response = new \stdClass();
        $response->retorno = false;
        $response->mensajeError = "Su sesión a caducado porfavor vuelva a ingresar para continuar.";
        return json_encode($response);
    });
}
?>