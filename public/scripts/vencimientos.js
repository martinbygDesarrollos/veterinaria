let lastId = 0;
let textToSearch = null;

function cargarVencimientoCuota(){
	let response = sendPost("getCuotasVencidas", {lastId: lastId, textToSearch: textToSearch});
	if(response.result == 2){
		console.log(response)
		if(lastId != response.lastId)
			lastId = response.lastId;
		let list = response.listResult;
		for (var i = 0; i < list.length; i++) {
			let row = createRowCuotas(list[i].idSocio, list[i].fechaUltimaCuota, list[i].fechaPago, list[i].nombre, list[i].cuota, list[i].lugarPago, list[i].telefono, list[i].email);
			$('#tbodyVencimientosCuota').append(row);
		}
	}
}

function createRowCuotas(idSocio, fechaUltimaCuota, fechaPago, nombre, cuota, lugarPago, telefono, email){
	let row = "<tr>";
	row += "<td class='text-center'>"+ fechaUltimaCuota +"</td>";
	row += "<td class='text-center notShowMobile'>"+ fechaPago +"</td>";
	row += "<td class='text-center'>"+ nombre +"</td>";
	row += "<td class='text-center notShowMobile'>"+ cuota +"</td>";
	row += "<td class='text-center notShowMobile'>"+ lugarPago +"</td>";
	//row += "<td class='text-center'>"+ telefono +"</td>";

	if(telefono != "No especificado" && telefono != "No corresponde" && telefono != "" && telefono){
		if( telefono.length >= 8 )
			row += '<td class="text-center" title="Notificar cliente '+telefono+'"><a href="https://'+telefono+'" target="_blank"><button title="Notificar cliente '+telefono+'" class="btn btn-light"><i class="fab fa-whatsapp"></i></button></a></td>';
		else
			row += '<td class="text-center">'+telefono+'</td>';
	}
	else
		row += "<td class='text-center'></td>";

	/*if(email)
		row += "<td class='text-center'><button onclick='notificarSocioCuota("+ idSocio +")' class='btn btn-info btn-sm'>"+ email +" <i class='fas fa-paper-plane'></i></button></td>";
	else
		row += "<td class='text-center'>No especificado</td>";
	row += "</tr>";*/

	if(email != "No especificado" && email != "No corresponde")
		row += "<td class='text-center' title='"+email+"' ><button onclick='notificarSocioCuota("+ idSocio +", `"+email+"`)' class='btn btn-info'><i class='fas fa-paper-plane'></i></button></td>";
	else
		row += "<td class='text-center'></td>";
	row += "</tr>";

	return row;
}

function notificarSocioCuota(idSocio, email){
	console.log(email);
	showReplyMessage(1, "Confirma enviar correo a "+email, "Notificar socio", null);

	$('#modalButtonResponse').off('click');
	$('#modalButtonResponse').click(function(){
		$('#modalButtonResponse').attr("disable", true);
		$('#modalButtonResponse').attr("hidden", true);
		$('#modalMessageResponse').html("Enviando...");
		sendAsyncPost("notificarSocioCuota", {idSocio: idSocio})
		.then((response)=>{
			$('#modalButtonResponse').attr("disable", false);
			$('#modalButtonResponse').attr("hidden", false);
			showReplyMessage(response.result, response.message, "Notificar socio", null);
		})
	})
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

	row += "<td class='text-center' onclick='openDescriptionVacuna("+idVacunaMascota+")'>"+ fechaProximaDosis +"</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna("+idVacunaMascota+")'>"+ nombreVacuna +"</td>";
	row += "<td class='text-center notShowMobile' onclick='openDescriptionVacuna("+idVacunaMascota+")'>"+ intervaloDosis +" días</td>";
	row += "<td class='text-center notShowMobile' onclick='openDescriptionVacuna("+idVacunaMascota+")'>"+ numDosis +"</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna("+idVacunaMascota+")'>"+ nombre +"</td>";
	row += "<td class='text-center notShowMobile' onclick='openDescriptionVacuna("+idVacunaMascota+")'>"+ raza +"</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna("+idVacunaMascota+")'>"+ nombreSocio +"</td>";
	//row += "<td class='text-center'>"+ telefono +"</td>";

	if(telefono != "No especificado" && telefono != "No corresponde" && telefono != "" && telefono){
		'href="https://'+telefono+'"';
		//href="https://wa.me/'+telefono+'"
		if( telefono.length >= 8 )
			row += '<td class="text-center" title="Notificar cliente '+telefono+'"><a target="_blank"><button title="Notificar cliente '+telefono+'" class="btn btn-light" onclick="thenNotifyVacunaByWhatsapp('+idVacunaMascota+')"><i class="fab fa-whatsapp"></i></button></a></td>';
		else
			row += '<td class="text-center">'+telefono+'</td>';
	}
	else
		row += "<td class='text-center'></td>";

	if(email != "No especificado" && email != "No corresponde")
		row += "<td class='text-center' title='"+email+"' ><button onclick='notificarVacunaMascota("+ idMascota +", `"+email+"`)' class='btn btn-info'><i class='fas fa-paper-plane'></i></button></td>";
	else
		row += "<td class='text-center'></td>";
	row += "</tr>";

	return row;
}

function notificarVacunaMascota(idMascota, email){
	/*let response = sendPost("notificarVacunaMascota", {idMascota: idMascota});
	console.log(response);
	showReplyMessage(response.result, response.message, "Notificar vacuna", null);*/

	console.log(email);
	showReplyMessage(1, "Confirma enviar correo a "+email, "Notificar vacuna", null);

	$('#modalButtonResponse').off('click');
	$('#modalButtonResponse').click(function(){
		$('#modalButtonResponse').attr("disable", true);
		$('#modalButtonResponse').attr("hidden", true);
		$('#modalMessageResponse').html("Enviando...");
		sendAsyncPost("notificarVacunaMascota", {idMascota: idMascota})
		.then((response)=>{
			$('#modalButtonResponse').attr("disable", false);
			$('#modalButtonResponse').attr("hidden", false);
			showReplyMessage(response.result, response.message, "Notificar vacuna", null);
		})
	})
}


function thenNotifyVacunaByWhatsapp(idVacunaMascota){
	showMessageConfirm(1, "Se notificó correctamente?", "Vacuna/medicamento", "modalVacuna");
	$('#modalMessageConfirmBtnSi').off('click');
	$('#modalMessageConfirmBtnSi').click(function(){

		console.log("luego del boton de notificar por wpp  ",idVacunaMascota);
		sendAsyncPost( "getVacunaMascotaToShow", {idVacunaMascota:idVacunaMascota})
		.then(( response )=>{
			console.log("datos de vacuna", response);
			let vacuna = null;
			if ( response.result == 2 ){
				vacuna = response.objectResult;
			}

			let timestamp = getTimestamp();
			if ( timestamp.length == 14 ){
				if ( vacuna.observacion ){
					obs = vacuna.observacion + ","+timestamp;
				}else obs = timestamp;
			}else obs = vacuna.observacion;

			let data = {
				idVacunaMascota: vacuna.idVacunaMascota,
				nombre: vacuna.nombreVacuna,
				intervalo: vacuna.intervaloDosis,
				fechaUltimaDosis: vacuna.fechaUltimaDosis,
				observaciones: obs
			};

			sendAsyncPost("updateVacunaMascota", data)
			.then((response)=>{
				console.log(response);
				if (response.result != 2){
					showReplyMessage(response.result, response.message, "Vacuna/medicamento", null);
				}
			})
			$('#modalMessageConfirmBtnSi').attr("disable", false);
			$('#modalMessageConfirm').modal("hide");
		});
	});
}