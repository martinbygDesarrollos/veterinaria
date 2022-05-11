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
				let row = createRow(list[i]);
				$('#tbodySocios').append(row);
			}
		}
	}
}

function createRow(obj){
	let idSocio = obj.idSocio
	let nombre = obj.nombre
	let telefono = obj.telefono
	let cantMascotas = obj.cantMascotas
	let cuota = obj.cuota
	let fechaUltimoPago = obj.fechaUltimoPago
	let fechaUltimaCuota = obj.fechaUltimaCuota
	let direccion = obj.direccion
	let deudor = obj.deudor
	let deudorFecha = obj.deudorFecha
	console.log(idSocio, deudor);

	if ( !telefono ){
		telefono = "";
	}

	if ( !direccion ){
		direccion = "";
	}

	let row = "<tr onclick='redirectToSocio("+ idSocio +")' "
	if ( deudor ){
		console.log("cambio de color de la linea ");
		row += "class='rowWarning' >";
	}
	else
		row += " > ";

	row += "<td class='text-center'>"+ idSocio +"</td>";
	row += "<td class='text-center'>"+ nombre +"</td>";
	row += "<td class='text-center'>"+ telefono +"</td>";
	row += "<td class='text-center'>"+ direccion +"</td>";
	row += "<td class='text-center'>" + cantMascotas + "</td>";
	row += "<td class='text-center'>"+ cuota + "</td>";
	row += "<td class='text-center' "

	if ( fechaUltimaCuota != "" ){
		if ( deudor && deudorFecha != "" )
			row += ' title="Último mes pago '+ fechaUltimaCuota +' el día '+ deudorFecha +'" ><i class="fas fa-exclamation-triangle"></i> '+fechaUltimaCuota+'</td>';
		else if (deudor && deudorFecha == "" )
			row += ' title="No se encontró fecha del último pago" ><i class="fas fa-exclamation-triangle"></i>'+fechaUltimaCuota+'</td>';
		else if ( !deudor )
			row += ">"+fechaUltimaCuota+'</td>';
	}
	else
		row+= ' title="No se encontró fecha del último pago"><i class="fas fa-exclamation-triangle"></i>'+ fechaUltimaCuota + "</td>";

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