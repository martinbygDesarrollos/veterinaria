function cargarTablaInternacion(){

	sendAsyncPost('getHospitalizedPet', {hospitalizedPlace:null})
	.then((response)=>{

		if(response.result == 2){


			let list = response.listResult;


			for (var i = 0; i < list.length; i++) {


				let row = createRowHospitalized(list[i]);


				$('#tbodyInternacion').append(row);
			}
		}else {
			$('#tbodyInternacion').empty();
		}

	})

}


function createRowHospitalized(obj){
	console.log(obj);
	if ( !obj.fechaFallecimiento ){
		internado = "";
		if ( obj.internado == "vet" ){
			internado = "En veterinaria";
		}else if (obj.internado == "casa"){
			internado = "Dar seguimiento";
		}

		colorForClient = calculateColorRowByClient(obj.tipo, obj.deudor);

		let botonwpp = '<button class="btn btn-info" title="No se encontró número de Whatsapp" disabled><a class="btn-info" target="_blank" value=""><i class="fab fa-whatsapp"></i></a></button>';

		telefax = "";
		if (obj.telefax){
			if ( obj.telefax !== null ){
				telefax = obj.telefax
			}
		}


		if ( obj.telefax ){
			if ( obj.telefax.length >=9 )
				botonwpp = '<a class="btn btn-info" title="Enviar whatsapp al '+obj.telefax+'" href="https://wa.me/'+obj.telefax+'" target="_blank" value=""><i class="fab fa-whatsapp"></i></a>'
		}


		let row = "<tr id='trinternado"+ obj.idMascota +"' class='"+colorForClient.class+"'>";
		row += "<td class='text-center' onclick='redirectToMascota("+ obj.idMascota +")'>"+ obj.nombre +"</td>";
		row += "<td class='text-center' onclick='redirectToMascota("+ obj.idMascota +")'>"+obj.nomCliente+"</td>";
		row += "<td class='text-center' onclick='redirectToMascota("+ obj.idMascota +")'>"+ internado +"</td>";
		row += "<td class='text-center d-flex justify-content-between '>"+telefax+ botonwpp+"</td>";
		row += "<td class='text-center'><button class='btn btn-warning' onclick='outPetHospitalized("+obj.idMascota+")'>Dar alta</button></td></tr>";

		return row;
	}

}



function openModalInternacion(idMascota){

	$('#inputIdMascotaInternacion').val(idMascota);
	$('#inputHoraInternacion').val(getCurrentHours());
	$('#inputFechaInternacion').val(getCurrentDate());
	$('#modalInternacion').modal("show");

}

function newPetHospitalized(){
	let hour = $('#inputHoraInternacion').val();
	let date = $('#inputFechaInternacion').val();
	let place = $('#inputLugarInternacion').val();
	let idMascota = $('#inputIdMascotaInternacion').val();

	hour = hour.replaceAll(":","");


	sendAsyncPost("newPetHospitalized", {place:place, date:date, hour:hour, idMascota:idMascota})
	.then((response)=>{
		if (response.result != 2)
			showReplyMessage(response.result, response.message, "Mascota internar", null);
		else window.location.reload();
	})

}



function outPetHospitalized(idMascota){


	let date = getCurrentDate();
	let hour = getCurrentHours();

	hour = hour.replaceAll(":","");

	sendAsyncPost("petHospitalizedOut", {date:date, hour:hour, idMascota:idMascota})
	.then((response)=>{
		console.log(response);
		if (response.result != 2)
			showReplyMessage(response.result, response.message, "Mascota internar", null);
		else window.location.reload();
	})

}