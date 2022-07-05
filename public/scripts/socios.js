let lastId = 0;
let textToSearch = null;
let estado = 1;

$(document).ready(()=>{

	if (estado) {
		$("#inputActiveSocio").prop('checked', true);
		$("#inputInactiveSocio").prop('checked', false);
	}else{
		$("#inputActiveSocio").prop('checked', false);
		$("#inputInactiveSocio").prop('checked', true);
	}
})

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
	//console.log(obj);
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
	let mascotas = obj.mascotas
	let tipo = obj.tipo
	console.log(idSocio, tipo, deudor);

	if ( !telefono ){
		telefono = "";
	}

	if ( !direccion ){
		direccion = "";
	}

	let titleMascotas = "";
	if ( mascotas.length > 0 ){
		for (var i = 0; i < mascotas.length; i++) {
			titleMascotas += mascotas[i].nombre + ", "
		}
	}


	let selectMascotas = '<select class="form-select form-control shadow-sm">';
	if ( mascotas.length > 0 ){
		for (var i = 0; i < mascotas.length; i++) {
			mascotaestado = "Muerto"
			if ( !mascotas[i].fechaFallecimiento )
				mascotaestado = "Vivo"

			selectMascotas += '<option >' + mascotas[i].nombre + ' - '+ mascotaestado +'</option>'
		}

		selectMascotas += '</select>'
	}else selectMascotas = "";

	//console.log("CALCULAR EL COLOR DE LA LINEA SEGUN EL TIPO DE SOCIO Y LA DEUDA ");
	classForClient = "";

	if ( tipo == 0 ){ //NO SOCIO
		if ( deudor )
			classForClient = "rowNosocio";
		else
			classForClient = "rowNosocio";
	}else if ( tipo == 1 ){ //SOCIO
		if ( deudor )
			classForClient = "rowWarning";
	}else if ( tipo == 3 ){ //EX SOCIO
		if ( deudor )
			classForClient = "rowExsocioWarning";
		else
			classForClient = "rowExsocio";
	}

	let row = "<tr class='"+classForClient+"' >";
	//.rowWarning.rowExsocio.rowExsocioWarning.rowNosocio

	row += "<td class='text-center notShowMobile' onclick='redirectToSocio("+ idSocio +")'>"+ idSocio +"</td>";
	row += "<td class='text-center' onclick='redirectToSocio("+ idSocio +")'>"+ nombre +"</td>";

	if ( selectMascotas.length == 0 ){
		row += "<td class='text-center' onclick='redirectToSocio("+ idSocio +")'>" + selectMascotas + "</td>";
	}else
		row += "<td class='text-center'>" + selectMascotas + "</td>";


	row += "<td class='text-center' onclick='redirectToSocio("+ idSocio +")'>"+ telefono +"</td>";
	row += "<td class='text-center notShowMobile' onclick='redirectToSocio("+ idSocio +")'>"+ direccion +"</td>";
	row += "<td class='text-center notShowMobile' onclick='redirectToSocio("+ idSocio +")'>"+ cuota + "</td>";
	row += "<td class='text-center notShowMobile' onclick='redirectToSocio("+ idSocio +")' "

	if ( fechaUltimaCuota != "" ){
		if ( deudor && deudorFecha != "" )
			row += ' title="Último mes pago '+ fechaUltimaCuota +' el día '+ deudorFecha +'" ><i class="fas fa-exclamation-triangle"></i> '+fechaUltimaCuota+'</td>';
		else if (deudor && deudorFecha == "" )
			row += ' title="No se encontró fecha del último pago" ><i class="fas fa-exclamation-triangle"></i>'+fechaUltimaCuota+'</td>';
		else if ( !deudor )
			row += ">"+fechaUltimaCuota+'</td>';
	}
	else
		row+= fechaUltimaCuota + "</td>";

	row += "<td class='text-center'><a class='text-dark' data-toggle='tooltip' data-placement='top' title='Agregar nueva mascota' href='" + getSiteURL() + "nueva-mascota/" + idSocio +"'><button class='btn btn-light' type='button'>Nueva masc.</button></a></td>";
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