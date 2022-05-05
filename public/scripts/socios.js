let lastId = 0;
let textToSearch = null;
let estado = 1;

function cargarTablaSocios(){

	let response = sendPost("getSociosPagina", {lastId: lastId, textToSearch: textToSearch, estado: estado});
	if(response.result == 2){
		if(response.lastId !=  lastId){
			lastId = response.lastId;
			let list = response.socios;
			for(let i = 0; i < list.length ; i ++){
				let row = createRow(list[i].idSocio, list[i].nombre, list[i].telefono, list[i].cantMascotas, list[i].cuota, list[i].fechaUltimaCuota, list[i].direccion);
				$('#tbodySocios').append(row);
			}
		}
	}
}

function createRow(idSocio, nombre, telefono, cantMascotas, cuota, fechaUltimaCuota, direccion){

	if ( !telefono ){
		telefono = "";
	}

	if ( !direccion ){
		direccion = "";
	}

	let row = "<tr>";
	row += "<td onclick='redirectToSocio("+ idSocio +")' class='text-center'>"+ idSocio +"</td>";
	row += "<td onclick='redirectToSocio("+ idSocio +")' class='text-center'>"+ nombre +"</td>";
	row += "<td onclick='redirectToSocio("+ idSocio +")'class='text-center'>"+ telefono +"</td>";
	row += "<td onclick='redirectToSocio("+ idSocio +")' class='text-center'>"+ direccion +"</td>";
	row += "<td onclick='redirectToSocio("+ idSocio +")' class='text-center'>" + cantMascotas + "</td>";
	row += "<td onclick='redirectToSocio("+ idSocio +")' class='text-center'>" + cuota + "</td>";
	row += "<td onclick='redirectToSocio("+ idSocio +")' class='text-center'>" + fechaUltimaCuota + "</td>";
	row += "<td class='text-center'><a class='text-dark' data-toggle='tooltip' data-placement='top' title='Agregar nueva mascota' href='" + getSiteURL() + "nueva-mascota/" + idSocio +"'><i class='fas fa-paw'></i></a></td>";
	row += "</tr>";

	return row;
}

function redirectToSocio(idSocio){
	window.location.href = getSiteURL() + "ver-socio/"+ idSocio;
}

function buscarSocio(){
	let textTemp = $('#textToSearch').val() || null;

	if(textTemp){
		if(textTemp.length > 3){
			textToSearch = textTemp;
			lastId = 0;
			$('#tbodySocios').empty();
			cargarTablaSocios();
			return;
		}
	}
	textToSearch = null;
	lastId = 0;
	$('#tbodySocios').empty();
	cargarTablaSocios();
}

function changeSociosState(){
	if($('#inputActiveSocio').is(':checked'))
		estado = 1;
	else
		estado = 0;

	textToSearch = null;
	lastId = 0;
	$('#tbodySocios').empty();
	cargarTablaSocios();
}