function operacionEnfermedad(buttonOp){
	if(buttonOp.name == "ModificarEnfermedad"){
		document.getElementById("modalTituloEnfermedad").innerHTML = "Modificar enfermedad";
		document.getElementById("modalButtonEnfermedad").innerHTML = "Modificar";
		precargarInformacionEnfermedad(buttonOp.id);
		$("#modalButtonEnfermedad").click(function(){
			modificarEnfermedad(buttonOp.id);
		});
	}else if(buttonOp.name == "AgregarEnfermedad"){
		document.getElementById("modalTituloEnfermedad").innerHTML = "Agregar enfermedad";
		document.getElementById("modalButtonEnfermedad").innerHTML = "Agregar";
		$("#modalButtonEnfermedad").click(function(){
			agregarEnfermedad(buttonOp.id);
		});
	}
	$("#modalNuevaEnfermedad").modal();
}

function precargarInformacionEnfermedad(idEnfermedad){
	$.ajax({
		async: false,
		url: urlBase + "/getEnfermedadMascota",
		type: "POST",
		data: {
			idEnfermedad: idEnfermedad,
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response){
				document.getElementById("inpNombreEnfermedad").value = response.nombreEnfermedad;
				var fechaToset = response.fechaDiagnostico.split('/');
				document.getElementById("inpFechaDiagnosticoEnfermedad").value = fechaToset[2] + "-" + fechaToset[1] + "-"+ fechaToset[0];
				document.getElementById("inpObservacionesEnfermedad").value = response.observaciones;
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Obtener enfermedad");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}

function modificarEnfermedad(idEnfermedad){
	$("#modalNuevaEnfermedad").modal('hide');

	var nombreEnfermedad = document.getElementById("inpNombreEnfermedad").value || null;
	var fechaEnfermedad = document.getElementById("inpFechaDiagnosticoEnfermedad").value;
	var observacionesEnfermedad = document.getElementById("inpObservacionesEnfermedad").value || null;

	if(!validarDatosEnfermedada(nombreEnfermedad, fechaEnfermedad, observacionesEnfermedad, "Modificar enfermedad")){

		$.ajax({
			async: false,
			url: urlBase + "/updateEnfermedadMascota",
			type: "POST",
			data: {
				idEnfermedad: idEnfermedad,
				nombreEnfermedad: nombreEnfermedad,
				fechaDiagnosticoEnfermedad: fechaEnfermedad,
				observacionesEnfermedad: observacionesEnfermedad
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial, "Modificar enfermedad");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Modificar enfermedad");
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}
}

function agregarEnfermedad(idMascota){

	$("#modalNuevaEnfermedad").modal('hide');

	var nombreEnfermedad = document.getElementById("inpNombreEnfermedad").value || null;
	var fechaEnfermedad = document.getElementById("inpFechaDiagnosticoEnfermedad").value || null;
	var observacionesEnfermedad = document.getElementById("inpObservacionesEnfermedad").value || null;

	if(!validarDatosEnfermedada(nombreEnfermedad, fechaEnfermedad, observacionesEnfermedad, "Nueva enfermedad")){
		$.ajax({
			async: false,
			url: urlBase + "/insertEnfermedadMascota",
			type: "POST",
			data: {
				idMascota: idMascota,
				nombreEnfermedad: nombreEnfermedad,
				fechaDiagnosticoEnfermedad: fechaEnfermedad,
				observacionesEnfermedad: observacionesEnfermedad
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial, "Nueva enfermedad");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Nueva enfermedad");
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}
}

function validarDatosEnfermedada(nombre, fecha, observaciones, tituloError){
	var conError = false;
	var mensajeError = "";

	if(nombre == null){
		conError = true;
		mensajeError = "No puede ingresar una enfermedad sin un nombre.";
	}else if(nombre.length < 5){
		conError = true;
		mensajeError = "El campo nombre en una nueva enfermedad debe contener al menos 5 caracteres para ser considerado valido.";
	}else if(fecha == null){
		conError = true;
		mensajeError = "No puede ingresar una enfermedad sin una fecha de diagnositico, por defecto el sistema proporciona la fecha de hoy.";
	}

	if(conError){
		showReplyMessage('warning', mensajeError, null, tituloError);
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}
	return conError;
}