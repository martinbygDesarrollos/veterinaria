$("#inputNombreNuevaVacuna").keyup(()=>{

	if ( $("#inputNombreNuevaVacuna").val().length >0 ){

		$("#inputNombreVacuna").val("");
		$("#inputNombreVacuna").attr("readonly", true);
		$("#inputNombreVacuna").attr("disabled", true);
		$("#inputIntervaloVacuna").val(1);
		$("#inputProximaDosisVacuna").val("");

	}else {
		$("#inputNombreVacuna").removeAttr("readonly");
		$("#inputNombreVacuna").removeAttr("disabled");
	}
})



function openModalVacuna(inputButton){

	let muerto = $("#inputFallecimiento").val() || null;

	if ( muerto ){

		if(inputButton.id == "NUEVAVACUNA"){
			showReplyMessage(0, "Mascota con fecha de fallecimiento<br>¿Desea agregar vacuna/medicamento igualmente?", "FALLECIDO", null);
		}else{
			showReplyMessage(0, "Mascota con fecha de fallecimiento<br>¿Desea editar los datos igualmente?", "FALLECIDO", null);
		}

		$("#modalButtonResponse").click(function(){

			if(inputButton.id == "NUEVAVACUNA"){
				$('#modalTitleVacuna').html("Nueva vacuna/medicamento");
				$('#labelInputDateVacuna').text("Fecha dosificación");
				clearModalVacuna();
				$('#modalButtonVacuna').off('click');
				$('#modalButtonVacuna').click(function(){
					createNewVacuna(inputButton.name);
				});
			}else{
				$('#modalTitleVacuna').html("Modificar vacuna/medicamento");
				$('#inputNombreNuevaVacuna').attr("disabled", true);
				$('#inputNombreNuevaVacuna').attr("readonly", true);
				$('#divNombreNuevaVacuna').hide();

				let response = sendPost('getVacunaMascota', {idVacunaMascota: inputButton.name});
				if(response.result == 2){
					$('#labelInputDateVacuna').text('Fecha dosificación');
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


		});


	}else{


		if(inputButton.id == "NUEVAVACUNA"){
			$('#modalTitleVacuna').html("Nueva vacuna/medicamento");
			$('#labelInputDateVacuna').text("Fecha dosificación");
			clearModalVacuna();
			$('#modalButtonVacuna').off('click');
			$('#modalButtonVacuna').click(function(){
				createNewVacuna(inputButton.name);
			});
		}else{
			$('#modalTitleVacuna').html("Modificar vacuna/medicamento");
			$('#inputNombreNuevaVacuna').attr("disabled", true);
			$('#inputNombreNuevaVacuna').attr("readonly", true);
			$('#divNombreNuevaVacuna').hide();

			let response = sendPost('getVacunaMascota', {idVacunaMascota: inputButton.name});
			if(response.result == 2){
				$('#labelInputDateVacuna').text('Fecha dosificación');
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


}

function createNewVacuna(idMascota){
	let nombre = $('#inputNombreVacuna').val() || null;
	let intervalo = $('#inputIntervaloVacuna').val() || null;
	let primerDosis = $('#inputPrimerDosisVacuna').val() || null;
	let observaciones = $('#inputObservacionesVacuna').html() || null;

	if(nombre){
		if(primerDosis){
			let data ={idMascota: idMascota, nombreVacuna: nombre, intervalo: intervalo, fechaDosis: primerDosis, observaciones: observaciones};
			let response = sendPost("aplicarNuevaVacunaMascota", data);

			showReplyMessage(response.result, response.message, "Agregar vacuna/medicamento", "modalVacuna");
			if(response.result == 2){
				let vacuna = response.newVacuna;
				$('#tbodyVacunas').prepend(createRowVacuna(vacuna.idVacunaMascota ,vacuna.fechaProximaDosis ,vacuna.fechaUltimaDosis ,vacuna.nombreVacuna ,vacuna.observacion, vacuna.intervaloDosis ,vacuna.numDosis ,vacuna.fechaPrimerDosis));
				$("#modalVacuna").modal("hide");
			}else {

				showReplyMessage(response.result, response.message, "Vacuna/medicamento", "modalVacuna");
			}
		}else showReplyMessage(1, "La fecha de la primer dosis no puede ser ingresada vacia", "Vacuna/medicamento", "modalVacuna");
	}else showReplyMessage(1, "El nombre no puede ser ingresado vacio.", "Vacuna/medicamento", "modalVacuna");
}

function createRowVacuna(idVacunaMascota, fechaProximaDosis, fechaUltimaDosis, nombreVacuna, observaciones, intervalo, numDosis, fechaPrimerDosis){
	let row = "<tr id='trV"+ idVacunaMascota +"'>";
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + nombreVacuna + " ("+intervalo+")</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + fechaUltimaDosis + "</td>";
	row += "<td class='text-center' onclick='openDescriptionVacuna(" + idVacunaMascota + ")'>" + fechaProximaDosis + "</td>";
	row += "<td class='text-center' style='min-width: 6em;'>";
	row += "<button class='btn btn-link btn-sm' name='" + idVacunaMascota + "' onclick='openModalVacuna(this)'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link btn-sm' onclick='openModalBorrarVacuna("+ idVacunaMascota + ")'><i class='fas fa-trash-alt text-dark'></i></button></td>";
	row += "<td><a class='btn btn-info mt-2 mr-1' target='_blank' title='Enviar whatsapp al ' href='https://wa.me/' value=''><i class='fab fa-whatsapp'></i></a></td>";
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
	}else showReplyMessage(response.result, response.message, "Vacuna/medicamento", null);
}

function aplicarDosisVacuna(idVacunaMascota){
	let dateDosis = $('#inputDateAplicarDosis').val() || null;
	if(dateDosis){
		let response = sendPost("aplicarDosisVacuna", {idVacunaMascota: idVacunaMascota, dateDosis: dateDosis});
		showReplyMessage(response.result, response.message, "Vacuna/medicamento", "modalAplicarDosis");
		if(response.result != 0){
			let vacuna = response.updatedVacuna;
			$('#trV' + vacuna.idVacunaMascota).replaceWith(createRowVacuna(vacuna.idVacunaMascota ,vacuna.fechaProximaDosis ,vacuna.fechaUltimaDosis ,vacuna.nombreVacuna ,vacuna.observacion, vacuna.intervaloDosis ,vacuna.numDosis ,vacuna.fechaPrimerDosis));
		}
	}else showReplyMessage(1, "Debe ingresar una fecha válida.", "Vacuna/medicamento", "modalAplicarDosis");
}


function openDescriptionVacuna(idVacunaMascota){
	let response = sendPost('getVacunaMascotaToShow', {idVacunaMascota: idVacunaMascota});
	if(response.result == 2){
		let vacuna = response.objectResult;
		let fechasNotif = vacuna.fechasNotif;
		let fechas = "";

		if ( fechasNotif ){
			fechas = '<table class="w-100 table-hover" >'
			for (var i = 0; i < fechasNotif.length; i++) {
				fechas += '<tr class=""><td>'+fechasNotif[i]+'</td></tr>';
			}
			fechas += '</table>';
		}

		$("#titleModalView").html("Vacuna");
		$('#dateModalView').html("<b>Fecha dosificación</b>: " + vacuna.fechaUltimaDosis + "<br><b>Próxima dosis:</b> " + vacuna.fechaProximaDosis);
		$("#textModalView").html("<b>Nombre</b>: " + vacuna.nombreVacuna + "<hr><b>Intervalo:</b> " + vacuna.intervaloDosis + "<hr><b>Notificaciones enviadas: </b>" + fechas + "<hr>");

		$("#divFilesTableModalView").attr("hidden", true);
		$("#divFilesTableModalView").attr("disable", true);

		$("#divButtonLeftModalView").attr("hidden",true);
		$("#divButtonLeftModalView").attr("disable",true);
		$("#divButtonRightModalView").attr("hidden",true);
		$("#divButtonRightModalView").attr("disable",true);


		var modal = document.getElementById("modalViewDialog");
		modal.className = "modal-dialog modal-dialog-centered";


		$('#modalView .modal-dialog').css('height', '');
		$('#modalView .modal-content').css('height', '');


		$('#modalView').modal();
	}
}

function clearModalVacuna(){
	$('#inputNombreNuevaVacuna').val("");
	$('#inputNombreNuevaVacuna').removeAttr("disabled");
	$('#inputNombreNuevaVacuna').removeAttr("readonly");
	$('#divNombreNuevaVacuna').show();

	$('#inputNombreVacuna').val("");
	$("#inputNombreVacuna").removeAttr("readonly");
	$("#inputNombreVacuna").removeAttr("disabled");

	$('#inputIntervaloVacuna').val(1);
	$('#inputPrimerDosisVacuna').val(getDateForInput());
	$('#inputProximaDosisVacuna').val("");

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
			showReplyMessage(response.result, response.message, "Vacuna/medicamento", "modalVacuna");
			if(response.result == 2){
				let vacuna = response.updatedVacuna;
				$('#trV' + vacuna.idVacunaMascota).replaceWith(createRowVacuna(vacuna.idVacunaMascota ,vacuna.fechaProximaDosis ,vacuna.fechaUltimaDosis ,vacuna.nombreVacuna ,vacuna.observacion, vacuna.intervaloDosis ,vacuna.numDosis ,vacuna.fechaPrimerDosis));
			}
		}else showReplyMessage(1, "La fecha de la primer dosis no puede ser ingresada vacia", "Vacuna/medicamento", "modalVacuna");
	}else showReplyMessage(1, "El nombre no puede ser ingresado vacio.", "Vacuna/medicamento", "modalVacuna");
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
	showReplyMessage(response.result, response.message, "Vacuna/medicamento", "modalBorrar");
	if(response.result == 2)
		$('#trV' + idVacunaMascota).remove();
}

function getDataVacunas(valueInput){


	if ( valueInput.length > 0 ){
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
			calularFechaProxDosis();
		}

	})
}



function calularFechaProxDosis(){

	let intervalo = $("#inputIntervaloVacuna").val() ?? 0;
	let fechaDosis = $("#inputPrimerDosisVacuna").val() ?? getCurrentDate();

	let proximaDosis = dateAddDays( fechaDosis, intervalo ) ?? getCurrentDate();
	$("#inputProximaDosisVacuna").val(proximaDosis);

}


//cuando se cambia la fecha de la próxima dosis de la vacuna, se calcula el intervalo según la fecha que se tenga de que se dió la primer dosis que puede ser el dia de hoy o no
function calularIntervaloDosis(){
	let fechaDosis = $("#inputPrimerDosisVacuna").val() ?? getCurrentDate();
	let proximaDosis = $("#inputProximaDosisVacuna").val() ?? getCurrentDate();

	let intervaloVacuna = parseInt( differenceDays( proximaDosis, fechaDosis) );
	$("#inputIntervaloVacuna").val( intervaloVacuna );
}