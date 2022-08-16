const urlBase = '/veterinarianan/public';

function seleccionarSocio(btn){
	var idMascota = btn.name;
	var idSocio = btn.id;

	$("#modalButtonAsociar").click(function(){
		vincularSocioMascota(idSocio, idMascota);
	});
	$("#modalAsociar").modal();
}

function vincularSocioMascota(idSocio, idMascota){
	$("#modalAsociar").modal('hide');
	console.log(idSocio)
	console.log(idMascota)
	$.ajax({
		async: false,
		url: urlBase + "/vincularSocioMascota",
		type: "POST",
		data: {
			idSocio: idSocio,
			idMascota: idMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ", response);

			if(response.retorno){
				showReplyMessage('success', response.mensaje, response.enHistorial, "Cliente");
				$("#modalButtonRetorno").click(function(){
					window.location.href = urlBase + "/mascotas";
				});
			}else{
				showReplyMessage('danger', response.mensajeError, null,"Cliente");
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		},
	});
}

function cargarTabla(){
	var idMascota = document.getElementById('idMascota').value;
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
				var fila = "<tr><td class='text-center'><button class='btn btn-success btn-sm' id='" + socios[i].idSocio + "' name='" + idMascota + "' onclick='seleccionarSocio(this)'><i class='fas fa-check'></i></button></td>" +
				"<td class='text-center'>" + socios[i].nombre + "</td>" +
				"<td class='text-center'>" + socios[i].telefono + "</td>" +
				"<td class='text-center'>" + socios[i].cuota + "</td></tr>";
				$('#tbodySocios').append(fila);
			}
		},
		error: function (response) {
			console.log("response ERROR:" + eval(response));
			showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
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
	var idMascota = document.getElementById('idMascota').value;
	if(aBuscar.length > 0){
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
						var fila = "<tr><td class='text-center'><button class='btn btn-success btn-sm' id='" + socios[i].idSocio + "' name='" + idMascota + "' onclick='seleccionarSocio(this)'><i class='fas fa-check'></i></button></td>" +
						"<td class='text-center'>" + socios[i].nombre + "</td>" +
						"<td class='text-center'>" + socios[i].telefono + "</td>" +
						"<td class='text-center'>" + socios[i].cuota + "</td></tr>";
						$('#tbodySocios').append(fila);
					}
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				showReplyMessage('danger', "Ocurrió un error y no se pudo establecer la conexíon con el servidor, por favor vuelva a intentarlo", null, "Conexión");
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