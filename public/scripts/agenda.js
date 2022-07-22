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

					row = '<tr id="'+ response.listResult[i].idAgenda +'" onchange="saveEventInCalendar(this)"><td><input class="form-control text-center shadow-sm" type="time" name="" value="'+ hora +'"></td><td class="w-50"><input class="form-control text-center shadow-sm" type="text" name="" value="'+ response.listResult[i].descripcion +'" placeholder="Motivo" ></td><td class="notShowMobile"><input class="form-control text-center shadow-sm" type="text" name="" value="'+ response.listResult[i].nombreCliente +'" onkeyup="searchClientByName(this.value)" list="dataListClientsCalendar" placeholder="Cliente"><datalist id="dataListClientsCalendar"></datalist></td><td class="notShowMobile"><input class="form-control text-center shadow-sm" type="text" name="" value="'+ response.listResult[i].nombreMascota +'" onkeyup="searchPetClientByName(this.value, this.parentElement.parentElement)" list="dataListPetCalendar" placeholder="Mascota"><datalist id="dataListPetCalendar"></datalist></td></td></tr>';
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
	row = '<tr id="" onchange="saveEventInCalendar(this)"><td><input class="form-control text-center shadow-sm" type="time" name="" value=""></td><td class="w-50" ><input class="form-control text-center shadow-sm" type="text" name="" value="" placeholder="Motivo" ></td><td  class="notShowMobile"><input class="form-control text-center shadow-sm" type="text" name="" value="" onkeyup="searchClientByName(this.value)" list="dataListClientsCalendar" placeholder="Cliente"><datalist id="dataListClientsCalendar"></datalist></td><td class="notShowMobile"><input class="form-control text-center shadow-sm" type="text" name="" value="" onkeyup="searchPetClientByName(this.value, this.parentElement.parentElement)" list="dataListPetCalendar" placeholder="Mascota"><datalist id="dataListPetCalendar"></datalist></td></tr>';
	$("#tbodyCirugiasCalendar").append(row);
}

function searchClientByName( valueCli ){
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