var widthBySize = 'style="width:75%"';

$(document).ready(()=>{
	var sizeHeight = window.innerHeight;
	var sizeWidth = window.innerWidth;

	if ( sizeWidth <= 768 ){
		widthBySize = '';
	}
});

function getCirugiasByDay( day ){
	sendAsyncPost("getEventCalendarByDay",{day:day})
	.then(( response )=>{
		if ( response.result == 2 ){
			if ( response.listResult.length > 0){
				clearTableEvents();

				for (var i = 0; i < response.listResult.length; i++) {
					if (!response.listResult[i].hora)
						hora = "00:00"
					else hora = response.listResult[i].hora;
					row = createRow(response.listResult[i]);
					$("#tbodyCirugiasCalendar").append(row);
				}
			}else {
				clearTableEvents();
			}
		}else{
			clearTableEvents();
		}
	})
}

//funcion para cargar todas las filas cuando ya hay registros en la agenda
function createRow( obj ){
	//fila
	let row = '<tr id="'+ obj.idAgenda +'" onchange="saveEventInCalendar(this)">';
	//hora
	row += '<td><input class="form-control text-center shadow-sm" type="time" name="" value="'+ hora +'"></td>';
	//motivo
	row += '<td '+widthBySize+'><input class="form-control text-center shadow-sm" type="text" name="" value="'+ obj.descripcion +'" placeholder="Motivo" ></td>';
	//cliente
	row += '<td class="notShowMobile" style="width:25%; display:none"><input class="form-control text-center shadow-sm" type="text" name="" value="'+ obj.nombreCliente +'" onkeyup="searchClientByName(this.value, this.parentElement.parentElement)" list="dataListClientsCalendar" placeholder="Cliente"><datalist id="dataListClientsCalendar"></datalist></td>';
	//contacto cliente
	contactClient = '';
	if ( obj.socio ){
		contactClient = '<select id="selectClientsCalendar'+obj.idAgenda+'" class="form-select form-control shadow-sm">';

		if ( obj.socio.telefono )
			contactClient += '<option>'+obj.socio.telefono+'</option>';
		if (obj.socio.telefax)
			contactClient += '<option>'+obj.socio.telefax+'</option>';
		if (obj.socio.email)
			contactClient += '<option>'+obj.socio.email+'</option>';
		if (obj.socio.direccion)
			contactClient += '<option>'+obj.socio.direccion+'</option>';

		contactClient += '</select>';
	}else{
		contactClient = '<select class="form-select form-control shadow-sm" disabled></select>';
	}

	row += '<td class="notShowMobile" style="display:none" id="tdRowContactClient'+obj.idAgenda+'">'+contactClient+'</td>';
	//boton ver cliente
	buttonVerSocio = "";
	if ( obj.socio )
		buttonVerSocio = '<a class="btn btn-info subtexto" title="Ver cliente" href="'+getSiteURL()+"ver-socio/"+obj.socio.idSocio+'" value="" target="_blank">Cliente</a>';
	else
		buttonVerSocio = '<button class="btn btn-info subtexto" title="Ver cliente" disabled >Cliente</button>';


	//row += '<td class="notShowMobile">'+buttonVerSocio+'</td>';
	row += '<td class="notShowMobile" style="width:25%; display:none"><input class="form-control text-center shadow-sm" type="text" name="" value="'+ obj.nombreMascota +'" onkeyup="searchPetClientByName(this.value, this.parentElement.parentElement)" list="dataListPetCalendar" placeholder="Mascota"><datalist id="dataListPetCalendar"></datalist></td>';

	//boton ver mascota
	buttonVerMascota = "";
	if ( obj.mascota )
		buttonVerMascota = '<a class="btn btn-info subtexto" title="Ver mascota" href="'+getSiteURL()+"ver-mascota/"+obj.mascota.idMascota+'" value="" target="_blank">ver</a>';
	else
		buttonVerMascota = '<button class="btn btn-info subtexto" title="Ver mascota" disabled >ver</button>';

	//row += '<td class="notShowMobile">'+buttonVerMascota+'</td></tr>';
	row += '<td class="notShowMobile" style="display:none">'+buttonVerSocio+'</td>';

	row += '</tr>';

	return row;
}

function saveEventInCalendar( tr ){
	let day = $("#idInputTodayCalendar").val();
	let hours = tr.getElementsByTagName("input")[0].value;
	let event = tr.getElementsByTagName("input")[1].value;
	let client = tr.getElementsByTagName("input")[2].value.split(" - ")[0];
	let petClient = tr.getElementsByTagName("input")[3].value.split(" - ")[0];

	day = day.replaceAll("-","");
	hours = hours.replaceAll(":","");

	let datetime = day+hours;

	if ( client ){
		let isnum = /^\d+$/.test(client);
		if ( isnum ){
			cleanSelectContactList(tr.id);
			loadClientContactData( client, tr )
		}
	}else{
		cleanSelectContactList(tr.id);
	}


	if ( datetime || event || client || petClient ){
		if ( tr.id ){
			data = {"id":tr.id, "fechaHora": datetime, "descripcion": event, "cliente": client, "mascota": petClient}
			sendAsyncPost("modifyEventCalendarByDay",{event:data})
			.then(( response )=>{
				if ( response.result != 2 ){
					showReplyMessage(response.result, response.message, "Agenda", null);
				}
			});
		}else{
			data = {"fechaHora": datetime, "descripcion": event, "cliente": client, "mascota": petClient}
			sendAsyncPost("saveEventCalendarByDay",{event:data})
			.then(( response )=>{
				if ( response.result != 2 ){
					showReplyMessage(response.result, response.message, "Agenda", null);
				}
				window.location.reload();
			});
		}
	}
}

function clearTableEvents(){
	$("#tbodyCirugiasCalendar").empty();
}

function newRowCirugiaCalendar(){
	row = createCleanRow();
	$("#tbodyCirugiasCalendar").append(row);
}

function createCleanRow(){
	let row = '<tr id="" onchange="saveEventInCalendar(this)">';
	row += '<td><input class="form-control text-center shadow-sm" type="time" name="" value=""></td>'
	row += '<td><input class="form-control text-center shadow-sm" type="text" name="" value="" placeholder="Motivo"></td>'
	row += '<td  class="notShowMobile" style="display:none"><input class="form-control text-center shadow-sm" type="text" name="" value="" onkeyup="searchClientByName(this.value, ``)" list="dataListClientsCalendar" placeholder="Cliente"><datalist id="dataListClientsCalendar"></datalist></td>'
	//row += '<td class="notShowMobile"><button class="btn btn-info subtexto" title="Ver cliente" disabled >ver</button></td>';
	row += '<td><select class="form-select form-control shadow-sm notShowMobile" style="display:none" disabled></select></td>'
	row += '<td class="notShowMobile" style="display:none"><input class="form-control text-center shadow-sm" type="text" name="" value="" onkeyup="searchPetClientByName(this.value, this.parentElement.parentElement)" list="dataListPetCalendar" placeholder="Mascota"><datalist id="dataListPetCalendar"></datalist></td>'
	row += '<td class="notShowMobile" style="display:none"><button class="btn btn-info subtexto" title="Ver mascota" disabled >Cliente</button></td></tr>';

	return row;
}

function searchClientByName( valueCli, tr ){
	if ( valueCli.length > 0 ){
		$('#dataListClientsCalendar').empty();
		sendAsyncPost("searchClientByName", {value: valueCli})
		.then((response)=>{
			if( response.result == 2 ){
				let list = response.listResult;
				for (let i = 0; i < list.length; i++) {
					let option = "<option>"+list[i].idSocio+" - "+list[i].nombre+"</option>";
					$('#dataListClientsCalendar').append(option);
				}
			}
		})
	}else{
		//cleanSelectContactList(tr.id);
		$('#dataListClientsCalendar').empty();
	}
}

function searchPetClientByName( valuePet, tr ){
	let client = tr.getElementsByTagName("input")[2].value.split(" - ")[0];
	$('#dataListPetCalendar').empty();
	sendAsyncPost("searchPetClientByName", {value: valuePet, client:client})
	.then((response)=>{
		if( response.result == 2 ){
			let list = response.listMascotas;
			for (let i = 0; i < list.length; i++) {
				let option = "<option>"+list[i].idMascota+" - "+list[i].nombre+"</option>";
				$('#dataListPetCalendar').append(option);
			}
		}
	})
}


function loadClientContactData( idClient, tr ){
	tr.getElementsByTagName("select")[0];
	//pedir los datos del cliente agregar al select
	sendAsyncPost("getSocio", {idSocio:idClient})
	.then((response)=>{
		if ( response.result == 2 ){
			if (response.socio.telefono){
				let option = document.createElement("option")
				option.append(response.socio.telefono)
				tr.getElementsByTagName("select")[0].appendChild(option);
			}

			if (response.socio.telefax){
				let option = document.createElement("option")
				option.append(response.socio.telefax)
				tr.getElementsByTagName("select")[0].appendChild(option);
			}

			if (response.socio.email){
				let option = document.createElement("option")
				option.append(response.socio.email)
				tr.getElementsByTagName("select")[0].appendChild(option);
			}

			if (response.socio.direccion){
				let option = document.createElement("option")
				option.append(response.socio.direccion)
				tr.getElementsByTagName("select")[0].appendChild(option);
			}
		}
	});

	tr.getElementsByTagName("select")[0].removeAttribute("disabled");
}

function cleanSelectContactList(idrow){

	if ( idrow ){
		$("#"+idrow+" select").attr("disabled", true);
		$("#"+idrow+" select").empty();
	}
}