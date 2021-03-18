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