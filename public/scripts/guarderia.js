function cargarTablaGuarderia(){
	console.log("cargar todo lo que está en agenda guarderia que no tenga fecha de salida");
	console.log("boton para seleccionar mascota fecha de entrada");


	/*select * from agenda
	where categoria = "guarderia" and (estado <> "eliminado" or estado is null ) and guarderiaSalida is null*/
}

function createCleanRowGuarderias(){
	let row = '<tr id="" onchange="saveEventInCalendar(this)">';
	row += '<td style=""><input class="form-control text-center shadow-sm" type="text" name="" value="" placeholder="Cliente"></td>'
	row += '<td><select class="form-select form-control shadow-sm notShowMobile" style="" disabled></select></td>'
	row += '<td class="" style=""><input class="form-control text-center shadow-sm" type="text" name="" value="" placeholder="Mascota"></td>';
	row += '<td class="" style=""><input class="form-control text-center shadow-sm" type="date" name="" value="" ></td>';
	row += '<td class="" style=""><input class="form-control text-center shadow-sm" type="date" name="" value="" ></td>';
	row += '<td><button class="btn btn-info" disabled><a class="btn-info" title="Enviar whatsapp"target="_blank" value=""><i class="fab fa-whatsapp"></i></a></button></td>';
	row += '<td class="notShowMobile" style=""><button class="btn btn-info" title="Ver cliente" disabled>Cliente</button></td>';
	row += '<td class="notShowMobile" onclick="openModalSearchClientOrPet(this.parentElement)"><button class="btn btn-info" title="Buscar cliente o mascota" ><i class="fas fa-search"></i></button></td>'
	row += '<td class="notShowMobile"><button class="btn btn-danger" title="Eliminar este evento" disabled ><i class="fas fa-trash"></i></button></td>';
	row += '</tr>';

	return row;
}



function saveEventInCalendarGuarderias(tr){

	let client = tr.getElementsByTagName("input")[0].value.split(" - ")[0];
	let petClient = tr.getElementsByTagName("input")[1].value.split(" - ")[0];
	let dateInit = tr.getElementsByTagName("input")[2].value;
	let dateFinish = tr.getElementsByTagName("input")[3].value;

	dateInit = dateInit.replaceAll("-", "");
	dateFinish = dateFinish.replaceAll("-", "");

	if ( client || petClient ){
		if ( tr.id ){
			data = {"id":tr.id, "cliente": client, "mascota": petClient, "entrada": dateInit, "salida": dateFinish}
			sendAsyncPost("modifyGuarderiaByDay",{event:data})
			.then(( response )=>{
				if ( response.result != 2 ){
					showReplyMessage(response.result, response.message, "Agenda", null);
				}
			});
		}else{
			data = {"cliente": client, "mascota": petClient, "entrada": dateInit, "salida": dateFinish}
			sendAsyncPost("saveGuarderiaByDay",data)
			.then(( response )=>{
				if ( response.result != 2 ){
					showReplyMessage(response.result, response.message, "Agenda", null);
				}
				window.location.reload();
			});
		}
	}

}