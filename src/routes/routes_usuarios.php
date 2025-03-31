<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

require_once '../src/controladores/ctr_usuarios.php';
require_once '../src/controladores/ctr_mascotas.php';
require_once '../src/utils/whatsapp.php';

return function (App $app) {
    $container = $app->getContainer();

    //---------------------------- CONTROLADORES Y CLASES ------------------------------------------------------
    $userController = new ctr_usuarios();
    $whatsappClass = new whatsapp();

    //---------------------------- VISTAS ------------------------------------------------------
    $app->get('/iniciar-sesion', function ($request, $response, $args) use ($container) {
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result != 2)
            return $this->view->render($response, "login.twig");
        else
            return $response->withRedirect('cerrar-sesion');
    })->setName("Login");

    $app->get('/cerrar-sesion', function ($request, $response, $args) use ($container) {
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2)
            session_destroy();

        return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("LogOut");

    $app->get('/agregar-socio', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            return $this->view->render($response, "newSocio.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("NewSocio");

    $app->get('/socios', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            return $this->view->render($response, "socios.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("Socios");

    $app->get('/asignarSocioMascota/{idMascota}', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $idMascota = $args['idMascota'];
            $args['mascota'] = ctr_mascotas::getMascota($idMascota);
            return $this->view->render($response, "asignarSocioMascota.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("asignarSocioMascota");

    $app->get('/ver-socio/{idSocio}', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $idSocio = $args['idSocio'];
            $responseGetSocio = ctr_usuarios::getSocioWithMascotaToShow($idSocio);
            if($responseGetSocio->result == 2){
                $args['responseSocio'] = $responseGetSocio;

                $args['rowColorClientType'] = "rowSocio";
                if ( $responseGetSocio->socio->tipoSocio == 0 ){ //NO SOCIO
                    $args['rowColorClientType'] = "rowNosocio";
                }else if ( $responseGetSocio->socio->tipoSocio == 1 ){ //SOCIO
                    if ( $responseGetSocio->socio->deudor )
                        $args['rowColorClientType'] = "rowWarning";
                }else if ( $responseGetSocio->socio->tipoSocio == 3 ){ //EX SOCIO
                    if ( $responseGetSocio->socio->deudor )
                        $args['rowColorClientType'] = "rowExsocioWarning";
                    else
                        $args['rowColorClientType'] = "rowExsocio";
                }
            }
            else return $response->withRedirect('socios');
            return $this->view->render($response, "verSocio.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("VerSocio");

    $app->get('/modificar-socio/{idSocio}', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            $idSocio = $args['idSocio'];
            $args['responseSocio'] = ctr_usuarios::getSocioWithMascotaToShow($idSocio);
            return $this->view->render($response, "editSocio.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("EditarSocio");

    $app->get('/cuotasVencidas', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            return $this->view->render($response, "vencimientosCuota.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("CuotasVencidas");

    $app->get('/notificaciones', function($request, $response, $args) use ($container){
        $args['version'] = FECHA_ULTIMO_PUSH;
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $args['administrador'] = $responseSession->session;
            return $this->view->render($response, "notifPendientes.twig", $args);
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    })->setName("Notificaciones");

    //------------------------------------------------------------------------------------------

    //------------------------------- DEBITOS --------------------------------------------------
    $app->get('/debitos-rest-cuotas', function($request, $response, $args) use ($container){
        $response = ctr_usuarios::getAllImportesSocios();
        return json_encode($response);
    });
    //------------------------------------------------------------------------------------------
    
    //------------------------------ POST ------------------------------------------------------

    $app->post('/gestcom-rest-cuotas', function(Request $request, Response $response){
        $data = $request->getParams();
        $ultimaCuota = $data['ultimaCuota'];
        $ultimoPago = $data['ultimoPago'];
        $idSocio = $data['numSocio'];
        $token = $data['token'];
        return json_encode(ctr_usuarios::gestComRestCuotas($idSocio, $ultimoPago, $ultimaCuota, $token));
    });

    $app->post('/gestcom-rest-facturas', function(Request $request, Response $response){
        $data = $request->getParams();
        $token = $data['token'];
        return json_encode(ctr_usuarios::getFileVistaFactura($token));
    });

    $app->post('/gestcom-rest-newclient', function(Request $request, Response $response) use ($userController){
        $data = $request->getParams();
        return json_encode(ctr_usuarios::gestcomNewClient($data));
    });


    $app->post('/gestcom-rest-historial', function(Request $request, Response $response) use ($userController){
        $data = $request->getParams();        /*
        $ultimaCuota = $data['ultimaCuota'];
        $ultimoPago = $data['ultimoPago'];
        $idSocio = $data['numSocio'];
        $token = $data['token'];*/
        return json_encode(ctr_usuarios::gestcomNewSale($data));
    });

    $app->post('/asignarMascotaSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            $idMascota = $data['idMascota'];
            return json_encode(ctr_usuarios::asignarMascotaSocio($idSocio, $idMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/desvincularMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            return json_encode(ctr_usuarios::desvincularMascota($idMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/getSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            return json_encode(ctr_usuarios::getSocio($idSocio));
        }else return json_encode($responseSession);
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
            return json_encode(ctr_usuarios::insertNewUsuario($usuario, $correo));
        }else return json_encode($responseSession);
    });

    $app->post('/modificarUsuario', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idUsuario = $data['idUsuario'];
            $usuario = $data['usuario'];
            $correo = $data['correo'];
            return json_encode(ctr_usuarios::modificarUsuario($idUsuario, $usuario, $correo));
        }else return json_encode($responseSession);
    });

    $app->post('/getUsuario', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idUsuario = $data['idUsuario'];
            return json_encode(ctr_usuarios::getUsuario($idUsuario));
        }else return json_encode($responseSession);
    });

    $app->post('/updatePassAdministrador',function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $nombre = $data['nombre'];
            $passActual = $data['passActual'];
            $pass1 = $data['pass1'];
            return json_encode(ctr_usuarios::updatePassAdministrador($nombre, sha1($passActual), sha1($pass1)));
        }else return json_encode($responseSession);
    });

    $app->post('/deleteUser',function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idUser = $data['idUser'];
            return json_encode(ctr_usuarios::deleteUser($idUser));
        }else return json_encode($responseSession);
    });

    $app->post('/cleanPassword',function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idUser = $data['idUser'];
            return json_encode(ctr_usuarios::cleanPassword($idUser));
        }else return json_encode($responseSession);
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
            $fechaBaja = $data['fechaBajaSocio'];
            $ultimoPago = $data['ultimoPago'];
            $fechaPago =  $data['fechaPago'];
            $ultimoMesPago = $data['ultimoMesPago'];

            if ( $cedula == "" ){
                $cedula = null;
            }
            return json_encode(ctr_usuarios::updateSocio($idSocio, $nombre, $cedula, $direccion, $telefono, $email, $rut, $telefax, $tipoSocio, $lugarPago, $fechaIngreso, $fechaBaja, $ultimoPago, $fechaPago, $ultimoMesPago));
        }else return json_encode($responseSession);
    });

    $app->post('/activarDesactivarSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            return json_encode(ctr_usuarios::activarDesactivarSocio($idSocio));
        }else return json_encode($responseSession);
    });

    $app->post('/notificarVacunaMascota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['idMascota'];
            return json_encode(ctr_usuarios::notificarVacunaMascota($idMascota));
        }else return json_encode($responseSession);
    });

    $app->post('/actualizarCuotaSocio', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            return json_encode(ctr_usuarios::actualizarCuotaSocio($idSocio));
        }else return json_encode($responseSession);
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
        }else return json_encode($responseSession);
    });

    // $app->post('/updateStateSocio', function(Request $request, Response $response){
    //     $responseSession = ctr_usuarios::validateSession();
    //     if($responseSession->result == 2){
    //         $data = $request->getParams();
    //         return json_encode(ctr_usuarios::updateStateSocio());
    //     }else return json_encode($responseSession);
    // });

    $app->post('/notificarSocioCuota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idSocio = $data['idSocio'];
            return json_encode(ctr_usuarios::notificarSocioCuota($idSocio));
        }else return json_encode($responseSession);
    });

    $app->post('/getSociosPagina', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $lastId = $data['lastId'];
            $estado= $data['estado'];
            $textToSearch = $data['textToSearch'];
            $tipoCliente = $data['tipo'];
            $deudor = $data['deudor'];


            return json_encode(ctr_usuarios::getSociosPagina($lastId, $estado, $textToSearch, $tipoCliente, $deudor));
        }else return json_encode($responseSession);
    });

    $app->post('/getCuotasVencidas', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $lastId = $data['lastId'];
            $textToSearch = $data['textToSearch'];
            return json_encode(ctr_usuarios::getCuotasVencidas($lastId, $textToSearch));
        }else return json_encode($responseSession);
    });

    $app->post('/saveFile', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $category = $data['category'];
            $idCategory = $data['idCategory'];
            return json_encode(ctr_historiales::saveFile($category, $idCategory));
        }
        else return json_encode($responseSession);
    });



    $app->post('/saveFileLocal', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $category = $data['category'];
            $idCategory = $data['idCategory'];
            $filename = $data['filename'];
            $filesize = $data['filesize'];
            $chunksize = $data['chunksize'];
            $currentsize = $data['currentsize'];
            return json_encode(ctr_historiales::saveFileLocal($category, $idCategory, $filename, $filesize, $chunksize, $currentsize));
        }
        else return json_encode($responseSession);
    });

    //descarga el archivo
    $app->get('/descargar/{id}', function(Request $request, Response $response, $args) {
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $ctr_historiales = new ctr_historiales();
            $fichero = $ctr_historiales->getFileById( $args['id'] );
            $content = $response->getBody();
            $content->write($fichero->objectResult->archivo);

            return $response
                ->withHeader('Content-Type', '*/*')
                //->withHeader('Content-Transfer-Encoding', 'binary')
                ->withHeader('Content-Disposition', 'inline; filename="' . $fichero->objectResult->nombre . '"');
                //->withHeader('Expires', '0')
                //->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                //->withHeader('Pragma', 'public');
        }else return $response->withRedirect($request->getUri()->getBaseUrl());
    });

    $app->post('/getSocioDataByMacota', function(Request $request, Response $response){
        $responseSession = ctr_usuarios::validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $idMascota = $data['id'];

            $idSocio = null;
            $responseGetSocio = null;

            $responseData = ctr_mascotas::getMascotaSocio($idMascota);
            if ( $responseData->result == 2 ){
                $idSocio = $responseData->objectResult->idSocio;
            }

            if ($idSocio){
                $responseGetSocio = ctr_usuarios::getSocioWithMascotaToShow($idSocio);
            }

            return json_encode($responseGetSocio);
        }
        else return json_encode($responseSession);
    });

    $app->post('/searchClientByName', function(Request $request, Response $response) use ($userController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $value = $data["value"];
            return json_encode($userController->searchClientByName($value));
        }
        else return json_encode($responseSession);
    });



    $app->post('/whatsappGetNewQr', function(Request $request, Response $response) use ($userController, $whatsappClass){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){

            $data = 'id='.WHATSAPP_API_USER.'&token='.TOKEN_API;
            $path = 'client/qr';

            //return json_encode($whatsappClass->nuevoQr($value));
            return $whatsappClass->nuevoQr($path, $data);
        }
        else return json_encode($responseSession);
    });



    $app->post('/getAllWhatsappClientByType', function(Request $request, Response $response) use ($userController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){
            $data = $request->getParams();
            $type = $data['type'];
            return json_encode($userController->getAllWhatsappClientByType($type));
        }
        else return json_encode($responseSession);
    });



    $app->post('/enviarWhatsapp', function(Request $request, Response $response) use ($userController, $whatsappClass){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){

            $data = $request->getParams();
            $content = $data['message'];
            $phone = $data['to'];

            $path = 'message/txt';

            return json_encode($whatsappClass->enviarWhatsapp($path, $content, $phone));
        }
        else return json_encode($responseSession);
    });


    $app->post('/getFacturasPendientesCliente', function(Request $request) use ($userController){
        $responseSession = $userController->validateSession();
        if($responseSession->result == 2){

            $data = $request->getParams();
            $idClient = $data['idClient'];


            return json_encode($userController->getFacturasPendientesCliente($idClient));
        }
        else return json_encode($responseSession);
    });
}
?>