const urlBase = '/veterinaria/public';

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
					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalTituloRetorno').innerHTML = "Vacunar mascota";
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					$('#modalColorRetorno').removeClass('alert-success');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-danger');
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Vacunar mascota";
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Vacunar mascota";
				document.getElementById("modalMensajeRetorno").innerHTML = "Ocurrio un error y no pudo comunicarse con el servidor, vuelva a intentarlo.";
				$('#modalColorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#buttonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
		$("#modalRetorno").modal();

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
		$("#modalColorRetorno").addClass('alert-warning');
		document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva vacuna";
		document.getElementById("modalMensajeRetorno").innerHTML = mensajeError;
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
		$("#modalRetorno").modal();
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
				$('#modalColorRetorno').addClass('alert-success');
				document.getElementById('modalTituloRetorno').innerHTML = "Vacunar mascota";
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{

				$('#modalColorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Vacunar mascota";
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}
			document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			$('#colorRetorno').removeClass('alert-success');
			$('#colorRetorno').addClass('alert-danger');
			$("#modalRetorno").modal();
			$("#buttonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
	$("#modalRetorno").modal();

}

function insertarHistoriaClinica(btn){

	var idMascota = btn.id;
	var motivoConsulta = document.getElementById('inpHistoriaMotivoConsulta').value || null;
	var diagnostico = document.getElementById('inpHistoriaDiagnostico').value || null;
	var observaciones = document.getElementById('inpHistoriaObservaciones').value || null;

	var alert  = document.getElementById('mensajeErrorHistoriaAlert');
	if(motivoConsulta == null){
		alert.innerHTML = "No puede ingresar una historia clinica sin un motivo de consulta.";
		alert.style.display = "block";
		return;
	}else if(motivoConsulta.length < 10) {
		alert.innerHTML = "El motivo de la consulta debe tener al menos 10 caracteres alfanuméricos para ser considerado valido.";
		alert.style.display = "block";
		return;
	}

	$("#modalNuevaHistoria").modal('hide');
	$.ajax({
		async: false,
		url: urlBase + "/insertHistoriaMascota",
		type: "POST",
		data: {
			idMascota: idMascota,
			motivoConsulta: motivoConsulta,
			diagnostico: diagnostico,
			observaciones: observaciones
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno){
				$('#modalColorRetorno').removeClass('alert-danger');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-success');
				document.getElementById('modalTituloRetorno').innerHTML = "Nueva historia clinica";
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva hisotira clinica";
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}
			document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			$('#colorRetorno').removeClass('alert-warning');
			$('#colorRetorno').removeClass('alert-success');
			$('#colorRetorno').addClass('alert-danger');
			$("#modalRetorno").modal();
			$("#buttonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
	$("#modalRetorno").modal();


}

function ocultarAlert(){
	var alert = document.getElementById('mensajeErrorHistoriaAlert');
	if(alert.style.display == "block")
		alert.style.display = "none";
}


function operacionEnfermedad(buttonOp){
	if(buttonOp.name == "ModificarEnfermedad"){
		document.getElementById("modalTituloEnfermedad").innerHTML = "Modificar enfermedad";
		document.getElementById("modalButtonEnfermedad").innerHTML = "Modificar";
		precargarInformacionEnfermedad(buttonOp.id);
		document.getElementById("fechaDiagnosticoEnfermedadActual").style.display = "block";
		$("#modalButtonEnfermedad").click(function(){
			modificarEnfermedad(buttonOp.id);
		});
	}else if(buttonOp.name == "AgregarEnfermedad"){
		document.getElementById("fechaDiagnosticoEnfermedadActual").style.display = "none";
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
				document.getElementById("inpFechaDiagnosticoEnfermedad").value = response.fechaDiagnostico;
				document.getElementById("inpObservacionesEnfermedad").value = response.observaciones;
				document.getElementById("fechaDiagnosticoEnfermedadActual").innerHTML = response.fechaDiagnostico;
			}else{
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Enfermedad";
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
				$("#modalRetorno").modal();
			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			document.getElementById('modalTituloRetorno').innerHTML = "Error: Enfermedad";
			$('#colorRetorno').removeClass('alert-warning');
			$('#colorRetorno').removeClass('alert-success');
			$('#colorRetorno').addClass('alert-danger');
			$("#modalRetorno").modal();
			$("#buttonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
			$("#modalRetorno").modal();
		},
	});
}

function modificarEnfermedad(idEnfermedad){
	$("#modalNuevaEnfermedad").modal('hide');

	var nombreEnfermedad = document.getElementById("inpNombreEnfermedad").value || null;
	var fechaEnfermedad = document.getElementById("inpFechaDiagnosticoEnfermedad").value || document.getElementById("fechaDiagnosticoEnfermedadActual").innerHTML;
	var observacionesEnfermedad = document.getElementById("inpObservacionesEnfermedad").value || null;
	if(!validarDatosEnfermedada(nombreEnfermedad, fechaEnfermedad, observacionesEnfermedad)){

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
					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalTituloRetorno').innerHTML = "Nueva enfermedad";
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					$('#modalColorRetorno').removeClass('alert-success');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-danger');
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva enfermedad";
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva enfermedad";
				$('#colorRetorno').removeClass('alert-warning');
				$('#colorRetorno').removeClass('alert-success');
				$('#colorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#buttonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
		$("#modalRetorno").modal();
	}
}

function agregarEnfermedad(idMascota){

	$("#modalNuevaEnfermedad").modal('hide');

	var nombreEnfermedad = document.getElementById("inpNombreEnfermedad").value || null;
	var fechaEnfermedad = document.getElementById("inpFechaDiagnosticoEnfermedad").value || null;
	var observacionesEnfermedad = document.getElementById("inpObservacionesEnfermedad").value || null;

	if(!validarDatosEnfermedada(nombreEnfermedad, fechaEnfermedad, observacionesEnfermedad)){
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
					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalTituloRetorno').innerHTML = "Nueva enfermedad";
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					$('#modalColorRetorno').removeClass('alert-success');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-danger');
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva enfermedad";
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva enfermedad";
				$('#colorRetorno').removeClass('alert-warning');
				$('#colorRetorno').removeClass('alert-success');
				$('#colorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#buttonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
		$("#modalRetorno").modal();
	}
}

function validarDatosEnfermedada(nombre, fecha, observaciones){
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
		$("#modalColorRetorno").addClass('alert-warning');
		document.getElementById('modalTituloRetorno').innerHTML = "Error: Información enfermedad";
		document.getElementById("modalMensajeRetorno").innerHTML = mensajeError;
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
		$("#modalRetorno").modal();
	}

	return conError;
}

function setEstadoMascota(btn){

	if(btn.name == "inactivar"){
		$("#modalColorActDescMascota").removeClass('alert-success');
		$("#modalColorActDescMascota").addClass('alert-danger');
		document.getElementById('modalTituloActDescMascota').innerHTML = "Desactivar mascota";
		document.getElementById('modalMensajeActDescMascota').innerHTML = "¿Desea desactivar esta mascota?";
		document.getElementById('modalButtonActDescMascota').innerHTML = "Desactivar"
	}else if (btn.name == "activar"){
		$("#modalColorActDescMascota").removeClass('alert-danger');
		$("#modalColorActDescMascota").addClass('alert-success');
		document.getElementById('modalTituloActDescMascota').innerHTML = "Activar mascota";
		document.getElementById('modalMensajeActDescMascota').innerHTML = "¿Desea activar esta mascota?";
		document.getElementById('modalButtonActDescMascota').innerHTML = "Activar";
	}
	$("#modalActDescMascota").modal();
}

function activarDesactivarMascota(btn){
	var idMascota = btn.name;
	$("#modalActDescMascota").modal('hide');

	console.log(idMascota)
	$.ajax({
		async: false,
		url: urlBase + "/activarDesactivarMascota",
		type: "POST",
		data: {
			idMascota: idMascota,
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno){
				$('#modalColorRetorno').removeClass('alert-danger');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-success');
				document.getElementById('modalTituloRetorno').innerHTML = response.titulo;
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = response.titulo;
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
			}
			document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva enfermedad";
			$('#modalColorRetorno').removeClass('alert-warning');
			$('#modalColorRetorno').removeClass('alert-success');
			$('#modalColorRetorno').addClass('alert-danger');
			$("#modalRetorno").modal();
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
	$("#modalRetorno").modal();
}