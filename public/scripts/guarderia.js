var pagination = 0;

function cargarTablaGuarderia(){
	console.log("cargar todo lo que está en agenda guarderia que no tenga fecha de salida");
	console.log("boton para seleccionar mascota fecha de entrada");


	//esto es un listado de todo lo que hay en agenda que sea de guarderias, está paginado por un limPagina pero no se aplica ningun filtro ni busqueda aún

	sendAsyncPost("getGuarderias", {pagination:pagination})
	.then(( response )=>{
		console.log(response);
		if ( response.result == 2 ){
			const list = response.listResult;
			if ( list.length > 0){
				//pagination = response.pagination;
				clearTableEvents();
				list.map((obj)=>{
					row = createRowGuarderias(obj);
					$("#tbodyGuarderia").append(row);
				})
			}else {
				clearTableEvents();
			}
		}else{
			clearTableEvents();
		}
	})
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
			sendAsyncPost("modifyGuarderiaByDay",data)
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



function createRowGuarderias( obj ){

	let nomClient = "";
	if (obj.nombreCliente)
		nomClient = obj.nombreCliente.replaceAll('"', '\'');

	let nomMascota = "";
	if (obj.nombreCliente)
		nomMascota = obj.nombreMascota.replaceAll('"', '\'');

	let wppBtn = '<button class="btn btn-info" disabled><a class="btn-info" title="Enviar whatsapp"target="_blank" value=""><i class="fab fa-whatsapp"></i></a></button>';

	let row = '<tr id="'+ obj.idAgenda +'" onchange="saveEventInCalendar(this)">';

	row += '<td><input class="form-control text-center shadow-sm" type="text" value="'+ nomClient +'" placeholder="Cliente"></td>';


	contactClient = '';
	if ( obj.socio ){
		contactClient = '<select id="selectClientsCalendar'+obj.idAgenda+'" class="form-select form-control shadow-sm">';
		if ( obj.socio.telefono ){
			if ( obj.socio.telefono.length >= 9 ){
				wppBtn = '<a class="btn btn-info" title="Enviar whatsapp al '+obj.socio.telefono+'" href="https://wa.me/'+obj.socio.telefono+'" target="_blank" value=""><i class="fab fa-whatsapp"></i></a>'
			}
			contactClient += '<option>'+obj.socio.telefono+'</option>';
		}
		if (obj.socio.telefax){
			if ( obj.socio.telefax.length >= 9 ){
				wppBtn = '<a class="btn btn-info" title="Enviar whatsapp al '+obj.socio.telefax+'" href="https://wa.me/'+obj.socio.telefax+'" target="_blank" value=""><i class="fab fa-whatsapp"></i></a>'
			}
			contactClient += '<option>'+obj.socio.telefax+'</option>';
		}
		if (obj.socio.email)
			contactClient += '<option>'+obj.socio.email+'</option>';
		if (obj.socio.direccion)
			contactClient += '<option>'+obj.socio.direccion+'</option>';

		contactClient += '</select>';
	}else{
		contactClient = '<select class="form-select form-control shadow-sm" disabled></select>';
	}

	row += '<td class="notShowMobile" style="" id="tdRowContactClient'+obj.idAgenda+'">'+contactClient+'</td>';

	row += '<td class="" style=""><input class="form-control text-center shadow-sm" type="text" name="" value="'+obj.nombreMascota+'"></td>';

	if (obj.entrada)
		row += '<td class="" style=""><input class="form-control text-center shadow-sm" type="date" name="" value="'+obj.entrada+'" ></td>';
	else
		row += '<td class="" style=""><input class="form-control text-center shadow-sm" type="date" name="" value="" ></td>';

	if (obj.salida)
		row += '<td class="" style=""><input class="form-control text-center shadow-sm" type="date" name="" value="'+obj.salida+'" ></td>';
	else
		row += '<td class="" style=""><input class="form-control text-center shadow-sm" type="date" name="" value="" ></td>';


	row += '<td>'+wppBtn+'</td>';


	buttonVerSocio = '<button class="btn btn-info" title="Ver cliente" disabled>Cliente</button>';
	if ( obj.socio )
		buttonVerSocio = '<a class="btn btn-info" title="Ver cliente" href="'+getSiteURL()+"ver-socio/"+obj.socio.idSocio+'" value="" target="_blank">Cliente</a>';
	row += '<td class="notShowMobile" style="">'+buttonVerSocio+'</td>';

	row += '<td class="notShowMobile"><button class="btn btn-info" title="Buscar cliente o mascota" onclick="openModalSearchClientOrPet(this.parentElement.parentElement)" ><i class="fas fa-search"></i></button></td>';

	row += '<td class="notShowMobile"><button class="btn btn-danger" title="Eliminar este evento" onclick="deleteEvent(`'+obj.idAgenda+'`)" ><i class="fas fa-trash"></i></button></td>';
	row += '</tr>';

	return row;
}



function createRow( obj ){
	console.log(obj);
	let descripcion = obj.descripcion.replaceAll('"', '\'');
	let nomClient = obj.nombreCliente.replaceAll('"', '\'');
	let nomMascota = obj.nombreMascota.replaceAll('"', '\'');

	let wppBtn = '<button class="btn btn-info" disabled><a class="btn-info" title="Enviar whatsapp"target="_blank" value=""><i class="fab fa-whatsapp"></i></a></button>';

	let tareaCompletada = "";
	if (obj.estado == "hecho")
		tareaCompletada = "tareaCompletada";

	//fila
	let row = '<tr id="'+ obj.idAgenda +'" class="'+tareaCompletada+'" onchange="saveEventInCalendar(this)">';
	//hora
	row += '<td><input class="form-control text-center shadow-sm" type="time" name="" value="'+ hora +'"></td>';
	//motivo
	row += '<td '+widthBySize+'><input class="form-control text-c enter shadow-sm" type="text" name="" value="'+ descripcion +'" placeholder="Motivo" ></td>';
	//cliente
	row += '<td class="notShowMobile" style="width:25%; "><input class="form-control text-center shadow-sm" type="text" value="'+ nomClient +'" placeholder="Cliente"></td>';
	//contacto cliente
	contactClient = '';
	if ( obj.socio ){
		contactClient = '<select id="selectClientsCalendar'+obj.idAgenda+'" class="form-select form-control shadow-sm">';
		if ( obj.socio.telefono ){
			if ( obj.socio.telefono.length >= 9 ){
				wppBtn = '<a class="btn btn-info" title="Enviar whatsapp al '+obj.socio.telefono+'" href="https://wa.me/'+obj.socio.telefono+'" target="_blank" value=""><i class="fab fa-whatsapp"></i></a>'
			}
			contactClient += '<option>'+obj.socio.telefono+'</option>';
		}
		if (obj.socio.telefax){
			if ( obj.socio.telefax.length >= 9 ){
				wppBtn = '<a class="btn btn-info" title="Enviar whatsapp al '+obj.socio.telefax+'" href="https://wa.me/'+obj.socio.telefax+'" target="_blank" value=""><i class="fab fa-whatsapp"></i></a>'
			}
			contactClient += '<option>'+obj.socio.telefax+'</option>';
		}
		if (obj.socio.email)
			contactClient += '<option>'+obj.socio.email+'</option>';
		if (obj.socio.direccion)
			contactClient += '<option>'+obj.socio.direccion+'</option>';

		contactClient += '</select>';
	}else{
		contactClient = '<select class="form-select form-control shadow-sm" disabled></select>';
	}

	row += '<td class="notShowMobile" style="" id="tdRowContactClient'+obj.idAgenda+'">'+contactClient+'</td>';
	//boton ver cliente
	buttonVerSocio = '<button class="btn btn-info" title="Ver cliente" disabled>Cliente</button>';
	if ( obj.socio )
		buttonVerSocio = '<a class="btn btn-info" title="Ver cliente" href="'+getSiteURL()+"ver-socio/"+obj.socio.idSocio+'" value="" target="_blank">Cliente</a>';


	//row += '<td class="notShowMobile">'+buttonVerSocio+'</td>';
	row += '<td class="notShowMobile" style="width:25%; "><input class="form-control text-center shadow-sm" type="text" name="" value="'+ nomMascota +'" placeholder="Mascota"></td>';

	row += '<td>'+wppBtn+'</td>';
	row += '<td class="notShowMobile" style="">'+buttonVerSocio+'</td>';
	row += '<td class="notShowMobile"><button class="btn btn-info" title="Buscar cliente o mascota" onclick="openModalSearchClientOrPet(this.parentElement.parentElement)" ><i class="fas fa-search"></i></button></td>';

	row += '<td class="notShowMobile"><button class="btn btn-danger" title="Eliminar este evento" onclick="deleteEvent(`'+obj.idAgenda+'`)" ><i class="fas fa-trash"></i></button></td>';
	row += '</tr>';

	return row;
}