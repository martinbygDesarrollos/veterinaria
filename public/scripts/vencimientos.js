
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
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
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
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}

function cargarVencimientoVacunas(){
	let dateVencimiento = $('#inputDateVencimiento').val();
	let response = sendPost("getVacunasVencidas", {dateVencimiento: dateVencimiento});
	if(response.result == 2){
		$('#tbodyVencimientosVacuna').empty();
		let list = response.listResult;
		for (var i = 0; i < list.length; i++) {
			let row = createRowVacunas(list[i].idVacunaMascota, list[i].nombreVacuna, list[i].intervaloDosis, list[i].numDosis, list[i].fechaProximaDosis, list[i].idMascota, list[i].nombre, list[i].raza, list[i].idSocio, list[i].nombreSocio, list[i].telefono, list[i].email);
			$('#tbodyVencimientosVacuna').append(row);
		}
	}
}

function createRowVacunas(idVacunaMascota, nombreVacuna, intervaloDosis, numDosis, fechaProximaDosis, idMascota, nombre, raza, idSocio, nombreSocio, telefono, email){
	let row = "<tr>";

	row += "<td class='text-center'>"+ fechaProximaDosis +"</td>";
	row += "<td class='text-center'>"+ nombreVacuna +"</td>";
	row += "<td class='text-center'>"+ intervaloDosis +"</td>";
	row += "<td class='text-center'>"+ numDosis +"</td>";
	row += "<td class='text-center'>"+ nombre +"</td>";
	row += "<td class='text-center'>"+ raza +"</td>";
	row += "<td class='text-center'>"+ nombreSocio +"</td>";
	row += "<td class='text-center'>"+ telefono +"</td>";
	row += "<td class='text-center'>"+ email +"</td>";

	return row;
}