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
			showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
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
			showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}