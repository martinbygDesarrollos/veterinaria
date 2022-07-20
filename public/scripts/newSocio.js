function insertarNuevoSocio(){
	let nombre = $('#inputNombreSocio').val() || null;
	let cedula = $('#inputCedulaSocio').val() || null;
	let telefono = $('#inputTelefonoSocio').val() || null;
	let direccion = $('#inputDireccionSocio').val() || null;
	let email = $('#inputEmailSocio').val() || null;
	let rut = $('#inputRutSocio').val() || null;
	let telefax = $('#inputTelefaxSocio').val() || null;
	let fechaPago = $('#inputFechaPagoSocio').val() || null;
	let lugarPago = $('#inputLugarPagoSocio').val() || null;
	let tipoSocio = $('#inputTipoSocio').val() || null;
	let fechaIngreso = $('#inputFechaIngresoSocio').val() || null;

	if(cedula){
		if(validateCI(cedula)){
			if(nombre){
					if(!email || validateEmail(email)){
						let data = {
							cedula: cedula,
							nombre: nombre,
							direccion: direccion,
							telefono: telefono,
							fechaPago: fechaPago,
							lugarPago: lugarPago,
							fechaIngreso: fechaIngreso,
							rut: rut,
							telefax: telefax,
							tipo: tipoSocio,
							email: email
						};

						let response = sendPost("insertNewSocio", data);
						if(response.result == 2){
							$('#modalResultNewSocio').modal();
							$('#modalResultNewSocio').modal();
							$('#modalButtonNewSocio').click(function(){
								window.location.href = getSiteURL() + "nueva-mascota/" + response.newIdSocio;
							});
						}else showReplyMessage(response.result, response.message, "Agregar socio", null);
					}else showReplyMessage(1, "En caso de ingresar un email este debe ser valido.", "Email incorrecto", "modalUpdateSocio");
			}else showReplyMessage(1, "Debe ingresar el nombre del socio para agregarlo", "Nombre requerido", "modalUpdateSocio");
		}else showReplyMessage(1, "La cédula ingresada no es valida", "Cédula incorrecta", "modalUpdateSocio");
	}else showReplyMessage(1, "Debe ingresar la cédula del socio para poder agregarlo.", "Cédula requerida", "modalUpdateSocio");
}


function keyEnterPress(eventEnter, value, size){
	if(eventEnter.keyCode == 13){
		if(eventEnter.srcElement.id == "inputCedulaSocio")
			$('#inputNombreSocio').focus();
		else if(eventEnter.srcElement.id == "inputNombreSocio")
			$('#inputTelefonoSocio').focus();
		else if(eventEnter.srcElement.id == "inputTelefonoSocio")
			$('#inputDireccionSocio').focus();
		else if(eventEnter.srcElement.id == "inputDireccionSocio")
			$('#inputEmailSocio').focus();
		else if(eventEnter.srcElement.id == "inputEmailSocio")
			$('#inputFechaPagoSocio').focus();
		else if(eventEnter.srcElement.id == "inputFechaPagoSocio")
			$('#inputUltimoPagoSocio').focus();
		else if(eventEnter.srcElement.id == "inputUltimoPagoSocio")
			$('#inputUltimoMesPagoSocio').focus();
		else if(eventEnter.srcElement.id == "inputUltimoMesPagoSocio")
			$('#inputFechaIngresoSocio').focus();
		else if(eventEnter.srcElement.id == "inputFechaIngreso")
			$('#inputRutSocio').focus();
		else if(eventEnter.srcElement.id == "inputRutSocio")
			$('#inputTelefaxSocio').focus();
		else if(eventEnter.srcElement.id == "inputTelefaxSocio")
			$('#btnConfirm').click();
	}else if(value != null && value.length == size) {
		return false;
	}
}