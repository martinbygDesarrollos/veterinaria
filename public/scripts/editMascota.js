
function editarMascota(btn){
	$('#modalEditarMascota').modal('hide');

	var idSocio  = document.getElementById('idSocioAsignado').value || null;
	var idMascota = btn.id;
	var nombre = document.getElementById('inpEditNombre').value || null;
	var raza = document.getElementById('inpEditRaza').value || null;
	var especie = document.getElementById('inpEditEspecie').value || null;
	var sexo = document.getElementById('inpEditSexo').value || null;
	var fechaNacimiento = document.getElementById('inpEditFechaNacimiento').value || document.getElementById('inpFechaNacimientoActual').value || null;
	var color = document.getElementById('inpEditColor').value || null;
	var pelo = document.getElementById('inpEditPelo').value || null;
	var chip = document.getElementById('inpEditChip').value || null;
	var pedigree = document.getElementById('inpEditPedigree').value || null;
	var observaciones = document.getElementById('inpEditObservaciones').value || null;

	if(sexo == 2){
		if( document.getElementById('inpSexoActual').value == "Hembra")
			sexo = 0;
		else sexo = 1;
	}

	if(!validarInformacionMascota(nombre, raza, especie, sexo, fechaNacimiento, idSocio)){
		$.ajax({
			async: false,
			url: urlBase + "/updateMascota",
			type: "POST",
			data: {
				idSocio: idSocio,
				idMascota: idMascota,
				nombre: nombre,
				raza: raza,
				especie: especie,
				sexo: sexo,
				fechaNacimiento: fechaNacimiento,
				color: color,
				pelo: pelo,
				chip: chip,
				pedigree: pedigree,
				observaciones: observaciones
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ", response);

				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial,"Modificar mascota");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null,"Modificar mascota");
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal("hide");
					});
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrio un error y no se pudo establecer la conexíon con el servidor, porfavor vuelva a intentarlo", null, "Conexión");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
	}
}

function validarInformacionMascota(nombre, raza, especie, sexo, fechaNacimiento, idSocio){
	var soloLetras = /^[A-Za-z0-9\s]+$/g;
	var conError = false;
	var mensajeError = "";

	if(nombre == null){
		conError = true;
		mensajeError = "No puede dejar el campo nombre vacio para modificar la información de la mascota";
	}else if(nombre.length < 3){
		conError = true;
		mensajeError = "El campo nombre requiere al menos 3 caracteres para ser considerado valido.";
	}else if(!soloLetras.test(nombre)){
		conError = true;
		mensajeError = "El nombre de la mascota solo puede contener caracteres alfabeticos.";
	}else if(raza == null){
		conError = true;
		mensajeError = "No puede dejar el campo raza vacio para modificar la información de la mascota,";
	}else if(raza.length < 3){
		conError = true;
		mensajeError = "El campo raza requiere al menos 3 caracteres para ser considerado valido.";
	}else if(especie == null){
		conError = true;
		mensajeError = "No puede dejar el campo especie vacio para modificar la información de la mascota.";
	}else if(especie.length < 3){
		conError = true;
		mensajeError = "El campo especie requiere al menos 3 caracteres para ser considerado valido.";
	}else if(sexo == null){
		conError = true;
		mensajeError = "Esta mascota no contaba con sexo precargado por lo que debe ingresarlo para poder modificar la información.";
	}else if(fechaNacimiento == null){
		conError = true;
		mensajeError = "Esta mascota no contaba con una fecha de nacimiento asociada por lo que debe ingresarla para poder modificar la información.";
	}else if(fechaNacimiento > new Date()){
		conError = true;
		mensajeError = "La fecha de nacimiento de la mascota no puede ser posterior a la fecha actual.";
	}else if(idSocio == null){
		conError = true;
		mensajeError = "Esta mascota no contaba con un socio asignado por lo que debe seleccionar uno para poder modificar la información.";
	}

	if(conError){
		showReplyMessage('warning', mensajeError, null, "Modificar mascota");
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}

	return conError;
}