let lastId = 0;
let textToSearch = null;
let stateMascota = 1;

function cargarTablaMascotas(){

	let response = sendPost('getMascotasPagina', {lastId: lastId, textToSearch: textToSearch, stateMascota: stateMascota});
	if(response.result == 2){
		if(response.lastId != lastId)
			lastId = response.lastId;
		let list = response.listMascotas;
		for (var i = 0; i < list.length; i++) {
			let row = createRow(list[i]);
			$('#tbodyMascotas').append(row);
		}
	}
}

function createRow(obj){
	//console.log(obj);
	idMascota = obj.idMascota
	nombre = obj.nombre
	especie	 = obj.especie
	sexo = obj.sexo
	fechaNacimiento = obj.fechaNacimiento

	socioNombre = obj.nombreSocio;
	if (socioNombre == null)
		socioNombre = "";

	fechaUltimaCuota = obj.fechaUltimaCuota
	fechaUltimoPago = obj.fechaUltimoPago
	socioDeudor = obj.socioDeudor
	socioTipo = obj.socioTipo;
	socioActivo = obj.socioActivo;
	telefono = obj.telefono;
	telefax = obj.telefax;
	buenPagador = obj.buenPagador;

	wppBtn = '<td class="text-center"><button title="Enviar whatsapp" class="btn bg-light" disabled><i class="fab fa-whatsapp"></i></button></td>';

	if ( telefono ){
		if (telefono.length >= 9){
			wppBtn = '<td class="text-center"><a href="https://wa.me/'+telefono+'" target="_blank"><button title="Enviar mensaje al '+telefono+'" class="btn bg-light"><i class="fab fa-whatsapp"></i></button></a></td>';
		}
	}



	if ( telefax ){
		if (telefax.length >= 9){
			wppBtn = '<td class="text-center"><a href="https://wa.me/'+telefax+'" target="_blank"><button title="Enviar mensaje al '+telefax+'" class="btn bg-light"><i class="fab fa-whatsapp"></i></button></a></td>';
		}
	}


	let  tipoCliente = "";

	if ( tipoCliente == 0 ) tipoCliente = "<br>(Inactivo)";

	classForClient = "";

	if ( socioTipo == 0 ){ //NO SOCIO
		tipoCliente = "<br>(Cliente)";
		classForClient = "rowNosocio";
	}else if ( socioTipo == 1 ){ //SOCIO
		tipoCliente = "<br>(Socio)";
		classForClient = "rowSocio";
		if ( socioDeudor )
			classForClient = "rowWarning";
	}else if ( socioTipo == 3 ){ //EX SOCIO
		tipoCliente = "<br>(Ex socio)";
		if ( socioDeudor )
			classForClient = "rowExsocioWarning";
		else
			classForClient = "rowExsocio";
	}else if ( socioTipo == 2 ) //ONG
		tipoCliente = "<br>(ONG)";


	let row = "";

	if (buenPagador === 1)
		classForClient += "BuenPagador"

	if ( obj.fechaFallecimiento ){
		if ( obj.fechaFallecimiento.length > 0 ){
			row = "<tr class='"+classForClient+" subtexto col-1' style='color:red; font-weight: bold;' >";

			row += "<td class='text-center col-1' onclick='redirectToMascota("+ idMascota +")'>FALLECIDO ";
		}
	}else{
		row = "<tr class='"+classForClient+"' >";
		row += "<td class='text-center' onclick='redirectToMascota("+ idMascota +")'>";
	}

	row += nombre +"</td>";
	row += "<td class='text-center' onclick='redirectToMascota("+ idMascota +")'"



	if ( fechaUltimaCuota != "" ){
		if ( socioDeudor && fechaUltimoPago != "" )
			row += ' title="Pago pendiente '+ fechaUltimaCuota +', último movimiento el día '+ fechaUltimoPago +'" >'+socioNombre+ tipoCliente+' <i class="fas fa-exclamation-triangle"></i></td>';
		else if (socioDeudor && fechaUltimoPago == "" )
			row += ' title="No se encontró fecha del último pago" >'+socioNombre+tipoCliente+' <i class="fas fa-exclamation-triangle"></i></td>';
		else if ( !socioDeudor )
			row += ">"+socioNombre+tipoCliente+'</td>';
	}
	else
		row+= ">"+socioNombre + tipoCliente+"</td>";


	row += "<td class='text-center notShowMobile' onclick='redirectToMascota("+ idMascota +")'>" + especie +"</td>";
	row += "<td class='text-center notShowMobile' onclick='redirectToMascota("+ idMascota +")'>" + sexo +"</td>";
	row += "<td class='text-center notShowMobile' onclick='redirectToMascota("+ idMascota +")'>" + fechaNacimiento +"</td>";

	row += wppBtn;
	row += "</tr>";

	return row;
}

function redirectToMascota(idMascota){
	window.location.href = getSiteURL() + "ver-mascota/" + idMascota;
}

function searchMascota(inputSearch){
	let textTemp = inputSearch.value;
	if(textTemp){
		if(textTemp.length > 2){
			lastId = 0;
			textToSearch = textTemp;
			$('#tbodyMascotas').empty();
			cargarTablaMascotas();
			return;
		}
	}
	lastId = 0;
	textToSearch = null;
	$('#tbodyMascotas').empty();
	cargarTablaMascotas();
}

function selectTypeMascota(selectMascota){
	stateMascota = selectMascota.value;
	lastId = 0;
	$('#tbodyMascotas').empty();
	cargarTablaMascotas();
}