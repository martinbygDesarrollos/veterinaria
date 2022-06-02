function openModalVacuna(inputButton){
	$("#dataListNombreVacuna").empty();

	if(inputButton.id == "NUEVAVACUNA"){
		$('#modalTitleVacuna').html("Nueva vacuna/medicamento");
		$('#labelInputDateVacuna').text("Fecha primer dosis");
		clearModalVacuna();
		$('#modalButtonVacuna').off('click');
		$('#modalButtonVacuna').click(function(){
			createNewVacuna(inputButton.name);
		});
	}else{
		$('#modalTitleVacuna').html("Modificar vacuna/medicamento");
		let response = sendPost('getVacunaMascota', {idVacunaMascota: inputButton.name});
		if(response.result == 2){
			$('#labelInputDateVacuna').text('Fecha ultima dosis');
			$('#inputNombreVacuna').val(response.objectResult.nombreVacuna);
			$('#inputIntervaloVacuna').val(response.objectResult.intervaloDosis);
			$('#inputPrimerDosisVacuna').val(response.objectResult.fechaUltimaDosis);
			$('#inputObservacionesVacuna').html(response.objectResult.observacion);

			$('#modalButtonVacuna').off('click');
			$('#modalButtonVacuna').click(function(){
				updateVacunaMascota(inputButton.name);
			});
		}
	}

	$('#modalVacuna').modal();
}

function createNewVacuna(idMascota){
	let nombre = $('#inputNombreVacuna').val() || null;
	let intervalo = $('#inputIntervaloVacuna').val() || null;
	let primerDosis = $('#inputPrimerDosisVacuna').val() || null;
	let observaciones = $('#inputObservacionesVacuna').html() || null;
	console.log(intervalo);
	if(nombre){
		if(primerDosis){
			let data ={idMascota: idMascota, nombreVacuna: nombre, intervalo: intervalo, fechaDosis: primerDosis, observaciones: observaciones};
			let response = sendPost("aplicarNuevaVacunaMascota", data);
			showReplyMessage(response.result, response.message, "Agregar vacuna/medicamento", "modalVacuna");
			if(response.result == 2){
				let vacuna = response.newVacuna;
				$('#tbodyVacunas').prepend(createRowVacuna(vacuna.idVacunaMascota ,vacuna.fechaProximaDosis ,vacuna.fechaUltimaDosis ,vacuna.nombreVacuna ,vacuna.observacion, vacuna.intervaloDosis ,vacuna.numDosis ,vacuna.fechaPrimerDosis));

				//buscar mascota y sus datos
				idMascota = vacuna.idVacunaMascota;
				sendAsyncPost("getSocioByMacota", {id: idMascota})
				.then((response)=>{
					vacMessage = "Se agendó próxima dosis de "+vacuna.nombreVacuna+" para el día "+vacuna.fechaProximaDosis+" a ";
					console.log(data);
					sendAsyncPost("enviarWhatsappAutomatico", {phone: "92459188", message: vacMessage})
					.then(( response )=>{
						console.log(response);
					})

					console.log(response);
				})
			}
		}else showReplyMessage(1, "La fecha de la primer dosis no puede ser ingresada vacia", "Fecha primer dosis requerida", "modalVacuna");
	}else showReplyMessage(1, "El nombre no puede ser ingresado vacio.", "Nombre requerido.", "modalVacuna");
}

function createRowVacuna(idVacunaMascota, fechaProximaDosis, fechaUltimaDosis, nombreVacuna, observaciones, intervalo, numDosis, fechaPrimerDosis){
	let row = "<tr id='trV"+ idVacunaMascota +"'>";
	row += "<td class='text-center'><butto class='btn btn-link btn-sm' onclick='modalAplicarDosis("+ idVacunaMascota +")'><i class='fas fa-syringe text-dark'></i></butto></td>"
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + fechaProximaDosis + "</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + fechaUltimaDosis + "</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + nombreVacuna + "</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + intervalo + "</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + numDosis + "</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + fechaPrimerDosis + "</td>";
	row += "<td class='text-center' style='min-width: 6em;'>";
	row += "<button class='btn btn-link btn-sm' name='" + idVacunaMascota + "' onclick='openModalVacuna(this)'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link btn-sm' onclick='openModalBorrarVacuna("+ idVacunaMascota + ")'><i class='fas fa-trash-alt text-dark'></i></button></td>";
	row += "</tr>";

	return row;
}

function modalAplicarDosis(idVacunaMascota){
	let response = sendPost('getVacunaMascota', {idVacunaMascota: idVacunaMascota});
	if(response.result == 2){
		$('#aplicarDosisText').html("¿Desea aplicar una dosis de <b>"+ response.objectResult.nombreVacuna +"</b>?");
		$('#inputDateAplicarDosis').val(getDateForInput());
		$('#modalAplicarDosis').modal();
		$('#btnAplicarDosis').off('click');
		$('#btnAplicarDosis').click(function(){
			aplicarDosisVacuna(idVacunaMascota);
		});
	}else showReplyMessage(response.result, response.message, "Vacuna/medicamento no encontrado", null);
}

function aplicarDosisVacuna(idVacunaMascota){
	let dateDosis = $('#inputDateAplicarDosis').val() || null;
	if(dateDosis){
		let response = sendPost("aplicarDosisVacuna", {idVacunaMascota: idVacunaMascota, dateDosis: dateDosis});
		showReplyMessage(response.result, response.message, "Aplicar dosis", "modalAplicarDosis");
		if(response.result != 0){
			let vacuna = response.updatedVacuna;
			$('#trV' + vacuna.idVacunaMascota).replaceWith(createRowVacuna(vacuna.idVacunaMascota ,vacuna.fechaProximaDosis ,vacuna.fechaUltimaDosis ,vacuna.nombreVacuna ,vacuna.observacion, vacuna.intervaloDosis ,vacuna.numDosis ,vacuna.fechaPrimerDosis));
		}
	}else showReplyMessage(1, "Debe ingresar una fecha válida.", "Fecha requerida", "modalAplicarDosis");
}


function openDescriptionVacuna(idVacunaMascota){
	let response = sendPost('getVacunaMascotaToShow', {idVacunaMascota: idVacunaMascota});
	if(response.result == 2){
		let vacuna = response.objectResult;
		$("#titleModalView").html("Vacuna");
		$('#dateModalView').html("<b>Última dosis</b>: " + vacuna.fechaUltimaDosis);
		$("#textModalView").html("<b>Nombre</b>: " + vacuna.nombreVacuna + "<hr><b>Intervalo:</b> " + vacuna.intervaloDosis + "<hr><b>Primer dosis:</b> " + vacuna.fechaPrimerDosis + "<hr><b>Proxima dosis:</b> " + vacuna.fechaProximaDosis +"<hr><b>Observaciones: </b>" + vacuna.observacion + "<hr>");

		$("#divFilesTableModalView").attr("hidden", true);
		$("#divFilesTableModalView").attr("disable", true);

		$('#modalView').modal();
	}
}

function clearModalVacuna(){
	$('#inputNombreVacuna').val("");
	$('#inputIntervaloVacuna').val(1);
	$('#inputPrimerDosisVacuna').val(getDateForInput());
	$('#inputObservacionesVacuna').html("");
}

function updateVacunaMascota(idVacunaMascota){

	let nombreVacuna = $('#inputNombreVacuna').val() || null;
	let intervalo = $('#inputIntervaloVacuna').val() || null;
	let fechaUltimaDosis = $('#inputPrimerDosisVacuna').val() || null;
	let observaciones = $('#inputObservacionesVacuna').val() || null;

	if(nombreVacuna){
		if(fechaUltimaDosis){
			let data = {
				idVacunaMascota: idVacunaMascota,
				nombre: nombreVacuna,
				intervalo: intervalo,
				fechaUltimaDosis: fechaUltimaDosis,
				observaciones: observaciones
			};
			let response = sendPost("updateVacunaMascota", data);
			showReplyMessage(response.result, response.message, "Modificar", "modalVacuna");
			if(response.result == 2){
				let vacuna = response.updatedVacuna;
				$('#trV' + vacuna.idVacunaMascota).replaceWith(createRowVacuna(vacuna.idVacunaMascota ,vacuna.fechaProximaDosis ,vacuna.fechaUltimaDosis ,vacuna.nombreVacuna ,vacuna.observacion, vacuna.intervaloDosis ,vacuna.numDosis ,vacuna.fechaPrimerDosis));
			}
		}else showReplyMessage(1, "La fecha de la primer dosis no puede ser ingresada vacia", "Fecha primer dosis requerida", "modalVacuna");
	}else showReplyMessage(1, "El nombre no puede ser ingresado vacio.", "Nombre requerido.", "modalVacuna");
}

function changeStateMascota(inputCheck){
	let currentValue = inputCheck.checked;
	let title = "Activar mascota";

	if(!currentValue)
		title = "Desactivar mascota";

	let response = sendPost('activarDesactivarMascota', {idMascota: inputCheck.name});
	showReplyMessage(response.result, response.message, title, null);
	if(response.result == 0){
		if(currentValue)
			$('#stateMascota').prop('checked', false);
		else
			$('#stateMascota').prop('checked', true);
	}
}

function openModalBorrarVacuna(idVacunaMascota){
	$('#modalBorrarVacuna').modal();
	$('#titleModalBorrar').html("Borrar")
	$('#textModalBorrar').html("¿Seguro que desea borrar la vacuna/medicamento?")
	$('#modalButtonBorrar').off('click');
	$('#modalButtonBorrar').click(function(){
		removeVacunaMascota(idVacunaMascota);
	});
	$('#modalBorrar').modal();
}

function removeVacunaMascota(idVacunaMascota){

	let response = sendPost("borrarVacunaMascota", {idVacunaMascota: idVacunaMascota});
	showReplyMessage(response.result, response.message, "Borrar vacuna", "modalBorrar");
	if(response.result == 2)
		$('#trV' + idVacunaMascota).remove();
}

function getDataVacunas(valueInput){
	console.log(valueInput);

	if ( valueInput.length > 2 ){
		sendAsyncPost("getVacunasByInput", {value: valueInput})
		.then((response)=>{
			if( response.result == 2 ){
				$('#dataListNombreVacuna').empty();
				let list = response.listResult;
				for (let i = 0; i < list.length; i++) {
					let option = "<option >"+list[i].nombre+"</option>";
					$('#dataListNombreVacuna').append(option);
				}
			}

		})
	}
}

function completeDataVacunas( value ){
	sendAsyncPost("getVacunasByName", {value: value})
	.then((response)=>{
		if( response.result == 2 ){
			let obj = response.objectResult
			$("#inputIntervaloVacuna").val(obj.intervalo)
			console.log(response)
		}

	})
}