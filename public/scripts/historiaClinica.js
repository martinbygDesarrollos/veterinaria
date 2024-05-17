let limitHisto = 0;
var idLastHistoriaClinica = null;
var phoneSocio = null;
var currentsize = []; // tamaño actual del archivo subido
const chunkSize = 1024 * 1024; // 1 MB (tamaño del fragmento)
var listAllIds = [];

$("#formConfirmFileHistory").submit(async function(e) {
    e.preventDefault();
    $("#modalHistoriaClinica").modal("hide");
	$("#modalHistoriaClinica").hide();


    if ( $("#idInputFileResult").val().length > 0 ){


	    if ( idLastHistoriaClinica ){
	    	let errores = {}
		    errores.result = 2;
			errores.message = "Archivos subidos correctamente.";

			progressBarId = loadPrograssBar();
		    $('#progressbar h5').text("Subiendo archivos...");
			$("#progressbar").modal("show");

    		const files = document.getElementById('idInputFileResult').files;
    		let errormessage = "Error al subir los siguientes archivos:<br>";


			for (var i = 0; i < files.length; i++) {

	    		let file = files[i];
	    		currentsize[i] = 0;
			    let start = 0;
			    uploadChunk(file, currentsize[i], start)
			    .then((respuesta)=>{
			    	if(respuesta){
				    	if(respuesta.result != 2){
				    		errores.result = 1;
							errormessage += respuesta.nameFile + "<br>"
							errores.message = errormessage

							stopPrograssBar(progressBarId);
							$('#progressbar').modal("hide");
							showReplyMessage(errores.result, errores.message, "Archivos", null)
				    	}else{
				    		if(files.length == i) {
							    stopPrograssBar(progressBarId);
								$('#progressbar').modal("hide");
							}

							showReplyMessage(errores.result, errores.message, "Archivos", null)

				    	}

				    }
				    else{
				    	if(files.length == i) {
						    stopPrograssBar(progressBarId);
							$('#progressbar').modal("hide");

							showReplyMessage(errores.result, errores.message, "Archivos", null)

						}
				    }
			    })
	    	}
    	}else{
	    	setTimeout(()=>{
	    		//console.log("no se ha cargado id de historia, se llama al submit nuevamente");
	    		$("#formConfirmFileHistory").trigger("submit");
	    	}, 10000);
	    }

	}



});

function uploadChunk( file, currentsize, start) {
	return new Promise( function(resolve, reject){

	    let chunk = file.slice(start, start + chunkSize);
	    var formData = new FormData();
		formData.append("category", "historiasclinica");
		formData.append("idCategory", idLastHistoriaClinica);
		formData.append("filename", file.name);
		formData.append("filesize", file.size);
		formData.append("chunksize", chunk.size);
		formData.append("currentsize", currentsize);
		formData.append('nameInputFile', chunk, file.name + '_' + start); // Agregar fragmento al FormData


	    //let response = sendPostFiles("saveFileLocal", formData)
	    sendAsyncPostFiles("saveFileLocal", formData)
	    .then((response)=>{
	    	console.log(response)
	    	if (response.currentsize && file.name == response.nameFile){
				currentsize = response.currentsize

				if (currentsize < file.size ){
		            start += chunkSize;
		            resolve( uploadChunk(file, currentsize, start) );
		        } else if(response.result == 2) {
		        	if (response.currentsize == currentsize)
		            	currentsize = 0;

					resolve(response);
		        }else if(response.result == 0) {
		        	//return response;
					resolve(response);

		        }
			}
	    })
	})

}

function getAllIdListHistory(id){
	//console.log("buscar todos los ids");

	sendAsyncPost("getAllIdListHistory", {idMascota:id})
	.then(( response )=>{
		//console.log(response);
		if ( response.result == 2 ){
			//console.log("se recuperaron todos los ids del los historiales");
			listAllIds = response.listResult;
		}
	})

}





function cargarHistoriaClinica(idMascota){

	if ( listAllIds.length <= 0 )
		getAllIdListHistory(idMascota);

	sendAsyncPost("getHistoriaClinicaMascota", {lastId: limitHisto, idMascota: idMascota })
	.then(( response )=>{
		if(response.result == 2){
			if(limitHisto != response.lastId){
				limitHisto = response. lastId;

				let list = response.listResult;
				for (let i = 0; i < list.length; i++) {
					let row = createRowHistorial(list[i]);
					$('#tbodyHistoriaClinica').append(row);
				}

			}
		}

	})

}

function createRowHistorial(obj){

	let motivoConsulta = obj.motivoConsulta;
	let fecha = obj.fecha;
	let idHistoriaClinica = obj.idHistoriaClinica;
	let observaciones = obj.observaciones;

	let nomUsuario = obj.nomUsuario+": " ?? "";

	let detalle = "";
	if ( motivoConsulta.length > 0 && observaciones.length > 0)
		detalle = motivoConsulta + " - " + observaciones;
	else if(motivoConsulta.length == 0 && observaciones.length > 0)
		detalle = observaciones;
	else if (motivoConsulta.length > 0 && observaciones.length == 0)
		detalle = motivoConsulta;


	let row = "<tr id='trH"+ idHistoriaClinica +"'>";
	row += "<td class='text-center' onclick='verHistoriaClinica("+ idHistoriaClinica +")' scope='col'>"+ fecha +"</td>";
	row += "<td class='text-center' onclick='verHistoriaClinica("+ idHistoriaClinica +")' scope='col'>"+nomUsuario+ detalle +"</td>";
	row += "<td class='text-center' style='min-width: 6em;'>";
	row += "<button class='btn btn-link' name='" + idHistoriaClinica + "' onclick='openModalHistoria(this)'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link' onclick='openModalBorrarHistoria("+ idHistoriaClinica + ")'><i class='fas fa-trash-alt text-dark'></i></button>";
	row += "</td>";

	if ( phoneSocio ){
		if ( phoneSocio.length > 0 ){
			wppBtn = '<td class="text-center"><a href="https://wa.me/'+phoneSocio+'" target="_blank"><button title="Enviar archivo '+phoneSocio+'" class="btn bg-light"><i class="fab fa-whatsapp"></i></button></a></td>';
		}
	}else {
		let responseSocio = sendPost("getSocioPorMascota", {idMascota: obj.idMascota});
		//console.log("response socio", responseSocio, responseSocio.socio);

		if ( responseSocio.socio ){
			phoneSocio = responseSocio.socio.telefax;

			if ( phoneSocio ){
				if ( phoneSocio.length > 0 ){
					wppBtn = '<td class="text-center"><a href="https://wa.me/'+phoneSocio+'" target="_blank"><button title="Enviar archivo '+phoneSocio+'" class="btn bg-light"><i class="fab fa-whatsapp"></i></button></a></td>';
				}
			}else {
				wppBtn = '<td class="text-center"><button title="No se encontró número de whatsapp" class="btn bg-light" disabled><i class="fab fa-whatsapp"></i></button></td>';
			}
		}else {
			wppBtn = '<td class="text-center"><button title="No se encontró número de whatsapp" class="btn bg-light" disabled><i class="fab fa-whatsapp"></i></button></td>';
		}

	}


	row += wppBtn;
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
	showReplyMessage(response.result, response.message, "Historia clínica", "modalBorrar");
	if(response.result == 2)
		$('#trH' + idHistoriaClinica).remove();
}

function openModalHistoria(button){


	let muerto = $("#inputFallecimiento").val() || null;
	if ( muerto ){

		if(button.id == "NUEVAHISTORIA"){
			showReplyMessage(0, "Mascota con fecha de fallecimiento<br>¿Desea agregar registro de historia clínica igualmente?", "FALLECIDO", null);
		}else{
			showReplyMessage(0, "Mascota con fecha de fallecimiento<br>¿Desea editar los datos igualmente?", "FALLECIDO", null);
		}

		$("#modalButtonResponse").click(function(){
			if(button.id == "NUEVAHISTORIA"){
				$('#titleHistoriaClinica').html("Nueva historia clínica");
				clearModalHistoria();
				$('#buttonConfirmHistoriaClinica').off('click');
				$('#buttonConfirmHistoriaClinica').click(function(){
					crearHistoriaClinica(button.name);
				});
				$('#inputHoraHistoria').val(getCurrentHours());
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
					if( response.objectResult.hora === null || response.objectResult.hora.length < 4 )
						hora = "00:00"
					else hora = response.objectResult.hora.substr(0,2)+":"+response.objectResult.hora.substr(2,2);


					$('#inputHoraHistoria').val(hora);


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
		});


	}else{
		if(button.id == "NUEVAHISTORIA"){
			$('#titleHistoriaClinica').html("Nueva historia clínica");
			clearModalHistoria();
			$('#buttonConfirmHistoriaClinica').off('click');
			$('#buttonConfirmHistoriaClinica').click(function(){
				crearHistoriaClinica(button.name);
			});
			$('#inputHoraHistoria').val(getCurrentHours());
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
				if( response.objectResult.hora === null || response.objectResult.hora.length < 4 )
					hora = "00:00"
				else hora = response.objectResult.hora.substr(0,2)+":"+response.objectResult.hora.substr(2,2);


				$('#inputHoraHistoria').val(hora);


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


}

function crearHistoriaClinica(idMascota){
	idLastHistoriaClinica = null;
	let fecha = $('#inputFechaHistoria').val() || null;
	let hora = $('#inputHoraHistoria').val() || null;
	let motivoConsulta = $('#inputMotivoConsultaHistoria').val() || null;
	let diagnostico= $('#inputDiagnosticoHistoria').val() || null;
	let observaciones = $('#inputObservacionesHistoria').val() || null;
	let peso = $('#inputPesoHistoria').val() || null;
	let temp = $('#inputTemperaturaHistoria').val() || null;
	let fc = $("#inputFrecuenciaCardiacaHistoria").val() || null;
	let fr = $("#inputFRHistoria").val() || null;
	let tllc = $("#inputTiempoLlenadoCapilarHistoria").val() || null;


	hora = hora.replace(':', '')

	let data = {
		idMascota: idMascota,
		fecha: fecha,
		hora: hora,
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
	//showReplyMessage(response.result, response.message, "Historia clínica", "modalHistoriaClinica");
	if(response.result == 2){
		limitHisto = 0;
		$('#tbodyHistoriaClinica').empty();
		cargarHistoriaClinica(idMascota);
		idLastHistoriaClinica = response.newHistoria.idHistoriaClinica
		//let newHistoria = response.newHistoria;
		//$('#tbodyHistoriaClinica').prepend(createRowHistorial(newHistoria));
	}
}

function modificarHistoriaClinica(idHistoriaClinica){
	let fecha = $('#inputFechaHistoria').val() || null;
	let hora = $('#inputHoraHistoria').val() || null;
	let motivoConsulta = $('#inputMotivoConsultaHistoria').val() || null;
	let diagnostico= $('#inputDiagnosticoHistoria').val() || null;
	let observaciones = $('#inputObservacionesHistoria').val() || null;
	let peso = $('#inputPesoHistoria').val() || null;
	let temp = $('#inputTemperaturaHistoria').val() || null;
	let fc = $("#inputFrecuenciaCardiacaHistoria").val() || null;
	let fr = $("#inputFRHistoria").val() || null;
	let tllc = $("#inputTiempoLlenadoCapilarHistoria").val() || null;

	hora = hora.replace(':', '')

	let data = {
		idHistoriaClinica: idHistoriaClinica,
		fecha: fecha,
		hora: hora,
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
	showReplyMessage(response.result, response.message, "Historia clínica", "modalHistoriaClinica");
	if(response.result == 2){
		let updatedHistoria = response.updatedHistoria;
		$('#trH' + idHistoriaClinica).replaceWith(createRowHistorial(updatedHistoria));
	}
}

function clearModalHistoria(){
	$('#inputFechaHistoria').val(getDateForInput());
	$('#inputHoraHistoria').val(getCurrentHours());
	$('#inputMotivoConsultaHistoria').val("");
	$('#inputDiagnosticoHistoria').val("");
	$('#inputObservacionesHistoria').val("");
	$("#idInputFileResult").val('');
	$("#inputPesoHistoria").val('');
	$("#inputTemperaturaHistoria").val('');
	$("#inputFrecuenciaCardiacaHistoria").val('');
	$("#inputFRHistoria").val('');
	$("#inputTiempoLlenadoCapilarHistoria").val('');
}


function verHistoriaClinica(idHistoria){


	$('#modalView').modal("hide");
	//$('#modalView .modal-dialog').css('height', '100%');
	//$('#modalView .modal-content').css('height', '100%');


	let pos = listAllIds.findIndex((obj)=>{
		return obj.idHistoriaClinica == idHistoria;
	});

	let idHistoriaClinicaSiguiente = null; let idHistoriaClinicaPrevia = null;

	sendAsyncPost("getHistoriaClinicaToShow", {idHistoriaClinica: idHistoria})
	.then(( response )=>{

		if(response.result == 2){
			let historia = response.objectResult;
			if( historia.hora === null || historia.hora.length < 4 )
				hora = "00:00"
			else hora = historia.hora.substr(0,2)+":"+ historia.hora.substr(2,2);


			histUsuario = "";
			if ( historia.idUsuario == 0){
				histUsuario = "Sin usuario";
			}else histUsuario = historia.nomUsuario;


			var modal = document.getElementById("modalViewDialog");
			modal.className = "modal-dialog modal-dialog-historia-clinica";


			$("#titleModalView").html("Historia clínica");
			$('#dateModalView').html(histUsuario+" - "+historia.fecha+" "+hora);
			$("#textModalView").html("<b>Motivo consulta:</b> " + historia.motivoConsulta + "<hr><b>Observaciones: </b>" + historia.observaciones + "<hr><b>Tratamiento: </b>" + historia.diagnostico );

			if ( historia.archivos ){
				$("#divFilesTableModalView table tbody").empty();
				$("#divFilesTableModalView").attr("hidden", true);
				$("#divFilesTableModalView").attr("disable", true);

				$("#thSendFilesTableModalView").attr("hidden", true);
				$("#thSendFilesTableModalView").attr("disable", true);

				//divFilesTableModalView
				for (var i = 0; i < historia.archivos.length; i++) {
					let arch = historia.archivos[i];
					let row = null;
					if (typeof arch.ruta !== 'undefined' && arch.ruta !== null && arch.ruta !== ''){
						row = '<tr><td>'+historia.archivos[i].nombre+'</td><td class="text-center"><button title="Ver archivo"class="btn bg-light" onclick="downloadFilePath('+historia.archivos[i].idMedia+')">ver</button></td><td class="text-center"></td></tr>';
					}else{
						row = '<tr><td>'+historia.archivos[i].nombre+'</td><td class="text-center"><button title="Ver archivo"class="btn bg-light" onclick="downloadFile('+historia.archivos[i].idMedia+')">ver</button></td><td class="text-center"></td></tr>';
					}

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
				$("#divDetailsTableModalView").attr("disabled", false);
			}else{
				$("#divDetailsTableModalView table tbody").empty();

				$("#divDetailsTableModalView").attr("hidden", true);
				$("#divDetailsTableModalView").attr("disabled", true);
			}


			$('#modalView').modal("show");
		}

	});
}


function openModalModificarMascota(idMascota){
	sendAsyncPost("getMascotaToEdit", {idMascota: idMascota})
	.then((response)=>{
		//console.log(response);
		if(response.result == 2){
			if ( response.objectResult.fechaFallecimiento ){
				showReplyMessage(0, "Mascota con fecha de fallecimiento<br>¿Desea editar los datos igualmente?", "FALLECIDO", null);
				$("#modalButtonResponse").click(function(){
					updateInformacionMascota("Mascota", response.objectResult);
					$('#modalModificarMascota').modal();
				});

			}else{
				updateInformacionMascota("Mascota", response.objectResult);
				$('#modalModificarMascota').modal();
			}

		}

	})
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
		if(response.result == 2)
			window.location.reload();
		else
			showReplyMessage(response.result, response.message, "Mascota", "modalModificarMascota");

			//updateInformacionMascota("", response.updatedMascota);
	}else showReplyMessage(1, "Debe ingresar nombre de la mascota para modificarla.", "Mascota", "modalModificarMascota");
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



function showNextHistoriaClinica(){
	verHistoriaClinica(idHistoria)

}



function showPreviousHistoriaClinica(){
	verHistoriaClinica(idHistoria)

}


function downloadFilePath( idMedia ){

	sendAsyncPost("getPathFileByIdMedia", {id: idMedia})
	.then(( response )=>{
		console.log(response)
		if (response.result == 2){

			let file = response.objectResult;
			if (typeof file.ruta !== undefined && file.ruta !== null && file.ruta !== "")
				window.location.href = getSiteURL() + 'downloadFile.php?path='+file.ruta+'&category='+file.categoria;
			else
				showReplyMessage(1, "No se encontraron los datos del archivo", "Archivos", null)

		}else{
			showReplyMessage(response.result, response.message, "Archivos", null)
		}

	});

}