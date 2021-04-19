const urlBase = '/veterinarianan/public';

function fijarCostoCuota(){
	$("#modalFijarCuota").modal('hide');
	var cuotaUno = document.getElementById("inpCuotaUna").value || null;
	var cuotaDos = document.getElementById("inpCuotaDos").value || null;
	var cuotaExtra = document.getElementById("inpCuotaExtra").value || null;

	if(!validarDatosCuota(cuotaUno, cuotaDos, cuotaExtra)){
		$.ajax({
			async: false,
			url: urlBase + "/updateCuotaSocio",
			type: "POST",
			data: {
				cuotaUna: cuotaUno,
				cuotaDos: cuotaDos,
				cuotaExtra: cuotaExtra
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('sucess', response.mensaje, response.enHistorial, "Modificar cuota");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Modificar cuota");
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}

}

function validarDatosCuota(cuotaUno, cuotaDos, cuotaExtra){
	var conError  = false;
	var mensajeError = "";
	var uno = parseInt(cuotaUno);
	var dos = parseInt(cuotaDos);
	var extra = parseInt(cuotaExtra);

	if(uno == null){
		conError = true;
		mensajeError = "No puede fijar las nuevas cuotas sin especificar la cuota para socios con una mascota.";
	}else if(dos == null ){
		conError = true;
		mensajeError = "No puede fijar las nuevas cuotas sin especificar la cuota para socios con dos mascota.";
	}else if(extra == null){
		conError = true;
		mensajeError = "No puede fijar las nuevas cuotas sin especificar la cuota para socios con más de dos mascota.";
	}else if(uno >= dos){
		conError = true;
		mensajeError = "Esta fijando una cuota por mascota única mayor a la cuota por dos mascotas.";
	}else if(extra > uno){
		conError = true;
		mensajeError = "El valor para la cuota extra no puede superar el valor de la cuota para una mascota";
	}else if(extra > dos){
		conError = true;
		mensajeError = "El valor para la cuota extra no puede superar el valor de la cuota para dos mascotas";
	}

	if(conError){
		showReplyMessage('warning', mensajeError, null, "Modificar cuota");
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}
	return conError;
}

function fijarPassAdministrador(){
	$('#modalModificarPass').modal('hide');

	var passActual = document.getElementById('inpPassActual').value || null;
	var pass1 = document.getElementById('inpPass1').value || null;
	var pass2 = document.getElementById('inpPass2').value || null;

	if(!validarDatosPassAdministrador(passActual, pass1, pass2)){
		$.ajax({
			async: false,
			url: urlBase + "/updatePassAdministrador",
			type: "POST",
			data: {
				passActual: passActual,
				pass1: pass1,
				pass2: pass2
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('sucess', response.mensaje,  response.enHistorial, "Modificar contraseña");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Modificar contraseña");
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}
}

function validarDatosPassAdministrador(passActual, pass1, pass2){
	var conError = false;
	var mensajeError = "";

	if(passActual == null){
		conError = true;
		mensajeError = "Para modificar la contraseña de administrador debe ingresar la contraseña actual.";
	}else if(pass1 == null){
		conError = true;
		mensajeError = "Para modificar la contraseña actual debe ingresar una nueva contraseña.";
	}else if(pass2 == null){
		conError = true;
		mensajeError = "Para modificar la contraseña actual debe repetir la nueva contraseña.";
	}else if(pass2.length < 5 || pass1.length < 5 || passActual.length < 5 ){
		conError = true;
		mensajeError = "El sistema no admite contraseñas con menos de 5 caracteres.";
	}else if(pass1 != pass2){
		conError = true;
		mensajeError = "La nueva contraseña y su confirmación deben coincidir para modificar la contraseña actual.";
	}else if(pass1 == passActual){
		conError = true;
		mensajeError = "Esta intentando asignar la contraseña actual como la nueva contraseña, esta operación no se realizará.";
	}

	if(conError){
		showReplyMessage('warning', mensajeError, null, "Modificar contraseña");
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}
	return conError;
}

function selectUsuarioModificar(btn){
	var idUsuario = btn.id;

	$.ajax({
		async: false,
		url: urlBase + "/getUsuario",
		type: "POST",
		data: {
			idUsuario: idUsuario
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response){
				document.getElementById('cuentaUsuario').value = response.nombre;
				document.getElementById('emailUsuario').value = response.email;
				document.getElementById('titleNuevoUsuario').innerHTML = "Modificar usuario";
				document.getElementById('btnNuevoUsuario').innerHTML = "Modificar usuario";
				document.getElementById('btnCancelarUsuario').style.display = "block";
				document.getElementById('btnNuevoUsuario').name = response.idUsuario;

				$("#contenedorButtons").removeClass('justify-content-center');
				$("#contenedorButtons").addClass('justify-content-between');
				$("#btnCancelarUsuario").click(function(){
					document.getElementById('cuentaUsuario').value = null;
					document.getElementById('titleNuevoUsuario').innerHTML = "Agregar nuevo usuario";
					document.getElementById('btnNuevoUsuario').innerHTML = "Agregar usuario";
					$("#contenedorButtons").removeClass('justify-content-between');
					$("#contenedorButtons").addClass('justify-content-center');
					document.getElementById('btnCancelarUsuario').style.display = "none";
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Seleccionar usuario");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}

function filtrarOperacion(btn){
	var emailExp = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3,4})+$/;
	var operacion = btn.innerHTML;

	var cuenta = document.getElementById('cuentaUsuario').value || null;
	var email = document.getElementById('emailUsuario').value || null;

	var conErro = false;
	var mensajeError = "";
	if(cuenta == null){
		conErro = true;
		mensajeError = operacion + " requiere que ingrese un nombre para su cuenta de usuario";
	}else if(cuenta.length < 4){
		conErro = true;
		mensajeError = operacion + " requiere que el nombre de la cuenta de usuario tenga 4 caracteres o más.";
	}

	if(email != null){
		if(emailExp.test(email)){
			conError = true;
			mensajeError = "El email ingresado no es valido porfavor verifiquelo.";
		}
	}
	if(conErro){
		showReplyMessage('warning', mensajeError, null, operacion);
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}else{
		if(operacion == "Agregar usuario"){
			agregarUsuario(cuenta, email);
		}else if(operacion == "Modificar usuario"){
			modificarUsuario(btn.name , cuenta, email);
		}
	}
}

function agregarUsuario(cuenta, email){
	$.ajax({
		async: false,
		url: urlBase + "/insertNewUsuario",
		type: "POST",
		data: {
			nombre: cuenta,
			email: email
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno){
				showReplyMessage('success', response.mensaje, response.enHistorial, "Agregar usuario");
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Agregar usuario");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}

function modificarUsuario(idUsuario, cuenta, email){
	console.log(idUsuario)
	$.ajax({
		async: false,
		url: urlBase + "/updateUsuario",
		type: "POST",
		data: {
			idUsuario: idUsuario,
			nombre: cuenta,
			email: email
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno){
				showReplyMessage('success', response.mensaje, response.enHistorial, "Modificar usuario");
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Modificar usuario");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}

function fijarPlazoDeuda(){
	var plazoDeuda = document.getElementById('inpPlazoDeuda').value || null;

	if(plazoDeuda == null){
		showReplyMessage('warning', "Debe ingresar un plazo para poder modificar este dato, no puede mantenerse este campo vacío.", null,"Plazo deuda");
	}else if(plazoDeuda < 31){
		showReplyMessage('warning', "El plazo de deuda no puede ser menor a un 31 días.",null, "Plazo deuda");
	}else{
		$.ajax({
			async: false,
			url: urlBase + "/updatePlazoDeuda",
			type: "POST",
			data: {
				plazoDeuda: plazoDeuda
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial, "Plazo deuda");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Plazo deuda");
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}
}