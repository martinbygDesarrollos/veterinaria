function operacionVacuna(buttonOp){

	if(buttonOp.name == "ModificarVacuna"){
		document.getElementById("modalTituloVacuna").innerHTML = "Modificar vacuna";
		document.getElementById("modalButtonVacuna").innerHTML = "Modificar";
		$("#modalButtonVacuna").click(function(){
			modificarVacuna(buttonOp.id);
		});
		precargarInformacionVacuna(buttonOp.id);
	}else if(buttonOp.name == "AgregarVacuna"){
		let date = new Date()
		let day = date.getDate()
		let month = date.getMonth() + 1
		let year = date.getFullYear()

		if(month < 10) month = "0" + month;
		if(day < 10) day = "0" + day;

		document.getElementById('inpNombreVacuna').value = null;
		document.getElementById('inpIntervaloVacuna').value = null;
		document.getElementById('inpPrimerDosisVacuna').value = `${year}-${month}-${day}`;
		document.getElementById('inpObservacionesVacuna').value = null;
		document.getElementById("modalTituloVacuna").innerHTML = "Agregar Vacuna";
		document.getElementById("modalButtonVacuna").innerHTML = "Agregar";
		$("#modalButtonVacuna").click(function(){
			aplicarNuevaVacunaMascota(buttonOp.id);
		});
		$("#modalNuevaVacuna").modal();
	}

}

function precargarInformacionVacuna(idVacunaMascota){
	$.ajax({
		async: false,
		url: urlBase + "/getVacunaMascota",
		type: "POST",
		data: {
			idVacunaMascota: idVacunaMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ");
			if(response){
				document.getElementById('inpNombreVacuna').value = response.nombreVacuna;
				document.getElementById('inpIntervaloVacuna').value = response.intervaloDosis;
				var fechaToset = response.fechaUltimaDosis.split('/');
				document.getElementById('inpPrimerDosisVacuna').value = fechaToset[2] + "-" + fechaToset[1] + "-"+ fechaToset[0];
				document.getElementById('inpObservacionesVacuna').value = response.observacion;
				$("#modalNuevaVacuna").modal();
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Obtener vacuna");
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

function aplicarNuevaVacunaMascota(idMascota){
	$('#modalNuevaVacuna').modal('hide');

	var nombreVacuna = document.getElementById('inpNombreVacuna').value || null;
	var intervalo = document.getElementById('inpIntervaloVacuna').value || null;
	var fechaDosis = document.getElementById('inpPrimerDosisVacuna').value || null;
	var observaciones = document.getElementById('inpObservacionesVacuna').value || null;

	if(!validarDatosNuevaVacuna(nombreVacuna, intervalo, fechaDosis, "Vacunar mascota")){

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
				console.log("response SUCCESS: ");
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
				showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}
}

function modificarVacuna(idVacunaMascota){
	$('#modalNuevaVacuna').modal('hide');

	var nombreVacuna = document.getElementById('inpNombreVacuna').value || null;
	var intervalo = document.getElementById('inpIntervaloVacuna').value || null;
	var fechaUltimaDosis = document.getElementById('inpPrimerDosisVacuna').value || null;
	var observaciones = document.getElementById('inpObservacionesVacuna').value || null;

	if(!validarDatosNuevaVacuna(nombreVacuna, intervalo, fechaUltimaDosis, "Modificar vacuna")){

		$.ajax({
			async: false,
			url: urlBase + "/updateVacunaMascota",
			type: "POST",
			data: {
				idVacunaMascota: idVacunaMascota,
				nombre: nombreVacuna,
				intervalo: intervalo,
				fechaUltimaDosis: fechaUltimaDosis,
				observaciones: observaciones
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ");
				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial, "Modificar mascota");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Modificar mascota");
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

function validarDatosNuevaVacuna(nombreVacuna, intervalo, fechaDosis, titulo){
	conError = false;
	mensajeError = "";

	if(nombreVacuna == null){
		conError = true;
		mensajeError = "El nombre de la vacuna no puede ser ingresado vacío.";
	}else if(nombreVacuna.length < 4 ){
		conError = true;
		mensajeError = "El campo nombre vacuna debe tener al menos 4 caracteres para ser considerado valido.";
	}else if(intervalo == null){
		conError = true;
		mensajeError = "El campo intervalo no puede ser ingresado vacío.";
	}else if(fechaDosis == null){
		conError = true;
		mensajeError = "Para aplicar una nueva vacuna debe ingresar una fecha de dosis, por defecto se toma la fecha actual.";
	}else if(fechaDosis < new Date()){
		conError = true;
		mensajeError = "La fecha de la ultima dosis no puede ser menor a la fecha actual.";
	}

	if(conError){
		showReplyMessage('warning', mensajeError, null, titulo);
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
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
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
					"<td class='text-justify'>" + vacunas[i].observacion + "</td>" +
					"<td class='text-center'>" + vacunas[i].intervaloDosis +"</td>" +
					"<td class='text-center'>" + vacunas[i].numDosis +"</td>" +
					"<td class='text-center'> <button id='" + vacunas[i].idVacunaMascota + "' name='" + vacunas[i].nombreVacuna +"' class='btn btn-success btn-sm'  onclick='abrirModalAplicarDosis(this)'><i class='fas fa-syringe'></i></button> </td>" +
					"<td class='text-center'> <button class='btn btn-success btn-sm' id='" + vacunas[i].idVacunaMascota + "' name='ModificarVacuna' onclick='operacionVacuna(this)'><i class='fas fa-edit'></i></button></td></tr>"
					$('#tbodyVacunas').append(fila);
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