const urlBase = '/veterinarianan/public';

function openModalVencimiento(btn){
	document.getElementById("idSocioVencimiento").value = btn.id;
	document.getElementById("idMascotaVencimiento").value = btn.name;
	$('#modalNotificacion').modal();
}

function notificarVencimientosVacuna(){
	var idSocio = document.getElementById("idSocioVencimiento").value;
	var idMascota = document.getElementById("idMascotaVencimiento").value;

	$('#modalNotificacion').modal('hide');

	$.ajax({
		async: false,
		url: urlBase + "/notificarSocioVacuna",
		type: "POST",
		data: {
			idSocio: idSocio,
			idMascota: idMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno){
				showReplyMessage('success', response.mensaje, response.enHistorial, "Notificar socio");
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Notificar socio");
			}
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});;
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

function openModalVencimientoCuota(btn){
	document.getElementById("idSocioVencimiento").value = btn.id;
	$('#modalNotificacionCuota').modal();
}

function notificarVencimientoCuota(){
	var idSocio = document.getElementById("idSocioVencimiento").value;
	$('#modalNotificacionCuota').modal('hide');

	$.ajax({
		async: false,
		url: urlBase + "/notificarSocioCuota",
		type: "POST",
		data: {
			idSocio: idSocio
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno){
				showReplyMessage('success', response.mensaje, response.enHistorial, "Notificar socio");
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Notificar socio");
			}
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});;
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

let menorIDVacuna = 0;
let maxIDVacuna = 0;

function cargarTablaVencimientoVacunas(){
	$.ajax({
		async: false,
		url: urlBase + "/getVencimientosVacunaPagina",
		type: "POST",
		data: {
			ultimoID: menorIDVacuna,
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorIDVacuna = response.min;
			maxIDVacuna = response.max;
			$('#tbodyVencimientosVacuna').empty();
			console.log(response)
			var vencimientos = response.vencimientos;
			for(var i = 0; i < vencimientos.length; i ++ ){
				var fila = "<tr><td class='text-center'>"+ vencimientos[i].fechaProximaDosis +"</td>" +
				"<td class='text-center'><a class='btn btn-info btn-sm' href='" + urlBase + "/verMascota/" + vencimientos[i].idMascota + "'>" + vencimientos[i].nombreMascota +"</a></td>" +
				"<td class='text-center'><a class='btn btn-info btn-sm' href='" + urlBase + "/verSocio/" + vencimientos[i].socio.idSocio + "'>" + vencimientos[i].socio.nombre +"</a></td>" +
				"<td class='text-center'>" + vencimientos[i].socio.telefono +"</td>" +
				"<td class='text-center'><button class='btn btn-info btn-sm' id='"+ vencimientos[i].socio.idSocio + "' name='"+ vencimientos[i].idMascota + "' onclick='openModalVencimiento(this)'>Enviar correo</button></td></tr>"
				$('#tbodyVencimientosVacuna').append(fila);
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

function paginaPosteriorVencimientoVacunas(){
	cargarTablaVencimientoVacunas();
}

function paginaAnteriorVencimientoVacunas(){
	if(menorIDVacuna != 0){
		menorIDVacuna = parseInt(maxIDVacuna) + 10;
		cargarTablaVencimientoVacunas();
	}
}

let menorIDCuota = 0;
let maxIDCuota = 0;

function cargarTablaVencimientoCuota(){
	$.ajax({
		async: false,
		url: urlBase + "/getVencimientosCuotaPagina",
		type: "POST",
		data: {
			ultimoID: menorIDCuota,
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorIDCuota = response.min;
			maxIDCuota = response.max;
			$('#tbodyVencimientosCuota').empty();
			console.log(response)
			var vencimientos = response.vencimientos;
			for(var i = 0; i < vencimientos.length; i ++ ){
				var fila = "<tr><td class='text-center'>"+ vencimientos[i].fechaUltimaCuota +"</td>" +
				"<td class='text-center'>" + vencimientos[i].fechaPago + "</td>" +
				"<td class='text-center'><a class='btn btn-info btn-sm' href='" + urlBase + "/verSocio/" + vencimientos[i].idSocio + "'>" + vencimientos[i].nombre +"</a></td>" +
				"<td class='text-center'>" + vencimientos[i].telefono +"</td>" +
				"<td class='text-center'><button class='btn btn-info btn-sm' id='"+ vencimientos[i].idSocio + "' onclick='openModalVencimientoCuota(this)'>Enviar correo</button></td></tr>"
				$('#tbodyVencimientosCuota').append(fila);
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

function paginaPosteriorVencimientoCuota(){
	cargarTablaVencimientoCuota();
}

function paginaAnteriorVencimientoCuota(){
	if(menorIDCuota != 0){
		menorIDCuota = parseInt(maxIDCuota) + 10;
		cargarTablaVencimientoCuota();
	}
}

function buscarSocioVencimientoCuota(inputSearch){
	var aBuscar = inputSearch.value;
	if(aBuscar.length > 3){
		document.getElementById("irAtrasPaginaVencimientosCuota").style.visibility = "hidden";
		document.getElementById("irAdelantePaginaVencimientosCuota").style.visibility = "hidden";
		$('#tbodyVencimientosCuota').empty();
		$.ajax({
			async: false,
			url: urlBase + "/buscadorDeSociosVencimientoCuota",
			type: "POST",
			data: {
				nombreSocio: aBuscar,
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				var vencimientos = response;
				if(vencimientos.length == 0){
					document.getElementById("noHayResultadosMensaje").style.display = "block";
				}else{
					document.getElementById("noHayResultadosMensaje").style.display = "none";
					for(var i = 0; i < vencimientos.length; i ++ ){
						var fila = "<tr><td class='text-center'>"+ vencimientos[i].fechaUltimaCuota +"</td>" +
						"<td class='text-center'>" + vencimientos[i].fechaPago + "</td>" +
						"<td class='text-center'><a class='btn btn-info btn-sm' href='" + urlBase + "/verSocio/" + vencimientos[i].idSocio + "'>" + vencimientos[i].nombre +"</a></td>" +
						"<td class='text-center'>" + vencimientos[i].telefono +"</td>" +
						"<td class='text-center'><button class='btn btn-info btn-sm' id='"+ vencimientos[i].idSocio + "' onclick='openModalVencimientoCuota(this)'>Enviar correo</button></td></tr>"
						$('#tbodyVencimientosCuota').append(fila);
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
	}else{
		if(aBuscar.length == 0){
			menorID = 0;
			maxID = 0;
			cargarTablaVencimientoCuota();
			document.getElementById("noHayResultadosMensaje").style.display = "none";
			document.getElementById("irAtrasPaginaVencimientosCuota").style.visibility = "visible";
			document.getElementById("irAdelantePaginaVencimientosCuota").style.visibility = "visible";
		}
	}
}