<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'controladores/ctr_usuarios.php';
require_once 'controladores/ctr_mascotas.php';

return function (App $app) {
    $container = $app->getContainer();

    //---------------------------- VISTAS ------------------------------------------------------
    $app->get('/iniciar-sesion', function ($request, $response, $args) use ($container) {
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result != 2)
            return $this->view->render($response, "login.twig");
        else
            return $response->withRedirect('cerrar-sesion');
    })->setName("Login");

    $app->get('/cerrar-sesion', function ($request, $response, $args) use ($container) {
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            session_destroy();
        }else return $response->withRedirect('iniciar-sesion');
    })->setName("LogOut");

    $app->get('/agregar-socio', function($request, $response, $args) use ($container){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            return $this->view->render($response, "newSocio.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName("NewSocio");

    $app->get('/socios', function($request, $response, $args) use ($container){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            return $this->view->render($response, "socios.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName("Socios");

    $app->get('/asignarSocioMascota/{idMascota}', function($request, $response, $args) use ($container){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $idMascota = $args['idMascota'];
            $args['mascota'] = ctr_mascotas::getMascota($idMascota);
            return $this->view->render($response, "asignarSocioMascota.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName("asignarSocioMascota");

    $app->get('/ver-socio/{idSocio}', function($request, $response, $args) use ($container){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $idSocio = $args['idSocio'];
            $responseGetSocio = ctr_usuarios::getSocioWithMascotaToShow($idSocio);
            if($responseGetSocio->result == 2)
                $args['responseSocio'] = $responseGetSocio;
            else return $response->withRedirect('socios');
            return $this->view->render($response, "verSocio.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName("VerSocio");

    $app->get('/modificar-socio/{idSocio}', function($request, $response, $args) use ($container){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $idSocio = $args['idSocio'];
            $args['responseSocio'] = ctr_usuarios::getSocioWithMascotaToShow($idSocio);
            return $this->view->render($response, "editSocio.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName("EditarSocio");

    $app->get('/cuotasVencidas', function($request, $response, $args) use ($container){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $args['fechaVencimiento'] = ctr_mascotas::getFechaActual();
            return $this->view->render($response, "vencimientosCuota.twig", $args);
        }else return $response->withRedirect('iniciar-sesion');
    })->setName("cuotasVencidas");

    //------------------------------------------------------------------------------------------

    //------------------------------ POST ------------------------------------------------------

    $app->post('/getSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            return json_encode(ctr_usuarios::getSocio($idSocio));
        }
    });

    $app->post('/buscadorDeSociosVencimientoCuota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $nombreSocio = $data['nombreSocio'];
            return json_encode(ctr_usuarios::buscadorDeSociosVencimientoCuota($nombreSocio));
        }
    });

    $app->post('/getVencimientosCuotaPagina', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $ultimoID = $data['ultimoID'];
            return json_encode(ctr_usuarios::getVencimientosCuotaPagina($ultimoID));
        }
    });

    $app->post('/iniciarSesion', function(Request $request, Response $response){
        $data = $request->getParams();
        $usuario = $data['usuario'];
        $pass = $data['pass'];
        return json_encode(ctr_usuarios::signIn($usuario, sha1($pass)));
    });

    $app->post('/crearUsuario', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $usuario = $data['usuario'];
            $correo = $data['correo'];
            return json_encode(ctr_usuarios::crearUsuario($usuario, $correo));
        }
    });

    $app->post('/modificarUsuario', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idUsuario = $data['idUsuario'];
            $usuario = $data['usuario'];
            $correo = $data['correo'];
            return json_encode(ctr_usuarios::modificarUsuario($idUsuario, $usuario, $correo));
        }
    });

    $app->post('/getUsuario', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idUsuario = $data['idUsuario'];
            return json_encode(ctr_usuarios::getUsuario($idUsuario));
        }
    });

    $app->post('/updatePassAdministrador',function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $nombre = $data['nombre'];
            $passActual = $data['passActual'];
            $pass1 = $data['pass1'];
            return json_encode(ctr_usuarios::updatePassAdministrador($nombre, sha1($passActual), sha1($pass1)));
        }
    });

    $app->post('/insertNewSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
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
            return json_encode(ctr_usuarios::insertNewSocio($nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $telefax, $fechaIngreso, $email, $rut, $tipoSocio));
        }else return json_encode($responseSession);
    });

    $app->post('/updateSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            $nombre = $data['nombre'];
            $cedula = $data['cedula'];
            $direccion = $data['direccion'];
            $telefono = $data['telefono'];
            $email = $data['email'];
            $rut = $data['rut'];
            $telefax = $data['telefax'];
            $tipoSocio = $data['tipo'];
            $lugarPago = $data['lugarPago'];
            $fechaIngreso = $data['fechaIngreso'];
            $ultimoPago = $data['ultimoPago'];
            $fechaPago =  $data['fechaPago'];
            $ultimoMesPago = $data['ultimoMesPago'];
            return json_encode(ctr_usuarios::updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax, $tipoSocio, $lugarPago, $fechaIngreso, $ultimoPago, $fechaPago, $ultimoMesPago));
        }else return json_encode($responseSession);
    });

    $app->post('/activarDesactivarSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            return json_encode(ctr_usuarios::activarDesactivarSocio($idSocio));
        }
    });

    $app->post('/notificarSocioVacuna', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            $idMascota = $data['idMascota'];
            return json_encode(ctr_usuarios::notificarSocioVacuna($idSocio, $idMascota));
        }
    });

    $app->post('/actualizarCuotaSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            return json_encode(ctr_usuarios::actualizarCuotaSocio($idSocio));
        }
    });

    $app->post('/updateAllQuotaSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $cuotaUno = $data['cuotaUno'];
            $cuotaDos = $data['cuotaDos'];
            $cuotaExtra = $data['cuotaExtra'];
            $plazoDeuda = $data['plazoDeuda'];
            return json_encode(ctr_usuarios::updateAllQuotaSocio($cuotaUno, $cuotaDos, $cuotaExtra, $plazoDeuda));
        }
    });

    $app->post('/notificarSocioCuota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            return json_encode(ctr_usuarios::notificarSocioCuota($idSocio));
        }
    });

    $app->post('/getSociosPagina', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $lastId = $data['lastId'];
            $estado= $data['estado'];
            $textToSearch = $data['textToSearch'];
            return json_encode(ctr_usuarios::getSociosPagina($lastId, $estado, $textToSearch));
        }
    });

}
?>