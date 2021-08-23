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
	console.log(response)
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
							rut: rut,
							telefax: telefax,
							tipo: tipoSocio,
							email: email
						};
						let response = sendPost("updateSocio", data);
						showReplyMessage(response.result, response.message, "Modificar socio", "modalUpdateSocio");
						if(response.result == 2)
							setValues("", response.newSocio);
					}else showReplyMessage(1, "En caso de ingresar un email este debe ser valido.", "Email incorrecto", "modalUpdateSocio");
				}else showReplyMessage(1, "El nombre del socio debe tener al menos 6 caracteres para ser considerado valido.", "Nombre incorrecto", "modalUpdateSocio");
			}else showReplyMessage(1, "Debe ingresar el nombre del socio para modificarlo", "Nombre requerido", "modalUpdateSocio");
		}else showReplyMessage(1, "La cédula ingresada no es valida", "Cédula incorrecta", "modalUpdateSocio");
	}else showReplyMessage(1, "Debe ingresar la cédula del socio para poder modificarlo.", "Cédula requerida", "modalUpdateSocio");
}

function setValues(inputFrom, socio){
	console.log(socio)
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
	if(inputFrom == "Modal")
		$('#selectLugarPago').val(socio.lugarPago)

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
