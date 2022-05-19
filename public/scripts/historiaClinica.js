let lastId = 0;
var idLastHistoriaClinica = null;
var phoneSocio = null;

$("#formConfirmFileHistory").submit(function(e) {
    e.preventDefault();
    if ( $("#idInputFileResult").val().length > 0 ){

	    if ( idLastHistoriaClinica ){
	    	var formData = new FormData(this);
	    	formData.append("category", "historiasclinica");
	    	formData.append("idCategory", idLastHistoriaClinica);

		    sendAsyncPostFiles( "saveFile", formData)
		    .then(function(response){
		        if ( response.result != 2 ){
		        	$("#modalLoadResultsOrder").modal("hide");
		        	showReplyMessage(response.result, response.message, "Orden de trabajo", null, true);
		        }else{
		        	$("#modalLoadResultsOrder").modal("hide");
		        	window.location.reload();
		        }
		    })
		    .catch(function(response){
		        $("#modalLoadResultsOrder").modal("hide");
		        alert(response.message);
		        //console.log(response);
		    })
	    }else{
	    	setTimeout(()=>{
	    		console.log("no se ha cargado id de historia, se llama al submit nuevamente");
	    		$("#formConfirmFileHistory").trigger("submit");
	    	}, 200);
	    }
	}else{
		console.log('en el formulario -historia clinica- por guardar archivos, pero no se cargaron archivos, saliendo');
	}
});

function cargarHistoriaClinica(idMascota){
	let response = sendPost("getHistoriaClinicaMascota", {lastId: lastId, idMascota: idMascota });
	if(response.result == 2){
		if(lastId != response.lastId)
			lastId = response. lastId;
		let list = response.listResult;
		for (let i = 0; i < list.length; i++) {
			let row = createRowHistorial(list[i].idHistoriaClinica, list[i].fecha,  list[i].motivoConsulta);
			$('#tbodyHistoriaClinica').append(row);
		}
	}
}

function createRowHistorial(idHistoriaClinica, fecha, motivoConsulta){
	let row = "<tr id='trH"+ idHistoriaClinica +"'>";
	row += "<td class='text-center' onclick='verHistoriaClinica("+ idHistoriaClinica +")' scope='col'>"+ fecha +"</td>";
	row += "<td class='text-center' onclick='verHistoriaClinica("+ idHistoriaClinica +")' scope='col'>"+ motivoConsulta +"</td>";
	row += "<td class='text-center' style='min-width: 6 em;'>";
	row += "<button class='btn btn-link' name='" + idHistoriaClinica + "' onclick='openModalHistoria(this)'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link' onclick='openModalBorrarHistoria("+ idHistoriaClinica + ")'><i class='fas fa-trash-alt text-dark'></i></button></td>";
	row += "</td>";
	row += "</tr>";

	return row;
}

function openModalBorrarHistoria(idHistoriaClinica){
	$('#titleModalBorrar').html("Borrar historia clínica")
	$('#textModalBorrar').html("¿Seguro que desea borrar la historia clínica seleccionado?")
	$('#modalButtonBorrar').off('click');
	$('#modalButtonBorrar').click(function(){
		borrarHistoriaClinica(idHistoriaClinica);
	});
	$('#modalBorrar').modal();
}

function borrarHistoriaClinica(idHistoriaClinica){
	let response = sendPost("borrarHistoriaClinica", {idHistoriaClinica: idHistoriaClinica});
	showReplyMessage(response.result, response.message, "Borrar hisotira clínica", "modalBorrar");
	if(response.result == 2)
		$('#trH' + idHistoriaClinica).remove();
}

function openModalHistoria(button){
	if(button.id == "NUEVAHISTORIA"){
		$('#titleHistoriaClinica').html("Nueva historia clínica");
		clearModalHistoria();
		$('#buttonConfirmHistoriaClinica').off('click');
		$('#buttonConfirmHistoriaClinica').click(function(){
			crearHistoriaClinica(button.name);
		});
		$('#modalHistoriaClinica').modal();
	}else{
		let response = sendPost("getHistoriaClinicaToEdit", {idHistoriaClinica: button.name });
		if(response.result == 2){
			$('#titleHistoriaClinica').html("Modificar historia clínica");
			$('#inputFechaHistoria').val(response.objectResult.fecha);
			$('#inputMotivoConsultaHistoria').val(response.objectResult.motivoConsulta);
			$('#inputDiagnosticoHistoria').val(response.objectResult.diagnostico);
			$('#inputObservacionesHistoria').val(response.objectResult.observaciones);

			$('#buttonConfirmHistoriaClinica').off('click');
			$('#buttonConfirmHistoriaClinica').click(function(){
				modificarHistoriaClinica(button.name);
				idLastHistoriaClinica = button.name;
			});

			$('#modalHistoriaClinica').modal();
		}
	}
}

function crearHistoriaClinica(idMascota){
	idLastHistoriaClinica = null;
	let fecha = $('#inputFechaHistoria').val() || null;
	let motivoConsulta = $('#inputMotivoConsultaHistoria').val() || null;
	let diagnostico= $('#inputDiagnosticoHistoria').val() || null;
	let observaciones = $('#inputObservacionesHistoria').val() || null;

	if(fecha){
		if(motivoConsulta){
			let data = {
				idMascota: idMascota,
				fecha: fecha,
				motivoConsulta: motivoConsulta,
				diagnostico: diagnostico,
				observaciones: observaciones
			};
			let response = sendPost("agregarHistoriaClinica", data);
			showReplyMessage(response.result, response.message, "Agregar historia clínica", "modalHistoriaClinica");
			if(response.result == 2){
				idLastHistoriaClinica = response.newHistoria.idHistoriaClinica
				let newHistoria = response.newHistoria;
				$('#tbodyHistoriaClinica').prepend(createRowHistorial(newHistoria.idHistoriaClinica, newHistoria.fecha, newHistoria.motivoConsulta));
			}
		}else showReplyMessage(1, "Debe ingresar el motivo de la consulta para agregar una historia clínica", "Motivo consulta requerido", "modalHistoriaClinica");
	}else showReplyMessage(1, "Debe ingresar la fecha para agregar una historia clínica", "Fecha requerida", "modalHistoriaClinica");
}

function modificarHistoriaClinica(idHistoriaClinica){
	let fecha = $('#inputFechaHistoria').val() || null;
	let motivoConsulta = $('#inputMotivoConsultaHistoria').val() || null;
	let diagnostico= $('#inputDiagnosticoHistoria').val() || null;
	let observaciones = $('#inputObservacionesHistoria').val() || null;

	if(fecha){
		if(motivoConsulta){
			let data = {
				idHistoriaClinica: idHistoriaClinica,
				fecha: fecha,
				motivoConsulta: motivoConsulta,
				diagnostico: diagnostico,
				observaciones: observaciones
			};
			let response = sendPost("modificarHistoriaClinica", data);
			showReplyMessage(response.result, response.message, "Modificar historia clínica", "modalHistoriaClinica");
			if(response.result == 2){
				let updatedHistoria = response.updatedHistoria;
				$('#trH' + idHistoriaClinica).replaceWith(createRowHistorial(updatedHistoria.idHistoriaClinica, updatedHistoria.fecha, updatedHistoria.motivoConsulta));
			}
		}else showReplyMessage(1, "Debe ingresar el motivo de la consulta para modificar una historia clínica", "Motivo consulta requerido", "modalHistoriaClinica");
	}else showReplyMessage(1, "Debe ingresar la fecha para modificar una historia clínica", "Fecha requerida", "modalHistoriaClinica");
}

function clearModalHistoria(){
	$('#inputFechaHistoria').val(getDateForInput());
	$('#inputMotivoConsultaHistoria').val("");
	$('#inputDiagnosticoHistoria').val("");
	$('#inputObservacionesHistoria').val("");
	$("#idInputFileResult").val('');
}

function verHistoriaClinica(idHistoria){
	let response = sendPost("getHistoriaClinicaToShow", {idHistoriaClinica: idHistoria});
	if(response.result == 2){
		let historia = response.objectResult;


		$("#titleModalView").html("Historia clínica");
		$('#dateModalView').html("<b>Fecha:</b> " + historia.fecha);
		$("#textModalView").html("<b>Motivo consulta:</b> " + historia.motivoConsulta + "<hr><b>Diagnóstico: </b>" + historia.diagnostico + "<hr><b>Observaciones: </b>" + historia.observaciones + "<hr>");

		if ( historia.archivos ){
			$("#divFilesTableModalView table tbody").empty();
			$("#divFilesTableModalView").attr("hidden", true);
			$("#divFilesTableModalView").attr("disable", true);

			$("#thSendFilesTableModalView").attr("hidden", true);
			$("#thSendFilesTableModalView").attr("disable", true);

			//divFilesTableModalView
			for (var i = 0; i < historia.archivos.length; i++) {
				let row = '<tr><td>'+historia.archivos[i].nombre+'</td><td class="text-center"><button title="Descargar archivo"class="btn bg-light" onclick="downloadFile('+historia.archivos[i].idMedia+')"><i class="fas fa-download"></i></button></td><td class="text-center"></td></tr>';

				$("#divFilesTableModalView table tbody").append(row);
			}

			$("#divFilesTableModalView").attr("hidden", false);
			$("#divFilesTableModalView").attr("disable", false);
		}else{
			$("#divFilesTableModalView table tbody").empty();

			$("#divFilesTableModalView").attr("hidden", true);
			$("#divFilesTableModalView").attr("disable", true);
		}


		$('#modalView').modal();
	}
}


function openModalModificarMascota(idMascota){
	let response = sendPost("getMascotaToEdit", {idMascota: idMascota});
	if(response.result == 2){
		updateInformacionMascota("Mascota", response.objectResult);
		$('#modalModificarMascota').modal();
	}
}

function modificarMascota(idMascota){
	let nombre = $('#inputNombreMascota').val() || null;
	let especie = $('#inputEspecieMascota').val() || null;
	let raza = $('#inputRazaMascota').val() || null;
	let sexo = $('#inputSexoMascota').val() || null;
	let pelo = $('#inputPeloMascota').val() || null;
	let color = $('#inputColorMascota').val() || null;
	let pedigree = $('#inputPedigreeMascota').val() || null;
	let chip = $('#inputChipMascota').val() || null;
	let nacimiento = $('#inputNacimientoMascota').val() || null;
	let muerte = $('#inputFallecimientoMascota').val() || null;
	let observaciones = $('#inputObservacionesMascota').val() || null;

	if(nombre){
		if(especie){
			if(raza){
				let data = {
					idMascota: idMascota,
					nombre: nombre,
					especie: especie,
					raza: raza,
					sexo: sexo,
					pelo: pelo,
					color: color,
					pedigree: pedigree,
					chip: chip,
					fechaNacimiento: nacimiento,
					fechaFallecimiento: muerte,
					observaciones: observaciones
				}
				let response = sendPost("modificarMascota", data);
				showReplyMessage(response.result, response.message, "Modificar mascota", "modalModificarMascota");
				if(response.result == 2)
					updateInformacionMascota("", response.updatedMascota);
			}else showReplyMessage(1, "Debe ingresar el nombre de la mascota para modificarla.", "Nombre requerido", "modalModificarMascota");
		}else showReplyMessage(1, "Debe ingresar la especie de la mascota para modificarla.", "Especie requerida", "modalModificarMascota");
	}else showReplyMessage(1, "Debe ingresar la raza de la mascota para modificarla.", "Raza requerida", "modalModificarMascota");
}

function updateInformacionMascota(inputFrom, mascota){
	$('#inputNombre'+ inputFrom).val(mascota.nombre);
	$('#inputEspecie'+ inputFrom).val(mascota.especie);
	$('#inputRaza'+ inputFrom).val(mascota.raza);
	$('#inputSexo'+ inputFrom).val(mascota.sexo);
	$('#inputPelo'+ inputFrom).val(mascota.pelo);
	$('#inputColor'+ inputFrom).val(mascota.color);
	$('#inputPedigree'+ inputFrom).val(mascota.pedigree);
	$('#inputChip'+ inputFrom).val(mascota.chip);
	$('#inputNacimiento'+ inputFrom).val(mascota.fechaNacimiento);
	$('#inputFallecimiento' + inputFrom).val(mascota.fechaFallecimiento);
	$('#inputObservaciones'+ inputFrom).val(mascota.observaciones);
}