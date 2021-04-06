const urlBase = '/veterinarianan/public';

function showSocios(){
	var contenedorSocio = document.getElementById('containerSocios');
	if(containerSocios.style.display == "block"){
		contenedorSocio.style.display = "none";
	}else{
		contenedorSocio.style.display = "block";
	}
}

function seleccionarSocio(btn){
	var nombre = btn.name;
	var idSocio = btn.id;

	document.getElementById('inpDuenio').value = "N° Socio: " + btn.id + " Nombre: " + nombre;
	document.getElementById('idSocioAsignado').value = idSocio;
	showSocios();
}

function insertNewMascota(){
	$('#modalNuevaMascota').modal('hide');

	var nombre = document.getElementById('inpNombre').value || null;
	var raza = document.getElementById('inpRaza').value || null;
	var especie = document.getElementById('inpEspecie').value || null;
	var fechaNacimiento = document.getElementById('inpFechaNacimiento').value || null;
	var sexo = document.getElementById('inpSexo').value || null;
	var color = document.getElementById('inpColor').value || null;
	var pelo = document.getElementById('inpPelo').value || null;
	var chip = document.getElementById('inpChip').value || null;
	var observaciones = document.getElementById('inpObservaciones').value || null;
	var idSocio = document.getElementById('idSocioAsignado').value || null;
	var pedigree = document.getElementById('inpPedegree').value || null;

	if(validarDatosMascota(nombre, raza, especie, fechaNacimiento, sexo, color, pelo, chip, idSocio)){
		$.ajax({
			async: false,
			url: urlBase + "/insertNewMascota",
			type: "POST",
			data: {
				nombre: nombre,
				raza: raza,
				especie: especie,
				nacimiento: fechaNacimiento,
				sexo: sexo,
				color: color,
				pelo: pelo,
				chip: chip,
				observaciones: observaciones,
				idSocio: idSocio,
				pedigree: pedigree
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					showReplyMessage('success', response.mensaje, response.enHistorial, "Nueva mascota");
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Nueva mascota");
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
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

function validarDatosMascota(nombre, raza, especie, fechaNacimiento, sexo, color, pelo, chip, idSocio){
	var datosValidos = true;
	var mensajeError = "";
	var expRegular = /^[A-Za-z\s]+$/g;

	if(nombre == null){
		datosValidos = false;
		mensajeError = "El nombre de la mascota no puede ser ingresado nulo.";
	}else if(nombre.length < 3){
		datosValidos = false;
		mensajeError = "El nombre de la mascota debe contener al menos 3 caracteres para ser considerado valido.";
	}else if(!/^[A-Za-z\s]+$/g.test(nombre)){
		datosValidos = false;
		mensajeError = "El nombre solo puede contener caracteres alfabeticos para ser considerado valido.";
	}else if ( raza == null){
		datosValidos = false;
		mensajeError = "La raza de la mascota no puede ser ingresada nula.";
	}else if( raza.length < 4){
		datosValidos = false;
		mensajeError = "La raza de la mascota debe contener al menos 4 caracteres para ser considerado valido.";
	}else if(!/^[A-Za-z\s]+$/g.test(raza)){
		datosValidos = false;
		mensajeError = "La raza solo puede contener caracteres alfabeticos para ser considerado valido.";
	}else if ( especie == null){
		datosValidos = false;
		mensajeError = "La especie de la mascota no puede ser ingresada nula.";
	}else if( especie.length < 4){
		datosValidos = false;
		mensajeError = "La especie de la mascota debe contener al menos 4 caracteres para ser considerado valido.";
	}else if(!/^[A-Za-z\s]+$/g.test(especie)){
		datosValidos = false;
		mensajeError = "La especie solo puede contener caracteres alfabeticos para ser considerado valido.";
	}else if(fechaNacimiento == null){
		datosValidos = false;
		mensajeError = "La fecha de nacimiento de la mascota no puede ser ingresada nula.";
	}else if(fechaNacimiento >= new Date()){
		datosValidos = false;
		mensajeError = "La fecha de nacimiento no puede ser superior a la fecha actual.";
	}else if ( sexo == 2){
		datosValidos = false;
		mensajeError = "Debe seleccionar el sexo de la mascota.";
	}else if(idSocio == null){
		datosValidos = false;
		mensajeError = "Debe seleccionar el dueño de su mascota, de no existir puede añadir socio y mascota desde el menú nuevo socio.";
	}

	if (color != null){
		if( color.length < 4){
			datosValidos = false;
			mensajeError = "El color de la mascota debe contener al menos 4 caracteres para ser considerado valido. De lo contrario deje el campo vacío.";
		}else if(!/^[A-Za-z\s]+$/g.test(color)){
			datosValidos = false;
			mensajeError = "El color solo puede contener caracteres alfabeticos para ser considerado valido. De lo contrario deje el campo vacío.";
		}
	}

	if (pelo != null){
		if( pelo.length < 4){
			datosValidos = false;
			mensajeError = "El pelo de la mascota debe contener al menos 4 caracteres para ser considerado valido. De lo contrario deje el campo vacío.";
		}else if(!/^[A-Za-z\s]+$/g.test(pelo)){
			datosValidos = false;
			mensajeError = "El pelo solo puede contener caracteres alfabeticos para ser considerado valido. De lo contrario deje el campo vacío.";
		}
	}

	if ( chip != null){
		if( chip.length < 10){
			datosValidos = false;
			mensajeError = "El de la mascota debe contener al menos 10 caracteres para ser considerado valido.";
		}
	}

	if(!datosValidos){
		showReplyMessage('warning', mensajeError, null, "Nueva mascota");
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}
	return datosValidos;
}

function cargarTabla(){

	$.ajax({
		async: false,
		url: urlBase + "/getSociosPagina",
		type: "POST",
		data: {
			ultimoID: menorID,
			estadoSocios: "1"
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorID = response.min;
			maxID = response.max;
			$('#tbodySocios').empty();
			var socios = response.socios;
			for(var i = 0; i < socios.length; i ++ ){
				var fila = "<tr><td class='text-center'>" +
				"<button class='btn btn-sm btn-outline-primary' id='" + socios[i].idSocio + "' name='" + socios[i].nombre +"' onclick='seleccionarSocio(this)'>" + socios[i].nombre + "</button></td></tr>";
				$('#tbodySocios').append(fila);
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

let menorID = 0;
let maxID = 0;

function paginaPosterior(){
	cargarTabla();
}

function paginaAnterior(){
	if(menorID != 0){
		menorID = parseInt(maxID) + 10;
		cargarTabla();
	}
}

function buscarSocio(inputSearch){
	var aBuscar = inputSearch.value;
	if(aBuscar.length > 3){
		document.getElementById("irAtrasPagina").style.visibility = "hidden";
		document.getElementById("irAdelantePagina").style.visibility = "hidden";
		$.ajax({
			async: false,
			url: urlBase + "/buscadorDeSocios",
			type: "POST",
			data: {
				nombreSocio: aBuscar,
				estadoSocio: "1"
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				var socios = response;
				$('#tbodySocios').empty();
				if(socios.length == 0){
					document.getElementById("noHayResultadosMensaje").style.display = "block";
				}else{
					document.getElementById("noHayResultadosMensaje").style.display = "none";
					for(var i = 0; i < socios.length; i ++ ){
						var fila = "<tr><td class='text-center'>" +
						"<button class='btn btn-sm btn-outline-primary' id='" + socios[i].idSocio + "' name='" + socios[i].nombre +"' onclick='seleccionarSocio(this)'>" + socios[i].nombre + "</button></td></tr>";
						$('#tbodySocios').append(fila);
					}
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
	}else{
		if(aBuscar.length == 0){
			menorID = 0;
			maxID = 0;
			cargarTabla();
			document.getElementById("noHayResultadosMensaje").style.display = "none";
			document.getElementById("irAtrasPagina").style.visibility = "visible";
			document.getElementById("irAdelantePagina").style.visibility = "visible";
		}
	}
}