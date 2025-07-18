let lastId = 0;
let textToSearch = null;
let estado = 1;
let tipocliente = null;
let deudor = null;

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

	let response = sendPost("getSociosPagina", {lastId: lastId, textToSearch: textToSearch, estado: estado, tipo:tipocliente, deudor:deudor});
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
	let telefax = obj.telefax
	let cantMascotas = obj.cantMascotas
	let cuota = obj.cuota
	let fechaUltimoPago = obj.fechaUltimoPago
	let fechaUltimaCuota = obj.fechaUltimaCuota
	let direccion = obj.direccion
	let deudor = obj.deudor
	let deudorFecha = obj.deudorFecha
	let mascotas = obj.mascotas
	let tipo = obj.tipo
	let lugarPago = obj.lugarPago;
	let buenPagador = obj.buenPagador;
	//console.log(idSocio, tipo, deudor);

	if ( !telefono ){
		telefono = "";
	}

	if ( !telefax ){
		telefax = "";
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

	metodopago = "";
	if ( lugarPago == 0 ){
		metodopago = "Veterinaria";
	}else if ( lugarPago == 1 ){
		metodopago = "Cobrador";
	}else if ( lugarPago == 2 ){
		metodopago = "OCA";
	}


	let selectMascotas = '<select class="form-select form-control shadow-sm">';
	if ( mascotas.length > 0 ){
		for (var i = 0; i < mascotas.length; i++) {
			classDeadPet = "disabled"
			if ( !mascotas[i].fechaFallecimiento ){
				classDeadPet = "";
			}

			selectMascotas += '<option class="" '+classDeadPet+'>' + mascotas[i].nombre +'</option>'
		}

		selectMascotas += '</select>'
	}else selectMascotas = "";

	//console.log("CALCULAR EL COLOR DE LA LINEA SEGUN EL TIPO DE SOCIO Y LA DEUDA "); y si es buen pagador o no
	classForClient = "";
	tipoCliente = "";

	if ( tipo == 0 ){ //NO SOCIO
		tipoCliente = "<br>(Cliente)";
		classForClient = "rowNosocio";
	}else if ( tipo == 1 ){ //SOCIO
		tipoCliente = "<br>(Socio)";
		classForClient = "rowSocio"
		if ( deudor )
			classForClient = "rowWarning";
	}else if ( tipo == 3 ){ //EX SOCIO
		tipoCliente = "<br>(Ex socio)";
		if ( deudor )
			classForClient = "rowExsocioWarning";
		else
			classForClient = "rowExsocio";
	}else if ( tipo == 2 ) //ONG
		tipoCliente = "<br>(ONG)";

	if (buenPagador === 1)
		classForClient += "BuenPagador"
	
	let row = "<tr class='"+classForClient+"' >";
	//.rowWarning.rowExsocio.rowExsocioWarning.rowNosocio

	row += "<td class='text-center notShowMobile' onclick='redirectToSocio("+ idSocio +")'>"+ idSocio +tipoCliente+"</td>";
	row += "<td class='text-center' onclick='redirectToSocio("+ idSocio +")'>"+ nombre +"</td>";

	if ( selectMascotas.length == 0 ){
		row += "<td class='text-center' onclick='redirectToSocio("+ idSocio +")'>" + selectMascotas + "</td>";
	}else
		row += "<td class='text-center'>" + selectMascotas + "</td>";


	row += "<td class='text-center' onclick='redirectToSocio("+ idSocio +")'>"+ telefono +" "+ telefax +"</td>";
	//row += "<td class='text-center notShowMobile' onclick='redirectToSocio("+ idSocio +")'>"+ direccion +"</td>";
	//row += "<td class='text-center notShowMobile' onclick='redirectToSocio("+ idSocio +")'>"+ cuota + "</td>";
	row += "<td class='text-center notShowMobile' onclick='redirectToSocio("+ idSocio +")' "

	if ( fechaUltimaCuota != "" ){
		if ( deudor && deudorFecha != "" )
			row += ' title="Pago pendiente '+ fechaUltimaCuota +' último movimiento el día '+ deudorFecha +'" ><i class="fas fa-exclamation-triangle"></i> '+fechaUltimaCuota+'</td>';
		else if (deudor && deudorFecha == "" )
			row += ' title="No se encontró fecha del último pago" ><i class="fas fa-exclamation-triangle"></i>'+fechaUltimaCuota+'</td>';
		else if ( !deudor )
			row += ">"+fechaUltimaCuota+'</td>';
	}
	else
		row+= fechaUltimaCuota + "</td>";

	row += "<td class='text-center' onclick='redirectToSocio("+ idSocio +")'>"+ metodopago +"</td>";

	//row += "<td class='text-center'><a class='text-dark' data-toggle='tooltip' data-placement='top' title='Agregar nueva mascota' href='" + getSiteURL() + "nueva-mascota/" + idSocio +"'><button class='btn btn-light' type='button'>Nueva masc.</button></a></td>";
	btnwpp = '<td class="text-center"><button title="No se encontró número de whatsapp" disabled class="btn bg-light"><i class="fab fa-whatsapp"></i></button></td>';

	if (telefax.length >= 7)
		btnwpp = '<td class="text-center"><a href="https://wa.me/'+telefax+'" target="_blank"><button title="Enviar mensaje a '+telefax+'" class="btn bg-light"><i class="fab fa-whatsapp"></i></button></a></td>';
	row += btnwpp;
	row += "</tr>";

	return row;
}

function redirectToSocio(idSocio){
	window.location.href = getSiteURL() + "ver-socio/"+ idSocio;
}

function buscarSocio(){
	let textTemp = $('#textToSearch').val() || null;

	if(textTemp){
		if(textTemp.length > 0){
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



function changeTipoCliente(value){

	//recordar en las cookies

	if(value){
		console.log("cliente seleccionado", value)
		tipocliente = value;
		lastId = 0;
		$('#tbodySocios').empty();
		cargarTablaSocios();
	}

}



function changeTipoDeuda(value){

	//recordar en las cookies

	if(value){
		console.log("filtro deuda", value)

		deudor = value;
		lastId = 0;
		$('#tbodySocios').empty();
		cargarTablaSocios();
	}


}