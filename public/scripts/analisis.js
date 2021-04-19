
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
					showReplyMessage('success', response.mensaje, response.enHistorial, "Nuevo análisis");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Nuevo análisis");
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

function validarInformacionAnalisis(nombre, fecha, detalle){
	var conError = false;
	var mensajeError = "";

	if(!nombre){
		conError = true;
		mensajeError = "El nombre del análisis no puede ser ingresado nulo.";
	}else if(nombre.length < 5){
		conError = true;
		mensajeError = "El nombre del análisis debe tener al menos 5 caracteres para ser considerado valido.";
	}else if(!fecha){
		conError = true;
		mensajeError = "La fecha del análisis no puede ser ingresada nula.";
	}else if(fecha  >= new Date()){
		conError= true;
		mensajeError = "La fecha de realización del análisis no puede superar a la fecha actual.";
	}

	if(conError){
		showReplyMessage('warning', mensajeError, null,  "Nuevo análisis");
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
				showReplyMessage('danger', response.mensajeError, null, "Nuevo análisis");
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
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}

function operacionAnalisis(buttonOp){
	if(buttonOp.name == "ModificarAnalisis"){
		document.getElementById("tituloAgregarAnalisis").innerHTML = "Modificar Análisis";
		document.getElementById("modalButtonAgregarAnalisisMascota").innerHTML = "Modificar";
		precargarInformacionAnalisis(buttonOp.id);
		$("#modalButtonAgregarAnalisisMascota").click(function(){
			modificarAnalisis(buttonOp.id);
		});
	}else if(buttonOp.name == "AgregarAnalisis"){
		let date = new Date()
		let day = date.getDate()
		let month = date.getMonth() + 1
		let year = date.getFullYear()

		if(month < 10) month = "0" + month;
		if(day < 10) day = "0" + day;

		var nombre = document.getElementById('inpNombreAnalisis').value = null;
		var fecha = document.getElementById('inpFechaAnalisis').value = `${year}-${month}-${day}`;
		var detalle = document.getElementById('inpDetalleAnalisis').value = null;
		var resultado = document.getElementById('inpResultadoAnalisis').value = null;

		document.getElementById("tituloAgregarAnalisis").innerHTML = "Agregar análisis";
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
				showReplyMessage('danger', response.mensajeError, null, "Obtener Análisis");
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
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
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
					showReplyMessage('success', response.mensaje, response.enHistorial, "Modificar análisis");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Modificar análisis");
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}
}

let menorIdAnalisis = 0;
let maxIdAnalisis = 0;

function cargarTablaAnalisis(idMascota){
	$.ajax({
		async: false,
		url: urlBase + "/getAnalisisPagina",
		type: "POST",
		data: {
			ultimoID: menorIdAnalisis,
			idMascota: idMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorIdAnalisis = response.min;
			maxIdAnalisis = response.max;
			var analisis = response.analisis;
			$('#tbodyAnalisis').empty();
			if(analisis.length == 0){
				document.getElementById("noHayResultadosAnalisisMensaje").style.display = "block";
				document.getElementById("irAdelantePaginaAnalisis").style.display = "none";
				document.getElementById("irAtrasPaginaAnalisis").style.display = "none";
			}else{
				if(analisis.length < 5){
					document.getElementById("irAdelantePaginaAnalisis").style.display = "none";
				}else{
					document.getElementById("irAtrasPaginaAnalisis").style.display = "block";
					document.getElementById("irAdelantePaginaAnalisis").style.display = "block";
				}
				document.getElementById("noHayResultadosAnalisisMensaje").style.display = "none";
				for(var i = 0; i < analisis.length; i ++ ){
					var fila = "<tr><td class='text-center'>" + analisis[i].fecha +"</td>" +
					"<td class='text-center'>" + analisis[i].nombre +"</td>" +
					"<td class='text-justify'>" + analisis[i].resultado +"</td>" +
					"<td class='text-center'>" +
					"<button class='btn btn-success btn-sm' name='ModificarAnalisis' id='" + analisis[i].idAnalisis + "'  onclick='operacionAnalisis(this)'><i class='fas fa-edit'></i></button></td>" +
					"<td class='text-center'>" +
					"<button class='btn btn-info btn-sm' id='" + analisis[i].idAnalisis + "'  onclick='verDetalleAnalsisi(this)'><i class='fas fa-eye'></i></button></td></tr>"
					$('#tbodyAnalisis').append(fila);
				}
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

function paginaPosteriorAnalisis(){
	var idMascota = document.getElementById('idMascotaSeleccionada').value;
	cargarTablaAnalisis(idMascota);
}

function paginaAnteriorAnalisis(){
	var idMascota = document.getElementById('idMascotaSeleccionada').value;
	if(menorIdAnalisis != 0){
		menorIdAnalisis = parseInt(maxIdAnalisis) + 5;
		cargarTablaAnalisis(idMascota);
	}
}