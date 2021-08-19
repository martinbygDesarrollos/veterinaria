let lastId = 0;

function cargarHistorialUsuario(){
	let response = sendPost("getHistorialUsuario", {lastId: lastId});
	if(response.result == 2){
		if(lastId != response.lastId)
			lastId = response.lastId;
		let list = response.listResult;
		for (let i = 0; i < list.length; i++) {
			let row = createRow(list[i].fecha, list[i].idSocio, list[i].socio, list[i].idMascota, list[i].mascota, list[i].funcion, list[i].observacion);
			$('#tbodyHistorialUsuario').append(row);
		}
	}
}

function createRow(fecha, idSocio, socio, idMascota, mascota, funcion, observacion){
	let row = "<tr>";
	row += "<td class='text-center'>"+ fecha +"</td>";
	row += "<td class='text-center'>"+ socio +"</td>";
	row += "<td class='text-center'>"+ mascota +"</td>";
	row += "<td class='text-center'>"+ funcion +"</td>";
	row += "<td class='text-center'>"+ observacion +"</td>";
	row += "</tr>";
	return row;
}
