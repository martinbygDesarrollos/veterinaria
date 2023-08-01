//guardar la fecha en cookie cuando se cambia


var widthBySize = 'style="width:20%"';
var lastIndexLimit = 0;
var trSelected = null; //elemento en el que se da click a la lupa
var calendarCategory = null;

$(document).ready(()=>{
	var sizeHeight = window.innerHeight;
	var sizeWidth = window.innerWidth;

	if ( sizeWidth <= 768 ){
		widthBySize = '';
	}

	if(window.location.pathname.includes("cirugia")){
		calendarCategory = "cirugia";
	}else if (window.location.pathname.includes("peluqueria")){
		calendarCategory = "peluqueria";
	}else if (window.location.pathname.includes("domicilios")){
		calendarCategory = "domicilios";
	}else if (window.location.pathname.includes("calendario")){
		calendarCategory = "calendario";
	}else if (window.location.pathname.includes("guarderia")){
		calendarCategory = "guarderia";
	}
});

function getCirugiasByDay( day ){


	document.cookie = "fechaciru="+$("#idInputTodayCalendar").val()


	sendAsyncPost("getEventCalendarByDay",{day:day, type:"cirugia"})
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
	console.log(obj);


	let descripcion = "";
	if ( obj.descripcion ){
		descripcion = obj.descripcion.replaceAll('"', '\'');
	}

	let nomClient = "";
	if ( obj.nombreCliente ){
		nomClient = obj.nombreCliente.replaceAll('"', '\'');
	}

	let nomMascota = "";
	if (obj.nombreMascota){
		nomMascota = obj.nombreMascota.replaceAll('"', '\'');
	}

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

	if (obj.estado == "hecho")
		row += '<td class="notShowMobile"><button class="btn btn-info" title="Tarea terminada" onclick="changeStatusEvent(`'+obj.idAgenda+'`, ``)" ><i class="fas fa-undo"></i></button></td>';
	else
		row += '<td class="notShowMobile"><button class="btn btn-success" title="Tarea terminada" onclick="changeStatusEvent(`'+obj.idAgenda+'`, `hecho`)" ><i class="fas fa-check"></i></button></td>';


	row += '<td class="notShowMobile"><button class="btn btn-danger" title="Eliminar este evento" onclick="deleteEvent(`'+obj.idAgenda+'`)" ><i class="fas fa-trash"></i></button></td>';
	row += '</tr>';

	return row;
}

function saveEventInCalendar( tr ){

	 if ( calendarCategory == "guarderia" ){
		saveEventInCalendarGuarderias(tr)
	}else{

		let day = null;
		if ( calendarCategory == "cirugia" ){
			day = $("#idInputTodayCalendar").val();
		}else if ( calendarCategory == "peluqueria" ){
			day = $("#idInputTodayPelu").val();
		}else if ( calendarCategory == "domicilios" ){
			day = $("#idInputTodayDomi").val();
		}


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
					if ( response.result != 2 ){
						showReplyMessage(response.result, response.message, "Agenda", null);
					}
				});
			}else{
				data = {"fechaHora": datetime, "descripcion": event, "cliente": client, "mascota": petClient}
				sendAsyncPost("saveEventCalendarByDay",{event:data, type:calendarCategory})
				.then(( response )=>{
					if ( response.result != 2 ){
						showReplyMessage(response.result, response.message, "Agenda", null);
					}
					window.location.reload();
				});
			}
		}



	}
}

function clearTableEvents(){

	if ( calendarCategory == "cirugia" ){
		$("#tbodyCirugiasCalendar").empty();
	}else if ( calendarCategory == "peluqueria" ){
		$("#tbodyPeluqueriasCalendar").empty();
	}else if ( calendarCategory == "domicilios" ){
		$("#tbodyDomiciliosCalendar").empty();
	}else if ( calendarCategory == "guarderia" ){
		$("#tbodyGuarderia").empty();
	}
}

function newRowToCalendar(){

	if ( calendarCategory == "cirugia" ){
		row = createCleanRow();
		$("#tbodyCirugiasCalendar").append(row);
	}else if ( calendarCategory == "peluqueria" ){
		row = createCleanRow();
		$("#tbodyPeluqueriasCalendar").append(row);
	}else if ( calendarCategory == "domicilios" ){
		row = createCleanRow();
		$("#tbodyDomiciliosCalendar").append(row);
	}else if ( calendarCategory == "guarderia" ){
		row = createCleanRowGuarderias();
		$("#tbodyGuarderia").prepend(row);
	}
}

function createCleanRow(){
	let row = '<tr id="" onchange="saveEventInCalendar(this)">';
	row += '<td><input class="form-control text-center shadow-sm" type="time" name="" value=""></td>'
	row += '<td><input class="form-control text-center shadow-sm" type="text" name="" value="" placeholder="Motivo"></td>'
	row += '<td  class="notShowMobile" style=""><input class="form-control text-center shadow-sm" type="text" name="" value="" placeholder="Cliente"></td>'
	row += '<td><select class="form-select form-control shadow-sm notShowMobile" style="" disabled></select></td>'
	row += '<td class="notShowMobile" style=""><input class="form-control text-center shadow-sm" type="text" name="" value="" placeholder="Mascota"></td>';
	row += '<td><button class="btn btn-info" disabled><a class="btn-info" title="Enviar whatsapp"target="_blank" value=""><i class="fab fa-whatsapp"></i></a></button></td>';
	row += '<td class="notShowMobile" style=""><button class="btn btn-info" title="Ver cliente" disabled>Cliente</button></td>';
	//row += '<td class="notShowMobile" onclick="openModalSearchClientOrPet(this.parentElement)" ><i class="btn btn-info fas fa-search"></i></td></tr>';
	row += '<td class="notShowMobile" onclick="openModalSearchClientOrPet(this.parentElement)"><button class="btn btn-info" title="Buscar cliente o mascota" ><i class="fas fa-search"></i></button></td>'
	row += '<td class="notShowMobile"><button class="btn btn-warning" title="Tarea terminada" disabled ><i class="fas fa-check"></i></button></td>';
	row += '<td class="notShowMobile"><button class="btn btn-danger" title="Eliminar este evento" disabled ><i class="fas fa-trash"></i></button></td>';
	row += '</tr>';

	return row;
}

function loadClientContactData( telefono, telefax, direccion, email ){

	if ( trSelected ){
		let select = trSelected.getElementsByTagName("select")[0]
		while(select.firstElementChild) {
		    select.firstElementChild.remove();
		}


		if (telefono && telefono != "null" && telefono != ""){
			let option = document.createElement("option")
			option.append(telefono)
			select.appendChild(option);
		}

		if (telefax && telefax != "null" && telefax != ""){
			let option = document.createElement("option")
			option.append(telefax)
			select.appendChild(option);
		}

		if (email && email != "null" && email != ""){
			let option = document.createElement("option")
			option.append(email)
			select.appendChild(option);
		}

		if (direccion && direccion != "null" && direccion != ""){
			let option = document.createElement("option")
			option.append(direccion)
			select.appendChild(option);
		}

		select.removeAttribute("disabled");
	}
}

function cleanSelectContactList(idrow){

	if ( idrow ){
		$("#"+idrow+" select").attr("disabled", true);
		$("#"+idrow+" select").empty();
	}
}

function openModalSearchClientOrPet( tr ){
	trSelected = tr;
	$("#modalSearchClientOrPet input").val("");
	$("#modalSearchClientOrPet tbody").empty();
	if ( tr )
		$("#modalSearchClientOrPet").modal("show");
	else
		$("#modalSearchClientOrPet").modal("hide");
}

function searchDataClienstOrPet( value ){
	if (value.length > 0 ){
		//$("#modalSearchClientOrPet tbody").empty();
		//console.log("indice previo a consultar", lastIndexLimit);
		sendAsyncPost("getClientOrPetByInput", {value: value, indexLimit: lastIndexLimit})
		.then(( response )=>{
			$("#modalSearchClientOrPet tbody").empty();
			if ( response.result == 2 ){
				let list = response.listResult;
				for (var i = 0; i < list.length; i++) {
					let row = createRowDataClientOrPet(list[i]);
					$("#modalSearchClientOrPet tbody").append(row);
				}
			}
		})
	} else
		$("#modalSearchClientOrPet tbody").empty();
}

function createRowDataClientOrPet( obj ){
	let telefono = obj.telefono;
	let telefax = obj.telefax;
	let direccion = obj.direccion;

	if ( obj.telefono == null ) telefono = "";
	if ( obj.telefax == null) telefax = "";
	if ( obj.direccion == null ) direccion = "";

	//agregar color a la fila de la tabla según la deuda del cliente
	tipoClient = calculateColorRowByClient(obj.tipo, obj.deudor);



	let nomMascota = "";
	let hrefMascota = "";
	if (obj.nomMascota != null &&  obj.nomMascota != "" ){
		nomMascota = obj.nomMascota;
		hrefMascota = 'href="'+getSiteURL()+'ver-mascota/'+obj.idMascota+'"';
	}

	let row = "";

	//si hay fecha de muerte agregar que esta fallecida la mascota
	mascotaViva = "";
	if ( obj.fechaFallecimiento != null && obj.fechaFallecimiento != "" ){
		mascotaViva = "FALLECIDO ";
		row = '<tr class="'+tipoClient.class+' subtexto"  style="color:red; font-weight: bold;">';

		row += '<td class="">'+obj.idSocio+'</td>';
		row += '<td><a href="'+getSiteURL()+'ver-socio/'+obj.idSocio+'" style="color:red; font-weight: bold;" >'+obj.nomSocio+'</a></td>';
		row += '<td><a '+hrefMascota+' style="color:red; font-weight: bold;" >'+mascotaViva+nomMascota+'</a></td>';
	}else {
		row = '<tr class="'+tipoClient.class+'">';
		row += '<td class="subtexto">'+obj.idSocio+'</td>';
		row += '<td><a href="'+getSiteURL()+'ver-socio/'+obj.idSocio+'" >'+obj.nomSocio+'</a></td>';
		row += '<td><a '+hrefMascota+' >'+mascotaViva+nomMascota+'</a></td>';
	}


	/*row += '<td class="subtexto">'+obj.idSocio+'</td>';
	row += '<td><a href="'+getSiteURL()+'ver-socio/'+obj.idSocio+'" >'+obj.nomSocio+'</a></td>';
	row += '<td><a '+hrefMascota+' >'+mascotaViva+nomMascota+'</a></td>';*/
	row += '<td>'+telefono+' '+telefax+'</td>';
	row += '<td>'+direccion+'</td>';

	if ( obj.fechaFallecimiento != null && obj.fechaFallecimiento != "" )
		row += '<td><button class="btn btn-info subtexto" disabled ><i class="fas fa-plus-circle"></i></button></td></tr>';
	else{
		let nom = encodeURI(obj.nomSocio) //escapa las comillas y otros caracteres que pueda haber
		let nomMascota = encodeURI(obj.nomMascota);
		let telefono = encodeURI(obj.telefono);
		let telefax = encodeURI(obj.telefax);
		let direccion = encodeURI(obj.direccion);
		let email = encodeURI(obj.email);

		row += '<td><button class="btn btn-info subtexto" onclick="addClientsCalendarRow('+obj.idSocio+', `'+nom+'`, '+obj.idMascota+', `'+nomMascota+'`, `'+telefono+'`, `'+telefax+'`, `'+direccion+'`, `'+email+'`)" ><i class="fas fa-plus-circle"></i></button></td></tr>';
	}


	//console.log(row);
	return row;
}

function addClientsCalendarRow( idClient, nomClient, idMascota, nomMascota, tel, telefax, direccion, email ){

	nomClient = decodeURI(nomClient)
	nomMascota = decodeURI(nomMascota)
	tel = encodeURI(tel);
	telefax = encodeURI(telefax);
	direccion = decodeURI(direccion);
	email = encodeURI(email);


	if ( calendarCategory == "guarderia" ){
		//loadClientContactDataGuarderia(tel, telefax, direccion, email);
		trSelected.getElementsByTagName("input")[0].value = idClient +" - "+nomClient;
		if ( idMascota != null && idMascota  )
			trSelected.getElementsByTagName("input")[1].value = idMascota +" - "+nomMascota;

		//fecha de entrada
		trSelected.getElementsByTagName("input")[2].value = getCurrentDate();

		//fecha de salida
		trSelected.getElementsByTagName("input")[3].value = getCurrentDate();

		trSelected.getElementsByTagName("a")[0].setAttribute("href", getSiteURL()+"ver-socio/"+idClient)
		trSelected.getElementsByTagName("a")[0].removeAttribute("disabled");


	}else{
		trSelected.getElementsByTagName("input")[2].value = idClient +" - "+nomClient;
		if ( idMascota != null && idMascota  )
			trSelected.getElementsByTagName("input")[3].value = idMascota +" - "+nomMascota;

		trSelected.getElementsByTagName("a")[0].setAttribute("href", getSiteURL()+"ver-socio/"+idClient)
		trSelected.getElementsByTagName("a")[0].removeAttribute("disabled");
	}

	loadClientContactData(tel, telefax, direccion, email);

	var evt = document.createEvent("HTMLEvents");
    evt.initEvent("change", false, true);
	trSelected.dispatchEvent(evt);


	openModalSearchClientOrPet(null); //como el parametro es null entonces se cierra el modal
}


//SCROLL TABLA BODY RESULTADOS PARA AGREGAR CLIENTE A SECCION CIRUGIAS
$('#modalSearchClientOrPet .tableCustomScroll').on('scroll', function() {
	/*console.log("el datos del last index ", lastIndexLimit);
	console.log("en el scroll");
	console.log("antes de cambiar el lastIndexLimit ", lastIndexLimit);

	/*if ( response.newIndexLimit != lastIndexLimit ){
		console.log(response.newIndexLimit);
		lastIndexLimit = response.newIndexLimit;
	}*/
	/*lastIndexLimit = $('#modalSearchClientOrPet tbody').children().length;
	console.log("se cambio el lastIndexLimit ", lastIndexLimit);

	//console.log($('#modalSearchClientOrPet input').val());
	searchDataClienstOrPet($('#modalSearchClientOrPet input').val());*/
})


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
var widthBySize = 'style="width:20%"';
var lastIndexLimit = 0;
var trSelected = null; //elemento en el que se da click a la lupa*/

function getPeluqueriasByDay( day ){

	document.cookie = "fechapelu="+$("#idInputTodayPelu").val()


	sendAsyncPost("getEventCalendarByDay",{day:day, type:"peluqueria"})
	.then(( response )=>{
		console.log(response);
		if ( response.result == 2 ){
			if ( response.listResult.length > 0){
				clearTableEvents();

				for (var i = 0; i < response.listResult.length; i++) {
					if (!response.listResult[i].hora)
						hora = "00:00"
					else hora = response.listResult[i].hora;
					row = createRow(response.listResult[i]);
					$("#tbodyPeluqueriasCalendar").append(row);
				}
			}else {
				clearTableEvents();
			}
		}else{
			clearTableEvents();
		}
	})
}


function newRowPeluqueriasCalendar(){
	row = createCleanRow();
	$("#tbodyPeluqueriasCalendar").append(row);
}


function saveNotesCalendarByDay(){
	let value = $("#textareaCalendarNotes").val();
	console.log(value);

	calendarCategory = "calendario";
	datetime = $("#idInputCalendarNotes").val();
	datetime = datetime.replaceAll("-","");

	data = {"fechaHora": datetime, "descripcion": value, "cliente": null, "mascota": null}
	sendAsyncPost("saveEventCalendarByDay",{event:data, type:calendarCategory})
	.then(( response )=>{
		if ( response.result != 2 ){
			showReplyMessage(response.result, response.message, "Agenda", null);
		}
		window.location.reload();
	});
}


function getCalendarNotesByDay( day ){


	document.cookie = "fechacal="+$("#idInputCalendarNotes").val()


	sendAsyncPost("getEventCalendarByDay",{day:day, type:"calendario"})
	.then(( response )=>{
		if ( response.result == 2 ){
			console.log(response);
			if ( response.listResult.length >0 ){
				$("#textareaCalendarNotes").val(response.listResult[0].descripcion)
			}else $("#textareaCalendarNotes").val("");
		}else $("#textareaCalendarNotes").val("");
	})
}


function getDomiciliosByDay( day ){


	document.cookie = "fechadomi="+$("#idInputTodayDomi").val()


	sendAsyncPost("getEventCalendarByDay",{day:day, type:"domicilios"})
	.then(( response )=>{
		if ( response.result == 2 ){
			if ( response.listResult.length > 0){
				clearTableEvents();

				for (var i = 0; i < response.listResult.length; i++) {
					if (!response.listResult[i].hora)
						hora = "00:00"
					else hora = response.listResult[i].hora;
					row = createRow(response.listResult[i]);
					$("#tbodyDomiciliosCalendar").append(row);
				}
			}else {
				clearTableEvents();
			}
		}else{
			clearTableEvents();
		}
	})
}



function deleteEvent( idEvent ){

	console.log("eliminando evento ", idEvent);


	sendAsyncPost("deleteEvent", {idEvent:idEvent})
	.then(( response )=>{
		console.log(response);
		if ( response.result == 2 ){
			window.location.reload();
		}
	})
}



function changeStatusEvent( idAgenda, estado ){

	sendAsyncPost("changeStatusEvent", {idEvent:idAgenda, status:estado})
	.then((response)=>{
		window.location.reload();
	})

}