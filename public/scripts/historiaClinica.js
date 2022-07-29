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
			let row = createRowHistorial(list[i]);
			$('#tbodyHistoriaClinica').append(row);
		}
	}
}

function createRowHistorial(obj){
	let motivoConsulta = obj.motivoConsulta;
	let fecha = obj.fecha;
	let idHistoriaClinica = obj.idHistoriaClinica;
	let observaciones = obj.observaciones;

	let detalle = "";
	if ( motivoConsulta.length > 0 && observaciones.length > 0)
		detalle = motivoConsulta + " - " + observaciones;
	else if(motivoConsulta.length == 0 && observaciones.length > 0)
		detalle = observaciones;
	else if (motivoConsulta.length > 0 && observaciones.length == 0)
		detalle = motivoConsulta;


	let row = "<tr id='trH"+ idHistoriaClinica +"'>";
	row += "<td class='text-center' onclick='verHistoriaClinica("+ idHistoriaClinica +")' scope='col'>"+ fecha +"</td>";
	row += "<td class='text-center' onclick='verHistoriaClinica("+ idHistoriaClinica +")' scope='col'>"+ detalle +"</td>";
	row += "<td class='text-center' style='min-width: 6em;'>";
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
			$("#inputPesoHistoria").val("");
			$("#inputTemperaturaHistoria").val("");

			let obj = response.objectResult;
			if ( obj.peso || obj.temperatura || obj.fc || obj.fr || obj.tllc){

				let auxPeso = "";
				let auxTemperatura = "";
				let auxFc = "";
				let auxFr = "";
				let auxTllc = "";

				if ( obj.peso )
					auxPeso = obj.peso

				if ( obj.temperatura )
					auxTemperatura = obj.temperatura

				if ( obj.fc )
					auxFc = obj.fc

				if ( obj.fr )
					auxFr = obj.fr

				if ( obj.tllc )
					auxTllc = obj.tllc

				$("#inputPesoHistoria").val(auxPeso);
				$("#inputTemperaturaHistoria").val(auxTemperatura);
				$("#inputFrecuenciaCardiacaHistoria").val(auxFc);
				$("#inputFRHistoria").val(auxFr);
				$("#inputTiempoLlenadoCapilarHistoria").val(auxTllc);
			}

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
	let peso = $('#inputPesoHistoria').val() || null;
	let temp = $('#inputTemperaturaHistoria').val() || null;
	let fc = $("#inputFrecuenciaCardiacaHistoria").val() || null;
	let fr = $("#inputFRHistoria").val() || null;
	let tllc = $("#inputTiempoLlenadoCapilarHistoria").val() || null;

	let data = {
		idMascota: idMascota,
		fecha: fecha,
		motivoConsulta: motivoConsulta,
		diagnostico: diagnostico,
		observaciones: observaciones,
		peso: peso,
		temperatura: temp,
		fc: fc,
		fr: fr,
		tllc: tllc
	};
	let response = sendPost("agregarHistoriaClinica", data);
	showReplyMessage(response.result, response.message, "Agregar historia clínica", "modalHistoriaClinica");
	if(response.result == 2){
		idLastHistoriaClinica = response.newHistoria.idHistoriaClinica
		let newHistoria = response.newHistoria;
		$('#tbodyHistoriaClinica').prepend(createRowHistorial(newHistoria));
	}
}

function modificarHistoriaClinica(idHistoriaClinica){
	let fecha = $('#inputFechaHistoria').val() || null;
	let motivoConsulta = $('#inputMotivoConsultaHistoria').val() || null;
	let diagnostico= $('#inputDiagnosticoHistoria').val() || null;
	let observaciones = $('#inputObservacionesHistoria').val() || null;
	let peso = $('#inputPesoHistoria').val() || null;
	let temp = $('#inputTemperaturaHistoria').val() || null;
	let fc = $("#inputFrecuenciaCardiacaHistoria").val() || null;
	let fr = $("#inputFRHistoria").val() || null;
	let tllc = $("#inputTiempoLlenadoCapilarHistoria").val() || null;

	let data = {
		idHistoriaClinica: idHistoriaClinica,
		fecha: fecha,
		motivoConsulta: motivoConsulta,
		diagnostico: diagnostico,
		observaciones: observaciones,
		peso: peso,
		temperatura: temp,
		fc: fc,
		fr: fr,
		tllc: tllc
	};
	let response = sendPost("modificarHistoriaClinica", data);
	showReplyMessage(response.result, response.message, "Modificar historia clínica", "modalHistoriaClinica");
	if(response.result == 2){
		let updatedHistoria = response.updatedHistoria;
		$('#trH' + idHistoriaClinica).replaceWith(createRowHistorial(updatedHistoria));
	}
}

function clearModalHistoria(){
	$('#inputFechaHistoria').val(getDateForInput());
	$('#inputMotivoConsultaHistoria').val("");
	$('#inputDiagnosticoHistoria').val("");
	$('#inputObservacionesHistoria').val("");
	$("#idInputFileResult").val('');
	$("#inputPesoHistoria").val('');
	$("#inputTemperaturaHistoria").val('');
}

function verHistoriaClinica(idHistoria){
	let response = sendPost("getHistoriaClinicaToShow", {idHistoriaClinica: idHistoria});
	if(response.result == 2){
		let historia = response.objectResult;


		$("#titleModalView").html("Historia clínica");
		$('#dateModalView').html(historia.fecha);
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

		if ( historia.peso || historia.temperatura || historia.fc || historia.fr || historia.tllc){
			$("#divDetailsTableModalView table tbody").empty();

			//divDetailsTableModalView
			let auxPeso = "";
			let auxTemperatura = "";
			let auxFc = "";
			let auxFr = "";
			let auxTllc = "";

			if ( historia.peso )
				auxPeso = historia.peso

			if ( historia.temperatura )
				auxTemperatura = historia.temperatura

			if ( historia.fc )
				auxFc = historia.fc

			if ( historia.fr )
				auxFr = historia.fr

			if ( historia.tllc )
				auxTllc = historia.tllc

			let row = '<tr><td hidden disabled>'+auxPeso+'</td><td>'+auxTemperatura+'</td><td>'+auxFc+'</td><td>'+auxFr+'</td><td>'+auxTllc+'</td></tr>';

			$("#divDetailsTableModalView table tbody").append(row);

			$("#divDetailsTableModalView").attr("hidden", false);
			$("#divDetailsTableModalView").attr("disable", false);
		}else{
			$("#divDetailsTableModalView table tbody").empty();

			$("#divDetailsTableModalView").attr("hidden", true);
			$("#divDetailsTableModalView").attr("disable", true);
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
	let peso = $('#inputPesoMascota').val() || null;
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
					peso: peso,
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
	$('#inputPeso'+ inputFrom).val(mascota.peso);
}


function paginacionHistoriaClinica(){
	console.log("scroll historia");
}