const urlBase = '/veterinarianan/public';
function seleccionarSocio(btn){
	var idMascota = btn.name;
	var idSocio = btn.id;

	$("#modalButtonAsociar").click(function(){
		vincularSocioMascota(idSocio, idMascota);
	});
	$("#modalAsociar").modal();
}

function vincularSocioMascota(idSocio, idMascota){
	$("#modalAsociar").modal('hide');
	$.ajax({
		async: false,
		url: urlBase + "/vincularSocioMascota",
		type: "POST",
		data: {
			idSocio: idSocio,
			idMascota: idMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ", response);

			if(response.retorno){
				showReplyMessage('success', response.mensaje, response.enHistorial, "Vincular socio");
				$("#modalButtonRetorno").click(function(){
					window.location.href = urlBase + "/mascotas";
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null,"Vincular socio");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
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