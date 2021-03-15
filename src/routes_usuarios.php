<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once 'controladores/ctr_usuarios.php';

return function (App $app) {
    $container = $app->getContainer();

    //---------------------------- VISTAS ------------------------------------------------------
    $app->get('/login', function ($request, $response, $args) use ($container) {
        if (isset($_SESSION['admin'])) {
            return $this->view->render($response, "index.twig", $args);
        }
        return $this->view->render($response, "login.twig");
    })->setName("login");

    $app->get('/newSocio', function($request, $response, $args) use ($container){
        return $this->view->render($response, "newSocio.twig", $args);
    })->setName("newSocio");

    $app->get('/verSocios', function($request, $response, $args) use ($container){
        $args['socios'] = ctr_usuarios::getSocios();
        return $this->view->render($response, "socios.twig", $args);
    })->setName("socios");

    $app->get('/verSocio/{idSocio}', function($request, $response, $args) use ($container){
        $idSocio = $args['idSocio'];
        $args['socio'] = ctr_usuarios::getSocio($idSocio);
        return $this->view->render($response, "verSocio.twig", $args);
    })->setName("verScoio");

    //------------------------------------------------------------------------------------------
    // $app->get('/menuPrincipal', function ($request, $response, $args) use ($container) {
    //     if (isset($_SESSION['admin'])) {
    //         $args["session"]=$_SESSION['admin'];
    //         return $this->view->render($response, "index.twig", $args);
    //     } else {
    //         return $this->view->render($response, "login.twig");
    //     }
    // });

    //------------------------------ POST ------------------------------------------------------

    $app->post('/iniciarSesion', function(Request $request, Response $response){
        $data = $request->getParams();

        $usuario = $data['usuario'];
        $pass = $data['pass'];

        return json_encode(ctr_usuarios::signIn($usuario, sha1($pass)));
    });

    $app->post('/insertNewUsuario',function(Request $request, Response $response){
        $data = $request->getParams();

        $usuario = $data['usuario'];
        $pass = $data['pass'];

        return json_encode(ctr_usuarios::insertNewUsuario($usuario, sha1($pass)));
    });

    $app->post('/insertNewSocio', function(Request $request, Response $response){
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
    });

    $app->post('/createNewGroup', function(Request $request, Response $response){
        $data = $request->getParams();

        $nombre = $data['nombre'];
        $funciones = $data['funciones'];

        return json_encode(ctr_usuarios::createNewGroup($nombre, $funciones));
    });

    $app->post('/asignarUsuarioGrupo', function(Request $request, Response $response){

        $data = $request->getParams();
        $idUsuario = $data['idUsuario'];
        $nombreGrupo = $data['nombreGrupo'];

        return json_encode(ctr_usuarios::vincularUsuarioGrupo($idUsuario, $nombreGrupo));
    });

    //------------------------------------------------------------------------------------------

}
?>