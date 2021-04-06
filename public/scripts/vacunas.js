function aplicarNuevaVacunaMascota(btnId){
	$('#vacunasModal').modal('hide');

	var idMascota = btnId.id;
	var nombreVacuna = document.getElementById('inpNombreVacuna').value || null;
	var intervalo = document.getElementById('inpIntervaloVacuna').value || null;
	var fechaDosis = document.getElementById('inpPrimerDosisVacuna').value || null;
	var observaciones = document.getElementById('inpObservacionesVacuna').value || null;

	if(!validarDatosNuevaVacuna(nombreVacuna, intervalo, fechaDosis)){

		$.ajax({
			async: false,
			url: urlBase + "/aplicarNuevaVacunaMascota",
			type: "POST",
			data: {
				idMascota: idMascota,
				nombreVacuna: nombreVacuna,
				intervalo: intervalo,
				fechaDosis: fechaDosis,
				observaciones: observaciones
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial, "Vacunar mascota");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Vacunar mascota");
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

function validarDatosNuevaVacuna(nombreVacuna, intervalo, fechaDosis){
	conError = false;
	mensajeError = "";

	if(nombreVacuna == null){
		conError = true;
		mensajeError = "El nombre de la vacuna no puede ser ingresado nulo.";
	}else if(nombreVacuna.length < 4 ){
		conError = true;
		mensajeError = "El campo nombre vacuna debe tener al menos 4 caracteres para ser considerado valido.";
	}else if(intervalo == null){
		conError = true;
		mensajeError = "El campo intervalo no puede ser ingresado nulo.";
	}else if(fechaDosis == null){
		conError = true;
		mensajeError = "Para aplicar una nueva vacuna debe ingresar una fecha de dosis, por defecto se toma la fecha actual.";
	}else if(fechaDosis < new Date()){
		conError = true;
		mensajeError = "La fecha de la ultima dosis no puede ser menor a la fecha actual.";
	}

	if(conError){
		showReplyMessage('warning', mensajeError, null, "Vacunar mascota");
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}
	return conError;
}

function abrirModalAplicarDosis(btn){
	var idVacunaMascota = btn.id;
	var nombre = btn.name;

	var mensaje = "¿Seguro que desea aplicar una dosis de " + nombre + " en esta mascota?";
	document.getElementById('mensajeAplicarDosis').innerHTML = mensaje;
	document.getElementById('btnAplicarDosis').name = idVacunaMascota;
	$('#modalAplicarDosis').modal();
}

function aplicarDosisVacuna(btn){
	var idVacunaMascota = btn.name;
	$('#modalAplicarDosis').modal('hide');
	$.ajax({
		async: false,
		url: urlBase + "/aplicarDosisVacuna",
		type: "POST",
		data: {
			idVacunaMascota: idVacunaMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno){
				showReplyMessage('success', response.mensaje, response.enHistorial, "Aplicar dosis");
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Aplicar dosis");
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

let menorIdVacuna = 0;
let maxIdVacuna = 0;

function cargarTablaVacunas(idMascota){
	$.ajax({
		async: false,
		url: urlBase + "/getVacunasPagina",
		type: "POST",
		data: {
			ultimoID: menorIdVacuna,
			idMascota: idMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorIdVacuna = response.min;
			maxIdVacuna = response.max;
			var vacunas = response.vacunas;
			$('#tbodyVacunas').empty();
			if(vacunas.length == 0){
				document.getElementById("noHayResultadosVacunasMensaje").style.display = "block";
				document.getElementById("irAdelantePaginaVacunas").style.display = "none";
				document.getElementById("irAtrasPaginaVacunas").style.display = "none";
			}else{
				if(vacunas.length < 5){
					document.getElementById("irAdelantePaginaVacunas").style.display = "none";
				}else{
					document.getElementById("irAtrasPaginaVacunas").style.display = "block";
					document.getElementById("irAdelantePaginaVacunas").style.display = "block";
				}
				document.getElementById("noHayResultadosVacunasMensaje").style.display = "none";
				for(var i = 0; i < vacunas.length; i ++ ){
					var fila = "<tr><td class='text-center'>" + vacunas[i].fechaProximaDosis +"</td>" +
					"<td class='text-center'>" + vacunas[i].fechaUltimaDosis +"</td>" +
					"<td class='text-center'>" + vacunas[i].nombreVacuna + "</td>" +
					"<td class='text-center'>" + vacunas[i].intervaloDosis +"</td>" +
					"<td class='text-center'>" + vacunas[i].numDosis +"</td>" +
					"<td class='text-center'> <button id='" + vacunas[i].idVacunaMascota + "' name='" + vacunas[i].nombreVacuna +"' class='btn btn-success btn-sm'  onclick='abrirModalAplicarDosis(this)'><i class='fas fa-syringe'></i></button> </td>" +
					"</td></tr>"
					$('#tbodyVacunas').append(fila);
				}
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

function paginaPosteriorVacunas(){
	var idMascota = document.getElementById('idMascotaSeleccionada').value;
	cargarTablaVacunas(idMascota);
}

function paginaAnteriorVacunas(){
	var idMascota = document.getElementById('idMascotaSeleccionada').value;
	if(menorIdVacuna != 0){
		menorIdVacuna = parseInt(maxIdVacuna) + 5;
		cargarTablaVacunas(idMascota);
	}
}