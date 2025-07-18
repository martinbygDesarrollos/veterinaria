let lastId = 0;

function cargarTablaHistorialSocios(idSocio){
	let response = sendPost('getListHistorialSocio', {lastId: lastId, idSocio: idSocio});
	if(response.result == 2){
		if(response.lastId != lastId)
			lastId = response.lastId;
		let list = response.listResult;
		for (var i = 0; i < list.length; i++) {
			let row = createRowHistorial(list[i]);
			$('#tbodyHistorialSocio').append(row);
		}
	}
}

function createRowHistorial(obj){

	let idHistorialSocio = obj.idHistorialSocio ;
	let idSocio = obj.idSocio ;
	let idMascota = obj.idMascota ;
	let mascota = obj.mascota ;
	let fechaEmision = obj.fechaEmision;
	let asunto = obj.asunto ;
	let importe = obj.importe ;
	let fecha = obj.fecha ; //formato DD/MM/YYYY
	let observaciones = obj.observaciones || "" ;
	let comprobante = obj.comprobante || "" ;
	let mes = obj.mes || "" ; //formato MM/YYYY
	let recibo = obj.recibo || "" ;


	let row = "<tr id='"+ idHistorialSocio +"'>";
	row += "<td class='col-1' >"+ mes +"</td>";
	row += "<td class='col-1' >"+ fecha +"</td>";
	if(mascota)
		row += "<td class='col-1' onclick='verMascota("+ idMascota + ")'>"+ mascota +"</td>";
	else
		row += "<td class='col-1'></td>";
	row += "<td class='col-3' >"+ asunto +"</td>";
	row += "<td class='col-2' >"+ observaciones +"</td>";
	row += "<td class='col-2' >"+ comprobante +"</td>";
	row += "<td class='col-1' >"+ importe +"</td>";
	row += "<td class='col-1' >"+ recibo +"</td></tr>";

	return row;
}

function openModificarHistorialSocio(idHistorialSocio){
	if(idHistorialSocio){
		$('#titleModalHistorialSocio').html("Modificar historia");
		$('#buttonModalHistorialSocio').off('click');
		$('#buttonModalHistorialSocio').click(function(){

		});
		$('#modalHistorialSocio').modal();
	}
}

function openNuevoHistorialSocio(idSocio){
	clearModalHistorial();
	$('#titleModalHistorialSocio').html("Agregar historia");
	$('#buttonModalHistorialSocio').off('click');
	$('#buttonModalHistorialSocio').click(function(){
		createHistorialSocio(idSocio);
	});
	$('#modalHistorialSocio').modal();
}

function clearModalHistorial(){
	$('#inputFechaHistorial').val(getDateForInput());
	$('#selectMascotaHistorial').val(0);
	$('#inputAsuntoHistorial').val("");
	$('#inputImporteHistorial').val("");
	$('#textAreaObservacionesHistorial').val("");
}

function createHistorialSocio(idSocio){
	let fecha = $('#inputFechaHistorial').val() || null;
	let mascota = $('#selectMascotaHistorial').val();
	let asunto = $('#inputAsuntoHistorial').val() || null;
	let importe = $('#inputImporteHistorial').val() || null;
	let observaciones = $('#textAreaObservacionesHistorial').val() || null;

	if(fecha){
		if(asunto){
			if(mascota == 0)
				mascota = null;

			let data = {
				idSocio:idSocio,
				idMascota: mascota,
				fecha: fecha,
				asunto: asunto,
				importe: importe,
				observaciones: observaciones
			};
			let response = sendPost('crearHistorialSocio', data);
			showReplyMessage(response.result, response.message, "Cliente", "modalHistorialSocio");
			if(response.result == 2){
				let newRow = response.newHistorial;
				//let row = createRowHistorial(newRow.idHistorialSocio, newRow.idSocio, newRow.idMascota, newRow.mascota, newRow.fechaEmision, newRow.asunto, newRow.importe, newRow.fecha, newRow.observaciones);
				let row = createRowHistorial(newRow);
				$('#tbodyHistorialSocio').prepend(row);
				clearModalHistorial();
			}
		}else showReplyMessage(1, "Debe ingresar un asunto para crear un registro en el historial", "Cliente", "modalHistorialSocio");
	}else showReplyMessage(1, "Debe ingresar una fecha para crear un registro en el historial", "Cliente", "modalHistorialSocio");

}

function openModalUpdateSocio(btnShowModal){
	let idSocio = btnShowModal.id;
	let response = sendPost('getSocio', {idSocio: idSocio});
	if(response.result == 2){
		setValues("Modal", response.socio);
		$('#modalUpdateSocio').modal();
	}else showReplyMessage(response.result, response.message, "Cliente", null);
}

function calculateQuotaSocio(idSocio){
	let response = sendPost("actualizarCuotaSocio", {idSocio: idSocio});
	showReplyMessage(response.result, response.message, "Cliente", null);
	if(response.result != 0)
		$('#inputCuota').val(response.newQuota);
}


function buscarMascotasSinSocio(inputToSearch, idSocio){
	let textToSearch = inputToSearch.value || null;
	$('#tbodyMascotasNoSocio').empty();
	if(textToSearch){
		let response = sendPost("getMascotasNoSocio", {textToSearch: textToSearch});
		if(response.result == 2){
			let list = response.listResult;
			for (var i = 0; i <list.length; i++) {
				let row = createRowMascotasNoSocio(idSocio, list[i].idMascota, list[i].nombre, list[i].especie, list[i].raza, list[i].sexo, list[i].fechaNacimiento, list[i].estado);
				$('#tbodyMascotasNoSocio').append(row);
			}
		}
	}
}

function createRowMascotasNoSocio(idSocio, idMascota, nombre, especie, raza, sexo, fechaNacimiento, estado){
	let row = "<tr id='trM"+ idMascota +"'>";
	row += "<td class='text-center'>"+ nombre +"</td>";
	row += "<td class='text-center'>"+ especie +"</td>";
	row += "<td class='text-center'>"+ raza +"</td>";
	row += "<td class='text-center'>"+ sexo +"</td>";
	row += "<td class='text-center'>"+ fechaNacimiento +"</td>";
	if(estado == 1)
		row += "<td class='text-center'>Si</td>";
	else
		row += "<td class='text-center'>No</td>";
	row += "<td class='text-center'><button id='"+ idSocio +"' name='"+ idMascota +"' class='btn btn-dark btn-sm' onclick='asignarMascota(this)'><i class='fas fa-plus'></i></button></td>";
	row += "</tr>";

	return row;
}

function asignarMascota(buttonAsignar){
	let idSocio = buttonAsignar.id;
	let idMascota = buttonAsignar.name;

	sendAsyncPost("asignarMascotaSocio", {idSocio: idSocio, idMascota: idMascota})

	.then((response)=>{

		showReplyMessage(response.result, response.message, "Cliente", "modalSetNewMascota");
		if(response.result != 0){
			$('#trM' +  idMascota).remove();
			let newMascota = response.newMascota;
			let row = createRowMascotasSocio(newMascota.idMascota, newMascota.nombre, newMascota.raza, newMascota.especie, newMascota.sexo, newMascota.fechaNacimiento, newMascota.fechaFallecimiento);
			$('#tbodyMascotasSocio').prepend(row);
			$('#tbodyMascotasNoSocio').empty();
			$('#inputTextToSearch').val("");
			$('#inputCuota').val(response.newQuota);
		}

	})

}

function createRowMascotasSocio(idMascota, nombre, raza, especie, sexo, nacimiento, fallecimiento){

	let row = "";

	if ( fallecimiento ){
		row = "<tr id='trM2"+ idMascota +"' class='subtexto' style='color:red; font-weight: bold;'>";
		row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>FALLECIDO " + nombre + "</td>";

	}
	else{

		row = "<tr id='trM2"+ idMascota +"'>";
		row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>" + nombre + "</td>";

	}


	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>" + raza + "</td>";
	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>" + especie + "</td>";
	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>" + sexo + "</td>";
	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>"+ nacimiento + "</td>";

	row += "<td class='text-center'><button class='btn btn-link' onclick='desvincularMascota("+ idMascota + ")'><i class='fas fa-trash-alt text-dark'></i></button></td>";

	row += "</tr>";

	return row;
}

function verMascota(idMascota){
	window.location.href = getSiteURL() + "ver-mascota/" + idMascota;
}

function desvincularMascota(idMascota){
	let response = sendPost("desvincularMascota", {idMascota: idMascota});
	showReplyMessage(response.result, response.message, "Cliente", null);
	if(response.result != 0){
		$('#inputCuota').val(response.newQuota);
		$('#trM2' + idMascota).remove();
	}
}

function saveChangeSocio(buttonConfirm){
	let idSocio = buttonConfirm.name;
	let cedula = $('#inputModalCedula').val() || null;
	let nombre = $('#inputModalNombre').val() || null;
	let direccion = $('#inputModalDireccion').val() || null;
	let telefono = $('#inputModalTelefono').val() || null;
	let fechaPago = $('#inputModalFechaPago').val() || null;
	let ultimoPago = $('#inputModalUltimoPago').val() || null;
	let ultimoMesPago = $('#inputModalUltimoMesPago').val() || null;
	let fechaIngreso = $('#inputModalFechaIngreso').val() || null;
	let fechaBajaSocio = $('#inputModalFechaBaja').val() || null;
	let lugarPago = $('#selectLugarPago').val() || null;
	let tipoSocio = $('#selectModalTipoSocio').val() || null;
	let rut = $('#inputModalRut').val() || null;
	let telefax = $('#inputModalTelefax').val() || null;
	let email = $('#inputModalEmail').val() || null;
	let buenPagador = $('#inputBuenPagadorEdit').prop("checked") ? 1 : 0;

	if (cedula) cedula.replaceAll('|', '');
	if (nombre) nombre.replaceAll('|', '');
	if (direccion) direccion.replaceAll('|', '');
	if (telefono) telefono.replaceAll('|', '');
	if (fechaPago) fechaPago.replaceAll('|', '');
	if (ultimoPago) ultimoPago.replaceAll('|', '');
	if (ultimoMesPago) ultimoMesPago.replaceAll('|', '');
	if (fechaIngreso) fechaIngreso.replaceAll('|', '');
	if (fechaBajaSocio) fechaBajaSocio.replaceAll('|', '');
	if (lugarPago) lugarPago.replaceAll('|', '');
	if (tipoSocio) tipoSocio.replaceAll('|', '');
	if (rut) rut.replaceAll('|', '');
	if (telefax) telefax.replaceAll('|', '');
	if (email) email.replaceAll('|', '');



	//if( cedula || rut ){
		if(validateCI(cedula) || !cedula){
			if(nombre){
				if(!email || validateEmail(email)){
					let data = {
						idSocio: idSocio,
						cedula: cedula,
						nombre: nombre,
						direccion: direccion,
						telefono: telefono,
						fechaPago: fechaPago,
						ultimoPago: ultimoPago,
						lugarPago: lugarPago,
						ultimoMesPago: ultimoMesPago,
						fechaIngreso: fechaIngreso,
						fechaBajaSocio: fechaBajaSocio,
						rut: rut,
						telefax: telefax,
						tipo: tipoSocio,
						email: email,
						buenPagador: buenPagador
					};
					let response = sendPost("updateSocio", data);
					if(response.result == 2)
						window.location.reload();
					else
						showReplyMessage(response.result, response.message, "Cliente", "modalUpdateSocio");
				}else showReplyMessage(1, "En caso de ingresar un email este debe ser valido", "Cliente", "modalUpdateSocio");
			}else showReplyMessage(1, "Debe ingresar el nombre del cliente para modificarlo", "Cliente", "modalUpdateSocio");
		}else showReplyMessage(1, "La cédula ingresada no es valida", "Cliente", "modalUpdateSocio");
	//}else showReplyMessage(1, "Debe ingresar cédula o rut para poder identificar el cliente", "Cliente", "modalUpdateSocio");
}

function setValues(inputFrom, socio){
	//console.log(socio)
	$('#input'+ inputFrom +'Cedula').val(socio.cedula);
	$('#input'+ inputFrom +'Nombre').val(socio.nombre);
	$('#input'+ inputFrom +'Direccion').val(socio.direccion);
	$('#input'+ inputFrom +'Telefono').val(socio.telefono);
	$('#input'+ inputFrom +'Email').val(socio.email);
	$('#input'+ inputFrom +'FechaPago').val(socio.fechaPago);
	$('#input'+ inputFrom +'UltimoPago').val(socio.fechaUltimoPago);
	$('#input'+ inputFrom +'UltimoMesPago').val(socio.fechaUltimaCuota)
	$('#input'+ inputFrom +'FechaIngreso').val(socio.fechaIngreso)
	$('#select'+ inputFrom +'TipoSocio').val(socio.tipo);
	$('#input' + inputFrom + 'Cuota').val(socio.cuota);
	$('#input'+ inputFrom +'FechaBaja').val(socio.fechaBajaSocio)
	if(inputFrom == "Modal"){
		$('#selectLugarPago').val(socio.lugarPago)
	}

	$('#input'+ inputFrom +'Rut').val(socio.rut);
	$('#input'+ inputFrom +'Telefax').val(socio.telefax);
}

function keyEnterPress(eventEnter, value, size){
	if(eventEnter.keyCode == 13){
		if(eventEnter.srcElement.id == "inputModalCedula")
			$('#inputModalNombre').focus();
		else if(eventEnter.srcElement.id == "inputModalNombre")
			$('#inputModalTelefono').focus();
		else if(eventEnter.srcElement.id == "inputModalTelefono")
			$('#inputModalDireccion').focus();
		else if(eventEnter.srcElement.id == "inputModalDireccion")
			$('#inputModalEmail').focus();
		else if(eventEnter.srcElement.id == "inputModalEmail")
			$('#inputModalFechaPago').focus();
		else if(eventEnter.srcElement.id == "inputModalFechaPago")
			$('#inputModalUltimoPago').focus();
		else if(eventEnter.srcElement.id == "inputModalUltimoPago")
			$('#inputModalUltimoMesPago').focus();
		else if(eventEnter.srcElement.id == "inputModalUltimoMesPago")
			$('#inputModalFechaIngreso').focus();
		else if(eventEnter.srcElement.id == "inputModalFechaIngreso")
			$('#inputModalRut').focus();
		else if(eventEnter.srcElement.id == "inputModalRut")
			$('#inputModalTelefax').focus();
		else if(eventEnter.srcElement.id == "inputModalTelefax")
			$('#btnConfirmChange').click();
	}else if(value != null && value.length == size) {
		return false;
	}
}

function openModalChangeState(idSocio){
	let responseGetSocio = sendPost("getSocio", {idSocio: idSocio});
	if(responseGetSocio.result == 2){
		if(responseGetSocio.socio.estado == 1){
			$('#tittleModalState').html("Desactivar cliente");
			$('#messageModalState').html("¿Desea desactivar el cliente seleccionado?<br>Esta operación desactivara todas las mascotas del cliente y dejara su cuota en 0");
		}else{
			$('#tittleModalState').html("Activar cliente");
			$('#messageModalState').html("¿Desea activar el cliente seleccionado?");
		}
		$('#buttonModalState').off('click');
		$('#buttonModalState').click(function(){
			activarDesactivarSocio(idSocio, responseGetSocio.socio.estado)
		});
		$('#modalChangeState').modal();
	}
}

function activarDesactivarSocio(idSocio, estado){
	let nuevoEstado = "Activar cliente";
	if(estado == 1)
		nuevoEstado = "Desactivar cliente";

	let response = sendPost("activarDesactivarSocio", {idSocio: idSocio});
	showReplyMessage(response.result, response.message, nuevoEstado, "modalChangeState");
	if(response.result != 0){
		if(response.newState == 0){
			$('#iconButtonState').removeClass("fa-user-check").addClass("fa-user-times");
			$('#inputCuota').val('0.00');
		}else $('#iconButtonState').removeClass("fa-user-times").addClass("fa-user-check");
	}
}
