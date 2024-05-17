//variables
var idLastAnalisis = null;
var phoneSocio = null;

//funciones sin nombre
$("#formConfirmFileAnalisisMasc").submit(function(e) {
    e.preventDefault();
    console.log("formulario archivos en nuevo analisis");

    $("#modalLoadResultsOrder").modal("hide");
	$("#modalLoadResultsOrder").hide();


    if ($("#idInputFileAnalisisMasc").val().length){
	    if ( idLastAnalisis !== null && idLastAnalisis > 0 ){
	    	let errores = {}
		    errores.result = 2;
			errores.message = "Archivos subidos correctamente.";

			const progressBarIdAnalisis = loadPrograssBar();
		    $('#progressbar h5').text("Subiendo archivos...");
			$("#progressbar").modal("show");

	    	const files = document.getElementById('idInputFileAnalisisMasc').files;
    		let errormessage = "Error al subir los siguientes archivos:<br>";


			for (var i = 0; i < files.length; i++) {

	    		let file = files[i];
	    		currentsize[i] = 0;
			    let start = 0;
			    uploadChunkAnalisis(file, currentsize[i], start)
			    .then((respuesta)=>{
			    	if(respuesta){
				    	if(respuesta.result != 2){
				    		errores.result = 1;
							errormessage += respuesta.nameFile + "<br>"
							errores.message = errormessage

							stopPrograssBar(progressBarIdAnalisis);
							$('#progressbar').modal("hide");
							showReplyMessage(errores.result, errores.message, "Archivos", null)
				    	}else{
				    		if(files.length == i) {
							    stopPrograssBar(progressBarIdAnalisis);
								$('#progressbar').modal("hide");
							}

							showReplyMessage(errores.result, errores.message, "Archivos", null)

				    	}

				    }
				    else{
				    	if(files.length == i) {
						    stopPrograssBar(progressBarIdAnalisis);
							$('#progressbar').modal("hide");

							showReplyMessage(errores.result, errores.message, "Archivos", null)

						}
				    }
			    })
	    	}
	    }else{
	    	setTimeout(()=>{
	    		console.log("no se ha cargado, se llama al submit nuevamente");
	    		if ($('#modalAnalisis').hasClass('show') === true ){
	    			$("#formConfirmFileAnalisisMasc").trigger("submit");
	    		}
	    	}, 10000);
	    }
	}else{
		console.log('en el formulario -analisis- por guardar archivos, pero no se cargaron archivos, saliendo');
	}
});

function uploadChunkAnalisis( file, currentsize, start) {
	return new Promise( function(resolve, reject){

	    let chunk = file.slice(start, start + chunkSize);
	    var formData = new FormData();
		formData.append("category", "analisismascota");
		formData.append("idCategory", idLastAnalisis);
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
		            resolve( uploadChunkAnalisis(file, currentsize, start) );
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

//funciones con nombre
function openModalAnalaisis(button){

	let muerto = $("#inputFallecimiento").val() || null;

	if ( muerto ){

		if(button.id == "NUEVOANALISIS"){
			showReplyMessage(0, "Mascota con fecha de fallecimiento<br>¿Desea agregar análisis igualmente?", "FALLECIDO", null);
		}else{
			showReplyMessage(0, "Mascota con fecha de fallecimiento<br>¿Desea editar los datos igualmente?", "FALLECIDO", null);
		}

		$("#modalButtonResponse").click(function(){
			if(button.id == "NUEVOANALISIS"){
				$('#titleModalAnalisis').html("Nuevo análisis");
				clearComponents();

				$('#buttonConfirmModalAnalisis').off('click');
				$('#buttonConfirmModalAnalisis').click(function(){
					crearAnalisis(button.name);
				});
				$('#modalAnalisis').modal();
			}else{

				//modificando datos del analisis
				let response = sendPost("getAnalisis", {idAnalisis: button.name});
				if(response.result == 2){
					$('#titleModalAnalisis').html("Modificar análisis");

					$('#inputNombreAnalisis').val(response.objectResult.nombre);
					$('#inputFechaAnalisis').val(response.objectResult.fecha);
					$('#inputDetalleAnalisis').val(response.objectResult.detalle);
					$('#inputResultadoAnalisis').val(response.objectResult.resultado);

					$('#buttonConfirmModalAnalisis').off('click');
					$('#buttonConfirmModalAnalisis').click(function(){
						modificarAnalisis(button.name);
						idLastAnalisis = button.name;
					});
					$('#modalAnalisis').modal();
				}
			}

		});

	}
	else{


		if(button.id == "NUEVOANALISIS"){
			$('#titleModalAnalisis').html("Nuevo análisis");
			clearComponents();

			$('#buttonConfirmModalAnalisis').off('click');
			$('#buttonConfirmModalAnalisis').click(function(){
				crearAnalisis(button.name);
			});
			$('#modalAnalisis').modal();
		}else{

			//modificando datos del analisis
			let response = sendPost("getAnalisis", {idAnalisis: button.name});
			if(response.result == 2){
				$('#titleModalAnalisis').html("Modificar análisis");

				$('#inputNombreAnalisis').val(response.objectResult.nombre);
				$('#inputFechaAnalisis').val(response.objectResult.fecha);
				$('#inputDetalleAnalisis').val(response.objectResult.detalle);
				$('#inputResultadoAnalisis').val(response.objectResult.resultado);

				$('#buttonConfirmModalAnalisis').off('click');
				$('#buttonConfirmModalAnalisis').click(function(){
					modificarAnalisis(button.name);
					idLastAnalisis = button.name;
				});
				$('#modalAnalisis').modal();
			}
		}
	}
}

function clearComponents(){
	$('#inputNombreAnalisis').val("");
	$('#inputFechaAnalisis').val(getDateForInput());
	$('#inputDetalleAnalisis').val("");
	$('#inputResultadoAnalisis').val("");
}


function crearAnalisis(idMascota){
	idLastAnalisis = null;

	let nombre = $('#inputNombreAnalisis').val() || null;
	let fecha = $('#inputFechaAnalisis').val() || null;
	let detalle = $('#inputDetalleAnalisis').val() || null;
	let resultado = $('#inputResultadoAnalisis').val() || null;

	if(nombre){
		if(fecha){
			let data = {
				idMascota: idMascota,
				nombre: nombre,
				fecha: fecha,
				detalle: detalle,
				resultado: resultado
			};

			let response = sendPost("insertAnalisis", data);
			//showReplyMessage(response.result, response.message, "Análisis", "modalAnalisis");
			if(response.result == 2){
				idLastAnalisis = response.newAnalisis.idAnalisis;
				let analisis = response.newAnalisis;
				$('#tbodyAnalisis').prepend(createRowAnalsis(analisis.idAnalisis, analisis.nombre, analisis.fecha,analisis.detalle, analisis.resultado));
			}
		}else showReplyMessage(1, "Debe ingresar la fecha del análisis que desea ingresar", "Análisis", "modalAnalisis");
	}else showReplyMessage(1, "Debe ingresar el nombre del análisis que desea ingresar", "Análisis", "modalAnalisis");
}

function modificarAnalisis(idAnalisis){
	let nombre = $('#inputNombreAnalisis').val() || null;
	let fecha = $('#inputFechaAnalisis').val() || null;
	let detalle = $('#inputDetalleAnalisis').val() || null;
	//let resultado = $('#inputResultadoAnalisis').val() || null;
	let resultado = null;

	if(nombre){
		if(fecha){
			if(detalle){
				let data = {
					idAnalisis: idAnalisis,
					nombre: nombre,
					fecha: fecha,
					detalle: detalle,
					resultado: resultado
				};
				let response = sendPost("updateAnalisis", data);
				console.log(response)
				showReplyMessage(response.result, response.message, "Análisis", "modalAnalisis");
				if(response.result == 2){
					let analisis = response.newAnalisis;
					$('#trA' + idAnalisis).replaceWith(createRowAnalsis(analisis.idAnalisis, analisis.nombre, analisis.fecha,analisis.detalle, analisis.resultado));
				}
			}else showReplyMessage(1, "Debe ingresar el detalle del análisis que desea ingresar", "Análisis", "modalAnalisis");
		}else showReplyMessage(1, "Debe ingresar la fecha del análisis que desea ingresar", "Análisis", "modalAnalisis");
	}else showReplyMessage(1, "Debe ingresar el nombre del análisis que desea ingresar", "Análisis", "modalAnalisis");
}

function createRowAnalsis(idAnalisis, nombre, fecha, detalle, resultado){

	let row = "<tr id='trA"+ idAnalisis +"'>";
	row += "<td class='text-center' onclick='verAnalisis("+ idAnalisis +")'>"+ fecha +"</td>";
	row += "<td class='text-center' onclick='verAnalisis("+ idAnalisis +")'>"+ nombre +"</td>";
	//row += "<td class='text-center' onclick='verAnalisis("+ idAnalisis +")'>"+ detalle +"</td>";
	row += "<td class='text-center' style='min-width: 6 em;'>";
	row += "<button class='btn btn-link' name='" + idAnalisis + "' onclick='openModalAnalaisis(this)'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link' onclick='openModalBorrarAnalisis("+ idAnalisis + ")'><i class='fas fa-trash-alt text-dark'></i></button></td>";
	row += "</td>";


	buttonWhatsapp = '<td class="text-center"><button title="No se encontró número de whatsapp" class="btn bg-light" disabled><i class="fab fa-whatsapp"></i></button></td>';
	if ( phoneSocio )
		buttonWhatsapp = '<td class="text-center"><a href="https://wa.me/'+phoneSocio+'" target="_blank"><button title="Enviar archivo '+phoneSocio+'" class="btn bg-light"><i class="fab fa-whatsapp"></i></button></a></td>';


	row += buttonWhatsapp ;
	row += "</tr>";

	return row;
}


function verAnalisis(idAnalisis){
	let response = sendPost("getAnalisisToShow", {idAnalisis: idAnalisis});
	if(response.result == 2){
		let analisis = response.objectResult;

		if ( !phoneSocio ){
			console.log("pidiendo numero de tel del socio");
			let responseSocio = sendPost("getSocioPorMascota", {idMascota: response.objectResult.idMascota});
			if (responseSocio.socio){
				phoneSocio = responseSocio.socio.telefax;
			}
		}

		$("#titleModalView").html("Análisis");
		$('#dateModalView').html("<b>Diagnositico:</b> " + analisis.fecha);
		$("#textModalView").html("<b>Nombre:</b> " + analisis.nombre + "<hr><b>Detalle: </b>" + analisis.detalle + "<hr>");

		//tabla que muestra temperatura y otros datos en la historia clínica
		$("#divDetailsTableModalView table tbody").empty();
		$("#divDetailsTableModalView").attr("hidden", true);
		$("#divDetailsTableModalView").attr("disabled", true);

		if ( analisis.archivos ){
			$("#divFilesTableModalView table tbody").empty();
			$("#divFilesTableModalView").attr("hidden", true);
			$("#divFilesTableModalView").attr("disabled", true);

			$("#thSendFilesTableModalView").attr("hidden", false);
			$("#thSendFilesTableModalView").attr("disabled", false);

			buttonWhatsapp = '<td class="text-center"><button title="No se encontró número de whatsapp" class="btn bg-light" disabled><i class="fab fa-whatsapp"></i></button></td>';
			if ( phoneSocio )
				buttonWhatsapp = '<td class="text-center"><a href="https://wa.me/'+phoneSocio+'" target="_blank"><button title="Enviar archivo '+phoneSocio+'" class="btn bg-light"><i class="fab fa-whatsapp"></i></button></a></td>';

			for (var i = 0; i < analisis.archivos.length; i++) {
				let row = '<tr><td>'+analisis.archivos[i].nombre+'</td><td class="text-center"><button title="Ver archivo"class="btn bg-light" onclick="downloadFile('+analisis.archivos[i].idMedia+')">ver</button></td>'+buttonWhatsapp+'</tr>';

				$("#divFilesTableModalView table tbody").append(row);
			}

			$("#divFilesTableModalView").attr("hidden", false);
			$("#divFilesTableModalView").attr("disabled", false);
		}else{
			$("#divFilesTableModalView table tbody").empty();

			$("#divFilesTableModalView").attr("hidden", true);
			$("#divFilesTableModalView").attr("disabled", true);
		}


		$("#divButtonLeftModalView").attr("hidden",true);
		$("#divButtonLeftModalView").attr("disabled",true);
		$("#divButtonRightModalView").attr("hidden",true);
		$("#divButtonRightModalView").attr("disabled",true);


		var modal = document.getElementById("modalViewDialog");
		modal.className = "modal-dialog modal-dialog-centered";


		$('#modalView .modal-dialog').css('height', '');
		$('#modalView .modal-content').css('height', '');

		$('#modalView').modal();
	}
}

function openModalBorrarAnalisis(idAnalisis){
	$('#titleModalBorrar').html("Borrar análisis")
	$('#textModalBorrar').html("¿Seguro que desea borrar el análisis seleccionado?")
	$('#modalButtonBorrar').off('click');
	$('#modalButtonBorrar').click(function(){
		borrarAnalisis(idAnalisis);
	});
	$('#modalBorrar').modal();
}

function borrarAnalisis(idAnalisis){
	let response = sendPost("deleteAnalisis", {idAnalisis: idAnalisis});
	showReplyMessage(response.result, response.message, "Análisis", "modalBorrar");
	if(response.result == 2)
		$('#trA' + idAnalisis).remove();
}