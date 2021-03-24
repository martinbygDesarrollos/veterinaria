const urlBase = '/veterinarianan/public';

function verHistoriaClinica(btn){
	var idHistoria = btn.id;

	$.ajax({
		async: false,
		url: urlBase + "/getHistoriaCompleta",
		type: "POST",
		data: {
			idHistoria: idHistoria
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			if(response){
				document.getElementById("titleConsulta").innerHTML = "Historia del " + response.historia.fecha;
				document.getElementById("fechaConsulta").innerHTML = response.historia.fecha;
				document.getElementById("motivoConsulta").innerHTML = response.historia.motivoConsulta;
				if(response.historia.observaciones.length > 3)
					document.getElementById("observacionesConsulta").innerHTML = response.historia.observaciones;
				else
					document.getElementById("observacionesConsulta").innerHTML = "No se especificaron observaciones.";

				if(response.historia.diagnostico.length > 3)
					document.getElementById("diagnosticoConsulta").innerHTML = response.historia.diagnostico;
				else
					document.getElementById("diagnosticoConsulta").innerHTML = "No se especifico un diagnostico.";

				$('#modalHistoriaClinica').modal();
			}else{
				$('#colorRetorno').removeClass('alert-warning');
				$('#colorRetorno').removeClass('alert-success');
				$('#colorRetorno').addClass('alert-danger');
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Ver historia.";
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal('hide');
				});
				$("#modalRetorno").modal();
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