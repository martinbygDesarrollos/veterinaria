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
			let row = createRow(list[i].idMascota, list[i].nombre, list[i].especie, list[i].raza, list[i].sexo, list[i].fechaNacimiento);
			$('#tbodyMascotas').append(row);
		}
	}
}

function createRow(idMascota, nombre, especie, raza, sexo, fechaNacimiento){
	let row = "<tr onclick='redirectToMascota("+ idMascota +")'>";

	row += "<td class='text-center'>" + nombre +"</td>";
	row += "<td class='text-center'>" + especie +"</td>";
	row += "<td class='text-center'>" + raza + "</td>";
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