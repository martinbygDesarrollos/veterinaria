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
            $args['administrador'] = $_SESSION['administrador'];
            return $this->view->render($response, "index.twig", $args);
        }else{
            return $this->view->render($response, "login.twig");
        }
    })->setName("login");

    $app->get('/cerrarSesion', function ($request, $response, $args) use ($container) {
        if (isset($_SESSION['administrador'])) {
         session_destroy();
     }
     return $this->view->render($response, "login.twig");
 })->setName("login");

    $app->get('/newSocio', function($request, $response, $args) use ($container){

        if (isset($_SESSION['administrador'])) {
            $args['administrador'] = $_SESSION['administrador'];
            return $this->view->render($response, "newSocio.twig", $args);
        }
    })->setName("newSocio");

    $app->get('/verSocios', function($request, $response, $args) use ($container){

        if (isset($_SESSION['administrador'])) {
            $args['administrador'] = $_SESSION['administrador'];
            $args['socios'] = ctr_usuarios::getSocios();
            return $this->view->render($response, "socios.twig", $args);
        }
    })->setName("socios");

    $app->get('/asignarSocioMascota/{idMascota}', function($request, $response, $args) use ($container){
        if (isset($_SESSION['administrador'])) {
            $args['administrador'] = $_SESSION['administrador'];
            $idMascota = $args['idMascota'];
            $args['socios'] = ctr_usuarios::sociosNoVinculados($idMascota);
            $args['mascota'] = ctr_mascotas::getMascota($idMascota);
            return $this->view->render($response, "asignarSocioMascota.twig", $args);
        }
    })->setName("socios");

    $app->get('/verSocio/{idSocio}', function($request, $response, $args) use ($container){

        if (isset($_SESSION['administrador'])) {
            $args['administrador'] = $_SESSION['administrador'];
            $idSocio = $args['idSocio'];
            $args['socio'] = ctr_usuarios::getSocio($idSocio);
            return $this->view->render($response, "verSocio.twig", $args);
        }
    })->setName("verScoio");

    $app->get('/editSocio/{idSocio}', function($request, $response, $args) use ($container){

        if (isset($_SESSION['administrador'])) {
            $args['administrador'] = $_SESSION['administrador'];
            $idSocio = $args['idSocio'];
            $args['socio'] = ctr_usuarios::getSocio($idSocio);
            return $this->view->render($response, "editSocio.twig", $args);
        }
    })->setName("verScoio");

    //------------------------------------------------------------------------------------------

    //------------------------------ POST ------------------------------------------------------

    $app->post('/iniciarSesion', function(Request $request, Response $response){

        if (!isset($_SESSION['administrador'])) {
            $data = $request->getParams();
            $usuario = $data['usuario'];
            $pass = $data['pass'];
            return json_encode(ctr_usuarios::signIn($usuario, sha1($pass)));
        }
    });

    $app->post('/insertNewUsuario', function(Request $request, Response $response){

        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $data = $request->getParams();
            $nombre = $data['nombre'];
            $email = $data['email'];

            return json_encode(ctr_usuarios::insertNewUsuario($nombre, $email));
        }
    });

    $app->post('/updateUsuario', function(Request $request, Response $response){

        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $data = $request->getParams();
            $idUsuario = $data['idUsuario'];
            $nombre = $data['nombre'];
            $email = $data['email'];

            return json_encode(ctr_usuarios::updateUsuario($idUsuario, $nombre, $email));
        }
    });

    $app->post('/getUsuario', function(Request $request, Response $response){
        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $data = $request->getParams();
            $idUsuario = $data['idUsuario'];

            return json_encode(ctr_usuarios::getUsuario($idUsuario));
        }
    });

    $app->post('/updatePassAdministrador',function(Request $request, Response $response){

        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $data = $request->getParams();
            $nombre = $data['nombre'];
            $passActual = $data['passActual'];
            $pass1 = $data['pass1'];
            return json_encode(ctr_usuarios::updatePassAdministrador($nombre, sha1($passActual), sha1($pass1)));
        }
    });

    $app->post('/insertNewSocio', function(Request $request, Response $response){

        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
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

            return json_encode(ctr_usuarios::insertNewSocio($cedula, $nombre, $telefono, $telefax, $direccion, $fechaPago, $lugarPago, $email, $rut));
        }
    });

    $app->post('/updateSocio', function(Request $request, Response $response){

        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {

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

            return json_encode(ctr_usuarios::updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $fechaPago, $lugarPago, $fechaIngreso, $email, $rut, $telefax));
        }
    });

    $app->post('/notificarSocio', function(Request $request, Response $response){

        $sesionActiva = $_SESSION['administrador'];
        if (isset($sesionActiva)) {
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            $idMascota = $data['idMascota'];

            return json_encode(ctr_usuarios::notificarSocio($idSocio, $idMascota));
        }
    });

    //------------------------------------------------------------------------------------------

}
?>