//variables
var idLastAnalisis = null;
var phoneSocio = null;

//funciones sin nombre
$("#formConfirmFileAnalisisMasc").submit(function(e) {
    e.preventDefault();
    console.log("formulario archivos en nuevo analisis");

    if ($("#idInputFileAnalisisMasc").val().length){
	    if ( idLastAnalisis ){
	    	var formData = new FormData(this);
	    	console.log(formData);

	    	formData.append("category", "analisismascota");
	    	formData.append("idCategory", idLastAnalisis);

		    sendAsyncPostFiles( "saveFile", formData)
		    .then(function(response){
		        if ( response.result != 2 ){
		        	$("#modalLoadResultsOrder").modal("hide");
		        	showReplyMessage(response.result, response.message, "Análisis", null, true);
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
	    		console.log("no se ha cargado, se llama al submit nuevamente");
	    		$("#formConfirmFileAnalisisMasc").trigger("submit");
	    	}, 200);
	    }
	}else{
		console.log('en el formulario -analisis- por guardar archivos, pero no se cargaron archivos, saliendo');
	}
});

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
			showReplyMessage(response.result, response.message, "Análisis", "modalAnalisis");
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


	row += "<td class='text-center'>"+ buttonWhatsapp +"</td>";
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
			phoneSocio = responseSocio.socio.telefax;
		}

		$("#titleModalView").html("Análisis");
		$('#dateModalView').html("<b>Diagnositico:</b> " + analisis.fecha);
		$("#textModalView").html("<b>Nombre:</b> " + analisis.nombre + "<hr><b>Detalle: </b>" + analisis.detalle + "<hr>");

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
		$("#divButtonLeftModalView").attr("disable",true);
		$("#divButtonRightModalView").attr("hidden",true);
		$("#divButtonRightModalView").attr("disable",true);


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