const urlBase = '/veterinarianan/public';
function openModalVencimiento(btn){
	document.getElementById("idSocioVencimiento").value = btn.id;
	document.getElementById("idMascotaVencimiento").value = btn.name;
	$('#modalNotificacion').modal();
}


function notificarVencimientos(){
	var idSocio = document.getElementById("idSocioVencimiento").value;
	var idMascota = document.getElementById("idMascotaVencimiento").value;

	$('#modalNotificacion').modal('hide');

	$.ajax({
		async: false,
		url: urlBase + "/notificarSocio",
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
				document.getElementById('modalTituloRetorno').innerHTML = "Notificar socio";
				$('#modalColorRetorno').removeClass('alert-danger');
				$('#modalColorRetorno').addClass('alert-success');
			}else{
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Notificar socio";
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').addClass('alert-danger');
			}
			document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;
		},

		error: function (response) {
			console.log("response ERROR:" + eval(response));
			showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo","Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}