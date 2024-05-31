var crearSocio = false;
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


	if(nombre) nombre = nombre.replaceAll('|', '');
	if(cedula) cedula = cedula.replaceAll('|', '');
	if(telefono) telefono = telefono.replaceAll('|', '');
	if(telefax) telefax = telefax.replaceAll('|', '');
	if(direccion) direccion = direccion.replaceAll('|', '');
	if(email) email = email.replaceAll('|', '');
	if(rut) rut = rut.replaceAll('|', '');

	if ( $("#inputTipoSocio").val() == 1 && !crearSocio){
		showMessageConfirm(1, "Está seguro de crear un SOCIO?", "Nuevo cliente", null)
		$('#modalMessageConfirmBtnSi').click(function(){
			$('#modalMessageConfirm').modal('hide');
			crearSocio = true
			insertarNuevoSocio()
		});
	}else{
		crearSocio = true
	}

	if( crearSocio === true ){
		crearSocio = false

		if(validateCI(cedula) || !cedula){
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

					console.log(data);
					sendAsyncPost("insertNewSocio", data)
					.then((response)=>{
						console.log(response);
						if(response.result == 2){
							$('#modalResultNewSocio').modal();
							$('#modalResultNewSocio').modal();
							$('#modalButtonNewSocio').click(function(){
								window.location.href = getSiteURL() + "nueva-mascota/" + response.newIdSocio;
							});

							$("#modalButtonCancelNewSocio").click(function(){
								window.location.href = getSiteURL() + "ver-socio/" + response.newIdSocio;
							});
						}else showReplyMessage(response.result, response.message, "Cliente", null);
					})
				}else showReplyMessage(1, "En caso de ingresar un email este debe ser valido", "Cliente", "modalUpdateSocio");
			}else showReplyMessage(1, "Debe ingresar el nombre del cliente para agregarlo", "Cliente", "modalUpdateSocio");
		}else showReplyMessage(1, "La cédula ingresada no es valida", "Cliente", "modalUpdateSocio");
	}//else showReplyMessage(1, "Debe ingresar cédula o rut para poder identificar el cliente", "Cliente", "modalUpdateSocio");
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