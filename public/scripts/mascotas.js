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


	let row = "<tr onclick='redirectToMascota("+ idMascota +")'";
	if ( socioDeudor )
		row += "class='rowWarning' >";
	else
		row += " > ";

	row += "<td class='text-center'>" + nombre +"</td>";
	row += "<td class='text-center'"

	//>" + socioNombre +"</td>";



	if ( fechaUltimaCuota != "" ){
		if ( socioDeudor && fechaUltimoPago != "" )
			row += ' title="Último mes pago '+ fechaUltimaCuota +' el día '+ fechaUltimoPago +'" >'+socioNombre+' <i class="fas fa-exclamation-triangle"></i></td>';
		else if (socioDeudor && fechaUltimoPago == "" )
			row += ' title="No se encontró fecha del último pago" >'+socioNombre+' <i class="fas fa-exclamation-triangle"></i></td>';
		else if ( !socioDeudor )
			row += ">"+socioNombre+'</td>';
	}
	else
		row+= ">"+socioNombre + "</td>";


	row += "<td class='text-center'>" + especie +"</td>";
	//row += "<td class='text-center'>" + raza + "</td>";
	row += "<td class='text-center'>" + sexo +"</td>";
	row += "<td class='text-center'>" + fechaNacimiento +"</td>";
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