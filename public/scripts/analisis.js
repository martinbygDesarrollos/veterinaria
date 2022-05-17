//variables
var idLastAnalisis = null;

//funciones sin nombre
$("#formConfirmFileAnalisisMasc").submit(function(e) {
    e.preventDefault();
    console.log("formulario archivos en nuevo analisis");
    if ( idLastAnalisis ){
    	var formData = new FormData(this);
    	console.log(formData);

    	formData.append("category", "analisismascota");
    	formData.append("idCategory", idLastAnalisis);

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
    		console.log("no se ha cargado, se llama al submit nuevamente");
    		$("#formConfirmFileAnalisisMasc").trigger("submit");
    	}, 200);
    }
});

//funciones con nombre
function openModalAnalaisis(button){
	if(button.id == "NUEVOANALISIS"){
		$('#titleModalAnalisis').html("Nuevo análisis");
		clearComponents();

		$('#buttonConfirmModalAnalisis').off('click');
		$('#buttonConfirmModalAnalisis').click(function(){
			crearAnalisis(button.name);
		});
		$('#modalAnalisis').modal();
	}else{
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
			});
			$('#modalAnalisis').modal();
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
			showReplyMessage(response.result, response.message, "Agregar análisis", "modalAnalisis");
			if(response.result == 2){
				idLastAnalisis = response.newAnalisis.idAnalisis;
				let analisis = response.newAnalisis;
				$('#tbodyAnalisis').prepend(createRowAnalsis(analisis.idAnalisis, analisis.nombre, analisis.fecha,analisis.detalle, analisis.resultado));
			}
		}else showReplyMessage(1, "Debe ingresar la fecha del análisis que desea ingresar", "Fecha requerida", "modalAnalisis");
	}else showReplyMessage(1, "Debe ingresar el nombre del análisis que desea ingresar", "Nombre requerido", "modalAnalisis");
}

function modificarAnalisis(idAnalisis){
	let nombre = $('#inputNombreAnalisis').val() || null;
	let fecha = $('#inputFechaAnalisis').val() || null;
	let detalle = $('#inputDetalleAnalisis').val() || null;
	let resultado = $('#inputResultadoAnalisis').val() || null;

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
				console.log(data);
				let response = sendPost("updateAnalisis", data);
				console.log(response)
				showReplyMessage(response.result, response.message, "Modificar análisis", "modalAnalisis");
				if(response.result == 2){
					let analisis = response.newAnalisis;
					$('#trA' + idAnalisis).replaceWith(createRowAnalsis(analisis.idAnalisis, analisis.nombre, analisis.fecha,analisis.detalle, analisis.resultado));
				}
			}else showReplyMessage(1, "Debe ingresar el detalle del análisis que desea ingresar", "Detalle requerido", "modalAnalisis");
		}else showReplyMessage(1, "Debe ingresar la fecha del análisis que desea ingresar", "Fecha requerida", "modalAnalisis");
	}else showReplyMessage(1, "Debe ingresar el nombre del análisis que desea ingresar", "Nombre requerido", "modalAnalisis");
}

function createRowAnalsis(idAnalisis, nombre, fecha, detalle, resultado){
	let row = "<tr id='trA"+ idAnalisis +"'>";
	row += "<td class='text-center' onclick='verAnalisis("+ idAnalisis +")'>"+ fecha +"</td>";
	row += "<td class='text-center' onclick='verAnalisis("+ idAnalisis +")'>"+ nombre +"</td>";
	row += "<td class='text-center' onclick='verAnalisis("+ idAnalisis +")'>"+ detalle +"</td>";
	row += "<td class='text-center' style='min-width: 6 em;'>";
	row += "<button class='btn btn-link' name='" + idAnalisis + "' onclick='openModalAnalaisis(this)'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link' onclick='openModalBorrarAnalisis("+ idAnalisis + ")'><i class='fas fa-trash-alt text-dark'></i></button></td>";
	row += "</td>";
	row += "</tr>";

	return row;
}


function verAnalisis(idAnalisis){
	let response = sendPost("getAnalisisToShow", {idAnalisis: idAnalisis});
	if(response.result == 2){
		let analisis = response.objectResult;
		$("#titleModalView").html("Análisis");
		$('#dateModalView').html("<b>Diagnositico:</b> " + analisis.fecha);
		$("#textModalView").html("<b>Nombre:</b> " + analisis.nombre + "<hr><b>Detalle: </b>" + analisis.detalle + "<hr>");

		if ( analisis.archivos ){
			$("#divFilesTableModalView table tbody").empty();
			$("#divFilesTableModalView").attr("hidden", true);
			$("#divFilesTableModalView").attr("disable", true);

			for (var i = 0; i < analisis.archivos.length; i++) {
				let row = '<tr><td>'+analisis.archivos[i].nombre+'</td><td class="text-center"><button title="Descargar archivo"class="btn bg-light" onclick="downloadFile('+analisis.archivos[i].idMedia+')"><i class="fas fa-download"></i></button></td><td class="text-center"><a href="https://wa.me/" target="_blank"><button title="Enviar archivo" class="btn bg-light"><i class="fas fa-paper-plane"></i></button></a></td></tr>';

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
	showReplyMessage(response.result, response.message, "Borrar análisis", "modalBorrar");
	if(response.result == 2)
		$('#trA' + idAnalisis).remove();
}