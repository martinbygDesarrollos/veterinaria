const urlBase = '/veterinarianan/public';

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
				showReplyMessage('success', response.mensaje, response.enHistorial, "Nueva historia clínica");
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null, "Nueva historia clínica");
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

function ocultarAlert(){
	var alert = document.getElementById('mensajeErrorHistoriaAlert');
	if(alert.style.display == "block")
		alert.style.display = "none";
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
				showReplyMessage('success', response.mensaje, response.enHistorial, response.titulo);
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null, response.titulo);
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

