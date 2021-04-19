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
				showReplyMessage('danger', response.mensajeError, null, "Ver historia clínica");
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

let menorIdHistoriaClinica = 0;
let maxIdHistoriaClinica = 0;

function cargarHistoriaClinica(idMascota){
	$.ajax({
		async: false,
		url: urlBase + "/getHistoriaClinicaPagina",
		type: "POST",
		data: {
			ultimoID: menorIdHistoriaClinica,
			idMascota: idMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorIdHistoriaClinica = response.min;
			maxIdHistoriaClinica = response.max;
			var historiaClinica = response.historial;
			console.log(response)
			$('#tbodyHistoriaClinica').empty();
			if(historiaClinica.length == 0){
				document.getElementById("noHayResultadosHistoriaClinicaMensaje").style.display = "block";
				document.getElementById("irAdelantePaginaHistoriaClinica").style.display = "none";
				document.getElementById("irAtrasPaginaHistoriaClinica").style.display = "none";
			}else{
				if(historiaClinica.length < 5){
					document.getElementById("irAdelantePaginaHistoriaClinica").style.display = "none";
				}else{
					document.getElementById("irAtrasPaginaHistoriaClinica").style.display = "block";
					document.getElementById("irAdelantePaginaHistoriaClinica").style.display = "block";
				}
				document.getElementById("noHayResultadosHistoriaClinicaMensaje").style.display = "none";
				for(var i = 0; i < historiaClinica.length; i ++ ){
					var fila = "<tr><td class='text-center'>" + historiaClinica[i].fecha +"</td>" +
					"<td class='text-center'>" + historiaClinica[i].motivoConsulta +"</td>" +
					"<td class='text-center'><button id='" + historiaClinica[i].idHistorialClinico +"' class='btn btn-info btn-sm' onclick='verHistoriaClinica(this)'><i class='fas fa-eye'></i></button></td></tr>";
					$('#tbodyHistoriaClinica').append(fila);
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

function paginaPosteriorHistoriaClinica(){
	var idMascota = document.getElementById('idMascotaSeleccionada').value;
	cargarHistoriaClinica(idMascota);
}

function paginaAnteriorHistoriaClinica(){
	if(menorIdHistoriaClinica != 0){
		var idMascota = document.getElementById('idMascotaSeleccionada').value;
		menorIdHistoriaClinica = parseInt(maxIdHistoriaClinica) + 10;
		cargarHistoriaClinica(idMascota);
	}
}