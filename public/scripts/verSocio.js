
function activarDesactivarSocio(btn){
	$('#modalActivarDesactivarSocio').modal('hide');
	var idSocio = btn.id;
	var estado = btn.name;

	if(estado == 0)
		estado = "Activar socio";
	else estado = "Desactivar socio";

	$.ajax({
		async: false,
		url: urlBase + "/activarDesactivarSocio",
		type: "POST",
		data: {
			idSocio: idSocio
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response.retorno){
				showReplyMessage('success', response.mensaje, response.enHistorial, estado);
				$("#modalButtonRetorno").click(function(){
					window.location.reload();
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null, estado);
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
