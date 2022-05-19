function openModalEnfermedad(button){
	if(button.id == "NUEVAENFERMEDAD"){
		clearComponentEnfermedad();
		$('#titleModalEnfermedad').html("Nueva enfermedad");
		$('#buttonConfirmModalEnfermedad').off('click');
		$('#buttonConfirmModalEnfermedad').click(function(){
			createNewEnfermedad(button.name);
		});
		$('#modalEnfermedad').modal();
	}else{
		let response = sendPost("getEnfermedad", {idEnfermedad: button.name});
		if(response.result == 2){
			$('#titleModalEnfermedad').html("Modificar enfermedad");

			$('#inputNombreEnfermedad').val(response.objectResult.nombreEnfermedad);
			$('#inputFechaDiagnosticoEnfermedad').val(response.objectResult.fechaDiagnostico);
			$('#inputObservacionesEnfermedad').val(response.objectResult.observaciones);

			$('#buttonConfirmModalEnfermedad').off('click');
			$('#buttonConfirmModalEnfermedad').click(function(){
				updateEnfermedad(button.name);
			});

			$('#modalEnfermedad').modal();
		}
	}
}

function clearComponentEnfermedad(){
	$('#inputNombreEnfermedad').val("");
	$('#inputFechaDiagnosticoEnfermedad').val(getDateForInput());
	$('#inputObservacionesEnfermedad').val("");
}

function createNewEnfermedad(idMascota){
	let nombre = $('#inputNombreEnfermedad').val() || null;
	let fechaDiagnostico = $('#inputFechaDiagnosticoEnfermedad').val() || null;
	let observaciones = $('#inputObservacionesEnfermedad').val() || null;

	if(nombre){
		if(fechaDiagnostico){
			let response = sendPost("insertEnfermedadMascota", {idMascota: idMascota, nombre: nombre, fechaDiagnostico: fechaDiagnostico, observaciones: observaciones});
			showReplyMessage(response.result, response.message, "Agregar enfermedad", "modalEnfermedad");
			if(response.result != 0){
				let enf = response.newEnfermedad;
				$('#tbodyEnfermedades').prepend(createRowEnfermedad(enf.idEnfermedad, enf.nombreEnfermedad, enf.fechaDiagnostico, enf.observaciones));
			}
		}else showReplyMessage(1, "Debe ingresar una fecha de diagnositico para la enfermedad", "Fecha diagnositico requerida", "modalEnfermedad");
	}else showReplyMessage(1, "Debe ingresar un nombre para la enfermedad", "Nombre requerido", "modalEnfermedad");
}

function updateEnfermedad(idEnfermedad){
	let nombre = $('#inputNombreEnfermedad').val() || null;
	let fechaDiagnostico = $('#inputFechaDiagnosticoEnfermedad').val() || null;
	let observaciones = $('#inputObservacionesEnfermedad').val() || null;

	if(nombre){
		if(fechaDiagnostico){
			let response = sendPost("updateEnfermedad", {idEnfermedad: idEnfermedad, nombre: nombre, fechaDiagnostico: fechaDiagnostico, observaciones: observaciones});
			showReplyMessage(response.result, response.message, "Modificar enfermedad", "modalEnfermedad");
			if(response.result != 0){
				let enf = response.updatedEnfermedad;
				$('#trE' + idEnfermedad).replaceWith(createRowEnfermedad(enf.idEnfermedad, enf.nombreEnfermedad, enf.fechaDiagnostico, enf.observaciones));
			}
		}else showReplyMessage(1, "Debe ingresar una fecha de diagnositico para la enfermedad", "Fecha diagnositico requerida", "modalEnfermedad");
	}else showReplyMessage(1, "Debe ingresar un nombre para la enfermedad", "Nombre requerido", "modalEnfermedad");
}

function createRowEnfermedad(idEnfermedad, nombre, fechaDiagnostico, observaciones){
	let row = "<tr id='trE"+ idEnfermedad +"'>";

	row += "<td class='text-center' onclick='showObservaciones("+ observaciones +")'>"+ fechaDiagnostico +"</td>";
	row += "<td class='text-center' onclick='showObservaciones("+ observaciones +")'>"+ nombre +"</td>";
	row += "<td class='text-center' onclick='showObservaciones("+ observaciones +")'>"+ observaciones +"</td>";
	row += "<td class='text-center' style='min-width: 6 em;'>";
	row += "<button class='btn btn-link' name='" + idEnfermedad + "' onclick='openModalEnfermedad(this)'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link' onclick='openModalBorrarEnfermedad("+ idEnfermedad + ")'><i class='fas fa-trash-alt text-dark'></i></button></td>";
	row += "</td>";
	row += "</tr>";

	return row;
}

function openModalBorrarEnfermedad(idEnfermedad){
	$('#titleModalBorrar').html("Borrar enfermedad")
	$('#textModalBorrar').html("¿Seguro que desea borrar la enfermedad seleccionada?")
	$('#modalButtonBorrar').off('click');
	$('#modalButtonBorrar').click(function(){
		borrarEnfermedad(idEnfermedad);
	});
	$('#modalBorrar').modal();
}

function borrarEnfermedad(idEnfermedad){
	let response = sendPost("deleteEnfermedad", {idEnfermedad: idEnfermedad});
	showReplyMessage(response.result, response.message, "Borrar enfermedad", "modalBorrarEnfermedad");
	if(response.result == 2)
		$('#trE'+ idEnfermedad).remove();
}

function showObservaciones(idEnfermedad){
	let response = sendPost("getEnfermedadToShow", {idEnfermedad: idEnfermedad});
	if(response.result == 2){
		let enfermedad = response.objectResult;
		$("#titleModalView").html("Enfermedad");
		$('#dateModalView').html("<b>Diagnositico</b>: " + enfermedad.fechaDiagnostico);
		$("#textModalView").html("<b>Nombre</b>: " + enfermedad.nombreEnfermedad + "<hr><b>Observaciones: </b>" + enfermedad.observaciones + "<hr>");


		$("#divFilesTableModalView").attr("hidden", true);
		$("#divFilesTableModalView").attr("disable", true);


		$('#modalView').modal();
	}
}