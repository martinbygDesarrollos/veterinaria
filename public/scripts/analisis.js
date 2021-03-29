
function agregarNuevoAnalisis(idMascota){
	$("#modalAgregarAnalisisMascota").modal('hide');
	var nombre = document.getElementById('inpNombreAnalisis').value || null;
	var fecha = document.getElementById('inpFechaAnalisis').value || null;
	var detalle = document.getElementById('inpDetalleAnalisis').value || null;
	var resultado = document.getElementById('inpResultadoAnalisis').value || null;

	if(!validarInformacionAnalisis(nombre, fecha, detalle)){
		$.ajax({
			async: false,
			url: urlBase + "/insertNewAnalisis",
			type: "POST",
			data: {
				idMascota: idMascota,
				nombreAnalisis: nombre,
				fechaAnalisis: fecha,
				detalleAnalisis: detalle,
				resultadoAnalisis: resultado
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial, "Nuevo analisis");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Nuevo analisis");
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

function validarInformacionAnalisis(nombre, fecha, detalle){
	var conError = false;
	var mensajeError = "";

	if(!nombre){
		conError = true;
		mensajeError = "El nombre del analisis no puede ser ingresado nulo.";
	}else if(nombre.length < 5){
		conError = true;
		mensajeError = "El nombre del analisis debe tener al menos 5 caracteres para ser considerado valido.";
	}else if(!fecha){
		conError = true;
		mensajeError = "La fecha del analisis no puede ser ingresada nula.";
	}else if(fecha  >= new Date()){
		conError= true;
		mensajeError = "La fecha de realización del analisis no puede superar a la fecha actual.";
	}

	if(conError){
		showReplyMessage('warning', mensajeError, null,  "Nuevo analisis");
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}
	return conError;
}

function verDetalleAnalsisi(btn){
	var idAnalisis = btn.id;
	console.log(btn.id)
	$.ajax({
		async: false,
		url: urlBase + "/getAnalisis",
		type: "POST",
		data: {
			idAnalisis: idAnalisis
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno == false){
				showReplyMessage('danger', response.mensajeError, null, "Nuevo analisis");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}else{
				document.getElementById('nombreVerAnalisis').innerHTML = response.nombre;
				document.getElementById('fechaVerAnalisis').innerHTML = response.fecha;
				document.getElementById('detalleVerAnalisis').innerHTML = response.detalle;
				document.getElementById('resultadoVerAnalisis').innerHTML = response.resultado;
				$('#modalVerAnalisisMascota').modal();
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

function operacionAnalisis(buttonOp){
	if(buttonOp.name == "ModificarAnalisis"){
		document.getElementById("tituloAgregarAnalisis").innerHTML = "Modificar Analisis";
		document.getElementById("modalButtonAgregarAnalisisMascota").innerHTML = "Modificar";
		precargarInformacionAnalisis(buttonOp.id);
		$("#modalButtonAgregarAnalisisMascota").click(function(){
			modificarAnalisis(buttonOp.id);
		});
	}else if(buttonOp.name == "AgregarAnalisis"){
		document.getElementById("tituloAgregarAnalisis").innerHTML = "Agregar analisis";
		document.getElementById("modalButtonAgregarAnalisisMascota").innerHTML = "Agregar";
		$("#modalButtonAgregarAnalisisMascota").click(function(){
			agregarNuevoAnalisis(buttonOp.id);
		});
	}
	$("#modalAgregarAnalisisMascota").modal();
}

function precargarInformacionAnalisis(idAnalisis){
	$.ajax({
		async: false,
		url: urlBase + "/getAnalisis",
		type: "POST",
		data: {
			idAnalisis: idAnalisis
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno == false){
				showReplyMessage('danger', response.mensajeError, null, "Obtener Analisis");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}else{
				document.getElementById('inpNombreAnalisis').value = response.nombre;
				document.getElementById('inpDetalleAnalisis').value = response.detalle;
				document.getElementById('inpResultadoAnalisis').value = response.resultado;
				var fechaToset = response.fecha.split('/');
				document.getElementById('inpFechaAnalisis').value = fechaToset[2] + "-" + fechaToset[1] + "-"+ fechaToset[0];
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

function modificarAnalisis(idAnalisis){
	$("#modalAgregarAnalisisMascota").modal('hide');

	var nombre = document.getElementById('inpNombreAnalisis').value || null;
	var fecha = document.getElementById('inpFechaAnalisis').value || null;
	var detalle = document.getElementById('inpDetalleAnalisis').value || null;
	var resultado = document.getElementById('inpResultadoAnalisis').value || null;

	if(!validarInformacionAnalisis(nombre, fecha, detalle)){
		$.ajax({
			async: false,
			url: urlBase + "/updateAnalisis",
			type: "POST",
			data: {
				idAnalisis: idAnalisis,
				nombreAnalisis: nombre,
				fechaAnalisis: fecha,
				detalleAnalisis: detalle,
				resultadoAnalisis: resultado
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial, "Modificar analisis");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Modificar analisis");
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