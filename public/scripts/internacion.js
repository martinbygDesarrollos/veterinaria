var thelastid = 0;

$("#petHospitalizedMode").change(function() {

	thelastid = 0;
	$('#tbodyInternacion').empty();
	cargarTablaInternacion();
});


function cargarTablaInternacion(){

	let mode = $("#petHospitalizedMode").val()

	if ( mode !== "vet" && mode !== "casa" ){
		mode = null;
	}


	sendAsyncPost('getHospitalizedPet', {hospitalizedPlace:mode, lastId:thelastid})
	.then((response)=>{

		if(response.result == 2){


			if(response.lastId != thelastid){
				thelastid = response.lastId;

				let list = response.listResult;


				for (var i = 0; i < list.length; i++) {


					let row = createRowHospitalized(list[i]);


					$('#tbodyInternacion').append(row);
				}
			}

		}

	})

}


function createRowHospitalized(obj){
	if ( !obj.fechaFallecimiento ){
		internado = "";
		if ( obj.internado == "vet" ){
			internado = "En veterinaria";
		}else if (obj.internado == "casa"){
			internado = "Internación ambulatoria";
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
			if ( obj.telefax.length >= 9 )
				botonwpp = '<a class="btn btn-info" title="Enviar whatsapp al '+obj.telefax+'" href="https://wa.me/'+obj.telefax+'" target="_blank" value=""><i class="fab fa-whatsapp"></i></a>'
		}


		let row = "<tr id='trinternado"+ obj.idMascota +"' class='"+colorForClient.class+"'>";
		row += "<td class='text-center'><a href='"+getSiteURL() + "ver-mascota/" + obj.idMascota+"'>"+ obj.nombre +"</a></td>";

		if ( obj.idSocio ){
			row += "<td class='text-center'><a href='"+getSiteURL() + "ver-socio/" + obj.idSocio+"'>"+obj.nomCliente+"</a></td>";
		}else
			row += "<td class='text-center'></td>";


		row += "<td class='text-center'>"+ internado +"</td>";
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



function redirectToSocio(idSocio){
	window.location.href = getSiteURL() + "ver-socio/"+ idSocio;
}