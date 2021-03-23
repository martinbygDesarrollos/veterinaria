const urlBase = '/veterinaria/public';

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
					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalTituloRetorno').innerHTML = "Modificar cuota";
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					$('#modalColorRetorno').removeClass('alert-success');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-danger');
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar cuota";
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar cuota";
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
		$("#modalRetorno").modal();
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

		$("#modalColorRetorno").addClass('alert-warning');
		document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar cuota";
		document.getElementById("modalMensajeRetorno").innerHTML = mensajeError;
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
		$("#modalRetorno").modal();
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
					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalTituloRetorno').innerHTML = "Modificar contraseña";
					document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					$('#modalColorRetorno').removeClass('alert-success');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-danger');
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar contraseña";
					document.getElementById("modalMensajeRetorno").innerHTML = response.mensajeError;
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}


			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar cuota";
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
		$("#modalRetorno").modal();
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

		$("#modalColorRetorno").addClass('alert-warning');
		document.getElementById('modalTituloRetorno').innerHTML = "Error: Modifcar contraseña administrador";
		document.getElementById("modalMensajeRetorno").innerHTML = mensajeError;
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
		$("#modalRetorno").modal();
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
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Seleccionar usuario";
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensajeError;
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
				$("#modalRetorno").modal();
			}


		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			document.getElementById('modalTituloRetorno').innerHTML = "Error: Seleccionar usuario";
			$('#modalColorRetorno').removeClass('alert-success');
			$('#modalColorRetorno').removeClass('alert-warning');
			$('#modalColorRetorno').addClass('alert-danger');
			$("#modalRetorno").modal();
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
			$("#modalRetorno").modal();
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

		$("#modalColorRetorno").addClass('alert-warning');
		document.getElementById('modalTituloRetorno').innerHTML = "Error: " + operacion;
		document.getElementById("modalMensajeRetorno").innerHTML = mensajeError;
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
		$("#modalRetorno").modal();
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
				$('#modalColorRetorno').removeClass('alert-danger');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-success');
				document.getElementById('modalTituloRetorno').innerHTML = "Agregar usuario";
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Agregar usuario";
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensajeError;
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});

			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar usuario";
			$('#modalColorRetorno').removeClass('alert-success');
			$('#modalColorRetorno').removeClass('alert-warning');
			$('#modalColorRetorno').addClass('alert-danger');
			$("#modalRetorno").modal();
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
	$("#modalRetorno").modal();
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
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').removeClass('alert-danger');
				$('#modalColorRetorno').addClass('alert-success');
				document.getElementById('modalTituloRetorno').innerHTML = "Modificar usuario";
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar usuario";
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensajeError;
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});

			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar usuario";
			$('#modalColorRetorno').removeClass('alert-success');
			$('#modalColorRetorno').removeClass('alert-warning');
			$('#modalColorRetorno').addClass('alert-danger');
			$("#modalRetorno").modal();
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
	$("#modalRetorno").modal();
}