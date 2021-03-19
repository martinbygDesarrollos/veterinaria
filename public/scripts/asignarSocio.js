const urlBase = '/veterinaria/public';
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
				$('#modalColorRetorno').removeClass('alert-danger');
				$('#modalColorRetorno').addClass('alert-success');
				document.getElementById('modalTituloRetorno').innerHTML = "Socio vinculado";
				document.getElementById('modalMensajeRetorno').innerHTML = response.mensaje;
				$("#modalRetorno").modal();
				$("#modalButtonRetorno").click(function(){
					window.location.href = urlBase + "/mascotas";
				});
			}else{
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Vincular socio";
				document.getElementById('modalMensajeRetorno').innerHTML = response.mensajeError;
				$("#modalRetorno").modal();
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			}


		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			$('#modalColorRetorno').removeClass('alert-success');
			document.getElementById('modalTituloRetorno').innerHTML = "Error: Vincular socio";
			$('#modalColorRetorno').addClass('alert-danger');
			$("#modalRetorno").modal();
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
	$("#modalRetorno").modal();
}