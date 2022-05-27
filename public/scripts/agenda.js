function getCirugiasByDay( day ){

	console.log("consultando las cirugias del dia ",day);

	sendAsyncPost("getEventCalendarByDay",{day:day})
	.then(( response )=>{
		console.log(response);

		if ( response.result == 2 ){
			if ( response.listResult.length > 0){
				clearTableEvents();

				for (var i = 0; i < response.listResult.length; i++) {
					row = '<tr id="'+ response.listResult[i].idAgenda +'" onchange="saveEventInCalendar(this)"><td><input class="form-control text-center shadow-sm" type="time" name="" value="'+ response.listResult[i].hora +'"></td><td><input class="form-control text-center shadow-sm" type="text" name="" value="'+ response.listResult[i].descripcion +'"></td></tr>';
					$("#tbodyCirugiasCalendar").append(row);
				}
			}else {
				console.log("no hay eventos este dia", response);
				clearTableEvents();
			}
		}else{
			console.log("la respuesta es ", response);
			clearTableEvents();
		}
	})
}

function saveEventInCalendar( tr ){
	console.log(tr)

	let day = $("#idInputTodayCalendar").val();
	let hours = tr.getElementsByTagName("input")[0].value;
	let event = tr.getElementsByTagName("input")[1].value;

	day = day.replaceAll("-","");
	hours = hours.replaceAll(":","");

	let datetime = day+hours;
	console.log(datetime);

	if ( datetime && event ){

		if ( tr.id ){
			data = {"id":tr.id, "fechaHora": datetime, "descripcion": event}
			sendAsyncPost("modifyEventCalendarByDay",{event:data})
			.then(( response )=>{
				console.log("se modificó el evento de la cirugia");
				console.log(response);
			});
		}else{
			data = {"fechaHora": datetime, "descripcion": event}
			sendAsyncPost("saveEventCalendarByDay",{event:data})
			.then(( response )=>{
				console.log("se creó nuevo evento en la agenda");
				console.log(response);
				window.location.reload();
			});
		}
	}
}

function clearTableEvents(){
	$("#tbodyCirugiasCalendar").empty();
}

function newRowCirugiaCalendar(){
	row = '<tr id="" onchange="saveEventInCalendar(this)"><td><input class="form-control text-center shadow-sm" type="time" name="" value=""></td><td><input class="form-control text-center shadow-sm" type="text" name="" value=""></td></tr>';
	$("#tbodyCirugiasCalendar").append(row);
}