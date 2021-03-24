const urlBase = '/veterinarianan/public';

function validarDatosNuevoSocio(nombre, cedula, telefono, direccion, email, rut, telefax, fechaPago, lugarPago){
	var soloLetras = /^[A-Za-z0-9\s]+$/g;
	var emailValido = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3,4})+$/;

	var conError = false;
	var mensajeError = "";
	if(nombre.length <= 4){
		conError = true;
		mensajeError = "El campo nombre debe tener al menos 4 caracteres alfabeticos.";
	}else if(!soloLetras.test(nombre)){
		conError = true;
		mensajeError = "El campo nombre solo permite caracteres alfabeticos.";
	}else if(cedula.length !== 8){
		conError = true;
		mensajeError = "La longitud de la cédula ingresada no corresponde a un documento valido.";
	}else if(!validarCedula(cedula)){
		conError = true;
		mensajeError = "La cédula ingresada no es valida.";
	}else if(telefono.length > 9 || telefono.length < 5){
		conError = true;
		mensajeError = "La longitud del teléfono no corresponde a un número valido.";
	}else if (telefono < 11111){
		conError = true;
		mensajeError = "El número telefonico no es valido.";
	}else if(direccion.length < 4){
		conError = true;
		mensajeError = "La direccion debe tener almenos 4 caracteres alfanuméricos.";
	}else if(fechaPago.length == 0){
		conError = true;
		mensajeError = "Debe seleccionar la fecha en el mes en la que pagara el socio.";
	}else if(fechaPago < 1 || fechaPago > 31){
		conError = true;
		mensajeError = "La campo fecha de Pago debe estar comprendido entre 1-31 ya que corresponde a un día del mes.";
	}else if(lugarPago == 2){
		conError = true;
		mensajeError = "Debe seleccionar el lugar de pago.";
	}

	if(email != null){
		if(emailValido.test(email)){
			conError = true;
			mensajeError = "El correo ingresado no es valido.";
		}
	}

	if(rut!= null){
		if(!validarRut(rut)){
			conError = true;
			mensajeError = "El rut ingresado no es valido.";
		}
	}

	if(conError){
		$('#modalColorRetorno').removeClass('alert-success');
		$('#modalColorRetorno').removeClass('alert-danger');
		$('#modalColorRetorno').addClass('alert-warning');
		document.getElementById('modalTituloRetorno').innerHTML = "Información no valida";
		document.getElementById('modalMensajeRetorno').innerHTML = mensajeError;
		$("#modalRetorno").modal();
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}

	return conError;
}

function insertarNuevoSocio(){
	var nombre = document.getElementById('inpNombreSocio').value;
	var cedula = document.getElementById('inpCedulaSocio').value;
	var telefono = document.getElementById('inpTelefonoSocio').value;
	var direccion = document.getElementById('inpDireccionSocio').value;
	var email = document.getElementById('inpEmailSocio').value || null;
	var rut = document.getElementById('inpRutSocio').value || null;
	var telefax = document.getElementById('inpTelefaxSocio').value || null;
	var fechaPago = document.getElementById('inpFechaPagoSocio').value;
	var lugarPago = document.getElementById('inpLugarPagoSocio').value;


	var conError = validarDatosNuevoSocio(nombre, cedula, telefono, direccion, email, rut, telefax, fechaPago, lugarPago);

	if(!conError){

		document.getElementById('modalTituloRetorno').innerHTML = "Nuevo socio";
		$.ajax({
			async: false,
			url: urlBase + "/insertNewSocio",
			type: "POST",
			data: {
				nombre: nombre,
				cedula: cedula,
				telefono: telefono,
				direccion: direccion,
				email: email,
				rut: rut,
				telefax: telefax,
				fechaPago: fechaPago,
				lugarPago: lugarPago
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);

				if(response.retorno){
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalMensajeRetorno').innerHTML = response.mensaje;
					$("#modalRetorno").modal();
					$("#modalButtonRetorno").click(function(){
						document.getElementById('containerNuevaMascota').style.display = "block";
						document.getElementById('containerNuevoSocio').style.display = "none";
						document.getElementById('pInformacionMascota').innerHTML = "Usted esta ingresando la información para la mascota de " + nombre;
						document.getElementById('btnCrearMascota').innerHTML = "Ingresar nueva mascota para " + nombre;
						document.getElementById('idSocioAgregado').value = response.idSocio;
					});
				}else{
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').removeClass('alert-success');
					$('#modalColorRetorno').addClass('alert-danger');
					document.getElementById('modalMensajeRetorno').innerHTML = response.mensajeError;
					$("#modalRetorno").modal();
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal("hide");
					});
				}


			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo","Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}
}

function validarDatosNuevaMascota(nombre, especie, nacimiento, raza, color, sexo, pelo, chip, pedigree, observaciones){
	var soloLetras = /^[A-Za-z0-9\s]+$/g;
	var conError = false;
	var mensajeError = "";
	var fechaActual = new Date();

	if(nombre.length < 4){
		conError = true;
		mensajeError = "El nombre de la mascota debe contener al menos 4 caracteres alfabeticos.";
	}else if(!soloLetras.test(nombre)){
		conError = true;
		mensajeError = "El nombre de la mascota solo admite caracteres alfabeticos.";
	}else if(especie.length < 4){
		conError = true;
		mensajeError = "La especie de la mascota debe tener al menos 4 caracteres alfabeticos.";
	}else if(nacimiento >= fechaActual){
		conError = true;
		mensajeError = "La fecha de nacimiento no puede ser superior o igual a la fecha actual.";
	}else if(raza.length < 4){
		conError = true;
		mensajeError = "La raza de la mascota debe contener al menos 4 caracteres alfabeticos.";
	}else if(soloLetras.test(raza)){
		conError = true;
		mensajeError = "La raza de la mascota solo admite caracteres alfabeticos.";
	}else if(color.length < 4){
		conError = true;
		mensajeError = "El color de la mascota debe contener al menos 4 caracteres alfabeticos.";
	}else if(!soloLetras.test(color)){
		conError = true;
		mensajeError = "El color de la mascota solo admite caracteres alfabeticos.";
	}else if(sexo == 2){
		conError = true;
		mensajeError = "Debe seleccionar el sexo de la mascota para ingresarla.";
	}

	if(pelo != null){
		if(pelo.length < 4){
			conError = true;
			mensajeError = "El campo pelo puede ser nulo, o debe tener un minimo de 4 caracteres alfabeticos.";
		}
	}

	if(chip != null){
		if(chip.length != 15){
			conError = true;
			mensajeError = "El chip de la mascota debe contener 15 caracteres númericos.";
		}
	}

	if(conError){
		$('#modalColorRetorno').removeClass('alert-success');
		$('#modalColorRetorno').addClass('alert-danger');
		document.getElementById('modalTituloRetorno').innerHTML = "Información no valida";
		document.getElementById('modalMensajeRetorno').innerHTML = mensajeError;
		$("#modalRetorno").modal();
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}


	return conError;
}

function insertarNuevaMascota(){
	var nombre = document.getElementById('inpNombreMascota').value;
	var especie = document.getElementById('inpEspecieMascota').value;
	var nacimiento = document.getElementById('inpNacimientoMascota').value;
	var raza = document.getElementById('inpRazaMascota').value;
	var color = document.getElementById('inpColorMascota').value;
	var sexo = document.getElementById('slcSexoMascota').value;
	var pelo = document.getElementById('inpPeloMascota').value || null;
	var chip = document.getElementById('inpChipMascota').value || null;
	var pedigree = document.getElementById('slcPedigreeMascota').value;
	var observaciones = document.getElementById('inpObservacionesMascota').value;

	var idSocio = document.getElementById('idSocioAgregado').value;

	var conError = validarDatosNuevaMascota(nombre, especie, nacimiento, raza, color, sexo, pelo, chip, pedigree, observaciones);

	if(!conError){

		$.ajax({
			async: false,
			url: urlBase + "/insertNewMascota",
			type: "POST",
			data: {
				idSocio: idSocio,
				nombre: nombre,
				especie: especie,
				nacimiento: nacimiento,
				raza: raza,
				color: color,
				sexo: sexo,
				pelo: pelo,
				chip: chip,
				pedigree: pedigree,
				observaciones: observaciones
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);

				if(response.retorno){

					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalMensajeRetorno').innerHTML = response.mensaje;
					$("#modalRetorno").modal();
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					$('#modalColorRetorno').removeClass('alert-success');
					$('#modalColorRetorno').addClass('alert-danger');
					document.getElementById('modalMensajeRetorno').innerHTML = response.mensajeError;
					$("#modalRetorno").modal();
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal("hide");
					});
				}
				document.getElementById('modalTituloRetorno').innerHTML = "Nueva mascota";
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo","Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}

}






function validarCedula(ci){
	ci = clean_ci(ci);
	var dig = ci[ci.length - 1];
	ci = ci.replace(/[0-9]$/, '');
	return (dig == validation_digit(ci));
}

function validation_digit(ci){
	var a = 0;
	var i = 0;
	if(ci.length <= 6){
		for(i = ci.length; i < 7; i++){
			ci = '0' + ci;
		}
	}
	for(i = 0; i < 7; i++){
		a += (parseInt("2987634"[i]) * parseInt(ci[i])) % 10;
	}
	if(a%10 === 0){
		return 0;
	}else{
		return 10 - a % 10;
	}
}

function clean_ci(ci){
	return ci.replace(/\D/g, '');
}

function validarRut(rut){

	if (rut.length != 12){
		return false;
	}
	if (!/^([0-9])*$/.test(rut)){
		return false;
	}
	var dc = rut.substr(11, 1);
	var rut = rut.substr(0, 11);
	var total = 0;
	var factor = 2;

	for (i = 10; i >= 0; i--) {
		total += (factor * rut.substr(i, 1));
		factor = (factor == 9)?2:++factor;
	}

	var dv = 11 - (total % 11);

	if (dv == 11){
		dv = 0;
	}else if(dv == 10){
		dv = 1;
	}
	if(dv == dc){
		return true;
	}
	return false;
}