function getCirugiasByDay( day ){

	//console.log("consultando las cirugias del dia ",day);

	sendAsyncPost("getEventCalendarByDay",{day:day})
	.then(( response )=>{
		//console.log(response);

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
				//console.log("no hay eventos este dia", response);
				clearTableEvents();
			}
		}else{
			//console.log("la respuesta es ", response);
			clearTableEvents();
		}
	})
}


function createRow( obj ){
	//fila
	let row = '<tr id="'+ obj.idAgenda +'" onchange="saveEventInCalendar(this)">';
	//hora
	row += '<td><input class="form-control text-center shadow-sm" type="time" name="" value="'+ hora +'"></td>';
	//motivo
	row += '<td class="w-25"><input class="form-control text-center shadow-sm" type="text" name="" value="'+ obj.descripcion +'" placeholder="Motivo" ></td>';
	//cliente
	row += '<td class="notShowMobile"><input class="form-control text-center shadow-sm" type="text" name="" value="'+ obj.nombreCliente +'" onkeyup="searchClientByName(this.value)" list="dataListClientsCalendar" placeholder="Cliente"><datalist id="dataListClientsCalendar"></datalist></td>';
	//contacto cliente
	contactClient = '';
	if ( obj.socio ){
		contactClient = '<select class="form-select form-control shadow-sm">';

		if ( obj.socio.telefono )
			contactClient += '<option>'+obj.socio.telefono+'</option>';
		if (obj.socio.telefax)
			contactClient += '<option>'+obj.socio.telefax+'</option>';
		if (obj.socio.email)
			contactClient += '<option>'+obj.socio.email+'</option>';

		contactClient += '</select>';
	}else{
		contactClient = '<select class="form-select form-control shadow-sm" disabled></select>';
	}

	row += '<td class="notShowMobile" id="tdRowContactClient'+obj.idAgenda+'">'+contactClient+'</td>';
	//boton ver cliente
	buttonVerSocio = "";
	if ( obj.socio )
		buttonVerSocio = '<a class="btn btn-info subtexto" title="Ver cliente" href="'+getSiteURL()+"ver-socio/"+obj.socio.idSocio+'" value="" target="_blank">Cliente</a>';
	else
		buttonVerSocio = '<button class="btn btn-info subtexto" title="Ver cliente" disabled >Cliente</button>';


	row += '<td class="notShowMobile">'+buttonVerSocio+'</td>';
	row += '<td class="notShowMobile"><input class="form-control text-center shadow-sm" type="text" name="" value="'+ obj.nombreMascota +'" onkeyup="searchPetClientByName(this.value, this.parentElement.parentElement)" list="dataListPetCalendar" placeholder="Mascota"><datalist id="dataListPetCalendar"></datalist></td>';

	//boton ver mascota
	buttonVerMascota = "";
	if ( obj.mascota )
		buttonVerMascota = '<a class="btn btn-info subtexto" title="Ver mascota" href="'+getSiteURL()+"ver-mascota/"+obj.mascota.idMascota+'" value="" target="_blank">Mascota</a>';
	else
		buttonVerMascota = '<button class="btn btn-info subtexto" title="Ver mascota" disabled >Mascota</button>';

	row += '<td class="notShowMobile">'+buttonVerMascota+'</td></tr>';

	return row;
}

function saveEventInCalendar( tr ){
	//console.log(tr)

	let day = $("#idInputTodayCalendar").val();
	let hours = tr.getElementsByTagName("input")[0].value;
	let event = tr.getElementsByTagName("input")[1].value;
	let client = tr.getElementsByTagName("input")[2].value.split(" - ")[0];
	let petClient = tr.getElementsByTagName("input")[3].value.split(" - ")[0];

	day = day.replaceAll("-","");
	hours = hours.replaceAll(":","");

	let datetime = day+hours;

	if ( datetime || event || client || petClient ){
		if ( tr.id ){
			data = {"id":tr.id, "fechaHora": datetime, "descripcion": event, "cliente": client, "mascota": petClient}
			sendAsyncPost("modifyEventCalendarByDay",{event:data})
			.then(( response )=>{
				console.log("se modificó el evento de la cirugia");
			});
		}else{
			data = {"fechaHora": datetime, "descripcion": event, "cliente": client, "mascota": petClient}
			sendAsyncPost("saveEventCalendarByDay",{event:data})
			.then(( response )=>{
				window.location.reload();
			});
		}
	}
}

function clearTableEvents(){
	$("#tbodyCirugiasCalendar").empty();
}

function newRowCirugiaCalendar(){
	row = '<tr id="" onchange="saveEventInCalendar(this)"><td><input class="form-control text-center shadow-sm" type="time" name="" value=""></td><td class="w-50" ><input class="form-control text-center shadow-sm" type="text" name="" value="" placeholder="Motivo" ></td><td  class="notShowMobile"><input class="form-control text-center shadow-sm" type="text" name="" value="" onkeyup="searchClientByName(this.value, ``)" list="dataListClientsCalendar" placeholder="Cliente"><datalist id="dataListClientsCalendar"></datalist></td><td class="notShowMobile"><input class="form-control text-center shadow-sm" type="text" name="" value="" onkeyup="searchPetClientByName(this.value, this.parentElement.parentElement)" list="dataListPetCalendar" placeholder="Mascota"><datalist id="dataListPetCalendar"></datalist></td></tr>';
	$("#tbodyCirugiasCalendar").append(row);
}

function searchClientByName( valueCli, idRow ){
	console.log(valueCli);
	console.log("el dato del cliente cambió, dejar limpia columna de contactos y boton de ver cliente");

	if ( idRow ){
		$("#tdRowContactClient"+idRow+" select").
	}

	if ( valueCli.length > 0 ){
		$('#dataListClientsCalendar').empty();
		sendAsyncPost("searchClientByName", {value: valueCli})
		.then((response)=>{
			console.log(response);
			if( response.result == 2 ){
				let list = response.listResult;
				for (let i = 0; i < list.length; i++) {
					let option = "<option>"+list[i].idSocio+" - "+list[i].nombre+"</option>";
					$('#dataListClientsCalendar').append(option);
				}
			}
		})
	}else
		$('#dataListClientsCalendar').empty();
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