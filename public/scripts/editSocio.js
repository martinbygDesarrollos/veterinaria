const urlBase = '/veterinaria/public';

function modificarSocio(btn){
	$("#modalModificarSocio").modal('hide');

	var idSocio = btn.id;
	var nombre = document.getElementById("inpNombre").value || null;
	var cedula = document.getElementById("inpCedula").value || null;
	var direccion = document.getElementById("inpDireccion").value || null;
	var telefono = document.getElementById("inpTelefono").value || null;
	var fechaPago = document.getElementById("inpFechaPago").value || null;
	var lugar = document.getElementById("inpLugarPago").value || null;
	var email = document.getElementById("inpEmail").value || null;
	var fechaIngreso = document.getElementById("inpFechaIngreso").value || document.getElementById("inpFechaIngresoActual").innerHTML;
	var rut = document.getElementById("inpRut").value || null;
	var telefax = document.getElementById("inpTelefax").value || null;

	if(lugar == 2){
		var lugarString  = document.getElementById("inpLugarPagoActual").innerHTML;
		if(lugarString == "Veterinaria")
			lugar = 0;
		else lugar = 1;
	}

	if(!validarInformacionSocio(nombre, cedula, direccion, telefono, fechaPago, lugar, email, fechaIngreso, rut, telefax)){
		$.ajax({
			async: false,
			url: urlBase + "/updateSocio",
			type: "POST",
			data: {
				idSocio: idSocio,
				nombre: nombre,
				cedula: cedula,
				direccion: direccion,
				telefono: telefono,
				fechaPago: fechaPago,
				lugarPago: lugar,
				email: email,
				fechaIngreso: fechaIngreso,
				rut: rut,
				telefax: telefax
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);

				if(response.retorno){
					document.getElementById('modalTituloRetorno').innerHTML = "Modificar Socio";
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalMensajeRetorno').innerHTML = response.mensaje;
					$("#modalRetorno").modal();
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar Socio";
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
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar Socio";
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
		$("#modalRetorno").modal();
	}
}

function validarInformacionSocio(nombre, cedula, direccion, telefono, fechaPago, lugar, email, fechaIngreso, rut, telefax){
	var conError = false;
	var mensajeError = "";
	var emailExp = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3,4})+$/;

	if(nombre == null){
		conError = true;
		mensajeError = "Para modificar la información del socio el campo nombre no puede estar vacio.";
	}else if(nombre.length < 8){
		conError = true;
		mensajeError = "El campo nombre debe tener al menos 8 caracteres para ser considerado valido.";
	}else if(cedula == null){
		conError = true;
		mensajeError = "Para modificar la información del socio el campo cédula no puede estar vacio.";
	}else if(cedula.length != 8){
		conError = true;
		mensajeError = "La cédula ingresada no tiene una longitud valida, porfavor vuelva a ingresarla sin puntos ni guiones.";
	}else if(!validarCedula(cedula)){
		conError = true;
		mensajeError = "La cédula ingresada no es valida, porfavor verifiquela.";
	}else if(direccion == null){
		conError = true;
		mensajeError = "Para modificar la información del socio el campo dirección no puede estar vacio.";
	}else if(direccion.length < 8){
		conError = true;
		mensajeError = "El campo direccion debe tener al menos 8 caracteres para ser considerado valido.";
	}else if(telefono == null){
		conError = true;
		mensajeError = "Para modificar la información del socio el campo teléfono no puede estar vacio.";
	}else if(telefono < 5){
		conError = true;
		mensajeError = "El campo teléfono debe tener al menos 5 caracteres numéricos para ser considerado valido.";
	}else if(fechaPago == null){
		conError = true;
		mensajeError = "Para modificar la información del socio el campo fecha de pago no puede estar vacio.";
	}else if( fechaPago > 31 || fechaPago < 1){
		conError = true;
		mensajeError = "El campo fecha de pago debe estar comprendido entre 1-31.";
	}

	if(email != null){
		if(email.length < 10 || emailExp.test(email)){
			conError = true;
			mensajeError = "En caso de ingresar un email para el socio, este debe ser valido.";
		}
	}

	if(rut != null){
		if(validarRut(rut)){
			conError = true;
			mensajeError = "En caso de ingresar un rut para el socio, este debe ser valido.";
		}
	}

	if(telefax != null){
		if(telefax.length < 5){
			conError = true;
			mensajeError = "En caso de ingresar un telefax para el socio, este debe ser valido";
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

function validarCedula(ci){
	ci = clean_ci(ci);
	var dig = ci[ci.length - 1];
	ci = ci.replace(/[0-9]$/, '');
	return (dig == validation_digit(ci));
}

function clean_ci(ci){
	return ci.replace(/\D/g, '');
}