let lastId = 0;

function cargarTablaHistorialSocios(idSocio){
	let response = sendPost('getListHistorialSocio', {lastId: lastId, idSocio: idSocio});
	if(response.result == 2){
		if(response.lastId != lastId)
			lastId = response.lastId;
		let list = response.listResult;
		for (var i = 0; i < list.length; i++) {
			let row = createRowHistorial(list[i].idHistorialSocio, list[i].idSocio, list[i].idMascota, list[i].mascota, list[i].fechaEmision, list[i].asunto, list[i].importe, list[i].fecha, list[i].observaciones);
			$('#tbodyHistorialSocio').append(row);
		}
	}
}

function createRowHistorial(idHistorialSocio, idSocio, idMascota, mascota, fechaEmision, asunto, importe, fecha, observaciones){
	let row = "<tr id='"+ idHistorialSocio +"'>";
	row += "<td class='text-center' >"+ fechaEmision +"</td>";
	row += "<td class='text-center' >"+ asunto +"</td>";
	if(mascota)
		row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>"+ mascota +"</td>";
	else
		row += "<td class='text-center'>No especificado</td>";
	row += "<td class='text-center' >"+ importe +"</td>";
	row += "<td class='text-center' >"+ fecha +"</td>";
	row += "<td class='text-center' >"+ observaciones +"</td></tr>";

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
			showReplyMessage(response.result, response.message, "Agregar historia", "modalHistorialSocio");
			if(response.result == 2){
				let newRow = response.newHistorial;
				let row = createRowHistorial(newRow.idHistorialSocio, newRow.idSocio, newRow.idMascota, newRow.mascota, newRow.fechaEmision, newRow.asunto, newRow.importe, newRow.fecha, newRow.observaciones);
				$('#tbodyHistorialSocio').prepend(row);
				clearModalHistorial();
			}
		}else showReplyMessage(1, "Debe ingresar un asunto para crear un registro en el historial", "Asunto requerido", "modalHistorialSocio");
	}else showReplyMessage(1, "Debe ingresar una fecha para crear un registro en el historial", "Fecha requeridda", "modalHistorialSocio");

}

function openModalUpdateSocio(btnShowModal){
	let idSocio = btnShowModal.id;
	let response = sendPost('getSocio', {idSocio: idSocio});
	if(response.result == 2){
		setValues("Modal", response.socio);
		$('#modalUpdateSocio').modal();
	}else showReplyMessage(response.result, response.message, "Obtener socio", null);
}

function calculateQuotaSocio(idSocio){
	let response = sendPost("actualizarCuotaSocio", {idSocio: idSocio});
	showReplyMessage(response.result, response.message, "Actualizar cuota", null);
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
		row += "<td class='text-center'>Activa</td>";
	else
		row += "<td class='text-center'>Inactiva</td>";
	row += "<td class='text-center'><button id='"+ idSocio +"' name='"+ idMascota +"' class='btn btn-dark btn-sm' onclick='asignarMascota(this)'><i class='fas fa-plus'></i></button></td>";
	row += "</tr>";

	return row;
}

function asignarMascota(buttonAsignar){
	let idSocio = buttonAsignar.id;
	let idMascota = buttonAsignar.name;

	let response = sendPost("asignarMascotaSocio", {idSocio: idSocio, idMascota: idMascota});
	showReplyMessage(response.result, response.message, "Asignar mascota", "modalSetNewMascota");
	if(response.result != 0){
		$('#trM' +  idMascota).remove();
		let newMascota = response.newMascota;
		let row = createRowMascotasSocio(newMascota.idMascota, newMascota.nombre, newMascota.raza, newMascota.especie, newMascota.sexo, newMascota.fechaNacimiento, newMascota.estado);
		$('#tbodyMascotasSocio').prepend(row);
		$('#tbodyMascotasNoSocio').empty();
		$('#inputTextToSearch').val("");
		$('#inputCuota').val(response.newQuota);
	}
}

function createRowMascotasSocio(idMascota, nombre, raza, especie, sexo, nacimiento, estado){
	let row = "<tr id='trM2"+ idMascota +"'>";
	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>" + nombre + "</td>";
	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>" + raza + "</td>";
	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>" + especie + "</td>";
	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>" + sexo + "</td>";
	row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>"+ nacimiento + "</td>";
	if(estado == 1)
		row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>Activa</td>";
	else
		row += "<td class='text-center' onclick='verMascota("+ idMascota + ")'>Inactiva</td>";
	row += "<td class='text-center'><button class='btn btn-link' onclick='desvincularMascota("+ idMascota + ")'><i class='fas fa-trash-alt text-dark'></i></button></td>";

	row += "</tr>";

	return row;
}

function verMascota(idMascota){
	window.location.href = getSiteURL() + "ver-mascota/" + idMascota;
}

function desvincularMascota(idMascota){
	let response = sendPost("desvincularMascota", {idMascota: idMascota});
	showReplyMessage(response.result, response.message, "Desvincular mascota", null);
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

	if(cedula){
		if(validateCI(cedula)){
			if(nombre){
				if(nombre.length > 5){
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
							email: email
						};
						console.log("fecha baja antes de la ruta ",fechaBajaSocio);
						let response = sendPost("updateSocio", data);
						//showReplyMessage(response.result, response.message, "Modificar socio", "modalUpdateSocio");
						if(response.result == 2)
							window.location.reload();
						else
							showReplyMessage(response.result, response.message, "Modificar socio", "modalUpdateSocio");
						//setValues("", response.newSocio);
					}else showReplyMessage(1, "En caso de ingresar un email este debe ser valido.", "Email incorrecto", "modalUpdateSocio");
				}else showReplyMessage(1, "El nombre del socio debe tener al menos 6 caracteres para ser considerado valido.", "Nombre incorrecto", "modalUpdateSocio");
			}else showReplyMessage(1, "Debe ingresar el nombre del socio para modificarlo", "Nombre requerido", "modalUpdateSocio");
		}else showReplyMessage(1, "La cédula ingresada no es valida", "Cédula incorrecta", "modalUpdateSocio");
	}else showReplyMessage(1, "Debe ingresar la cédula del socio para poder modificarlo.", "Cédula requerida", "modalUpdateSocio");
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
			$('#tittleModalState').html("Desactivar socio");
			$('#messageModalState').html("¿Desea desactivar el socio seleccionado?<br>Esta operación desactivara todas las mascotas del socio y dejara su cuota en 0");
		}else{
			$('#tittleModalState').html("Activar socio");
			$('#messageModalState').html("¿Desea activar el socio seleccionado?");
		}
		$('#buttonModalState').off('click');
		$('#buttonModalState').click(function(){
			activarDesactivarSocio(idSocio, responseGetSocio.socio.estado)
		});
		$('#modalChangeState').modal();
	}
}

function activarDesactivarSocio(idSocio, estado){
	let nuevoEstado = "Activar socio";
	if(estado == 1)
		nuevoEstado = "Desactivar socio";

	let response = sendPost("activarDesactivarSocio", {idSocio: idSocio});
	showReplyMessage(response.result, response.message, nuevoEstado, "modalChangeState");
	if(response.result != 0){
		if(response.newState == 0){
			$('#iconButtonState').removeClass("fa-user-check").addClass("fa-user-times");
			$('#inputCuota').val('0.00');
		}else $('#iconButtonState').removeClass("fa-user-times").addClass("fa-user-check");
	}
}
