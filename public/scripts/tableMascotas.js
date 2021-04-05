let menorID = 0;
let maxID = 0;
let estadoMascota = 1;
const urlBase = '/veterinarianan/public';

function cargarTabla(){
	estadoMascota = document.getElementById('estadoMascotaVista').value;

	$.ajax({
		async: false,
		url: urlBase + "/getMascotasPagina",
		type: "POST",
		data: {
			ultimoID: menorID,
			estadoMascota: estadoMascota
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorID = response.min;
			maxID = response.max;
			$('#tbodyMascotas' + estadoMascota).empty();
			var mascotas = response.mascotas;
			for(var i = 0; i < mascotas.length; i ++ ){
				var fila = "<tr><td class='text-center'>" + mascotas[i].nombre +"</td>" +
				"<td class='text-center'>" + mascotas[i].especie +"</td>" +
				"<td class='text-center'>" + mascotas[i].raza + "</td>" +
				"<td class='text-center'>" + mascotas[i].sexo +"</td>" +
				"<td class='text-center'>" + mascotas[i].fechaNacimiento +"</td>" +
				"<td class='text-center'> <button class='btn btn-info btn-sm'>" +
				"<a style='color: white;'" +
				"href=/veterinarianan/public/verMascota/" + mascotas[i].idMascota + "'><i class='fas fa-eye'></i></a></button>"+
				"</td> </tr>"
				$('#tbodyMascotas' + estadoMascota).append(fila);
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

function paginaPosterior(){
	$('#tbodyMascotas' + estadoMascota).empty();
	cargarTabla();
}

function paginaAnterior(){
	if(menorID != 0){
		menorID = parseInt(maxID) + 10;
		$('#tbodyMascotas' + estadoMascota).empty();
		cargarTabla();
	}
}

function buscarMascota(inputSearch){
	var aBuscar = inputSearch.value;
	if(aBuscar.length > 3){
		document.getElementById("irAtrasPagina").style.visibility = "hidden";
		document.getElementById("irAdelantePagina").style.visibility = "hidden";
		$('#tbodyMascotas' + estadoMascota).empty();
		$.ajax({
			async: false,
			url: urlBase + "/buscadorDeMascotas",
			type: "POST",
			data: {
				nombreMascota: aBuscar,
				estadoMascota: estadoMascota
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				var mascotas = response;
				if(mascotas.length == 0){
					document.getElementById("noHayResultadosMensaje").style.display = "block";
				}else{
					document.getElementById("noHayResultadosMensaje").style.display = "none";
					for(var i = 0; i < mascotas.length; i ++ ){
						var fila = "<tr><td class='text-center'>" + mascotas[i].nombre +"</td>" +
						"<td class='text-center'>" + mascotas[i].raza +"</td>" +
						"<td class='text-center'>" + mascotas[i].especie + "</td>" +
						"<td class='text-center'>" + mascotas[i].sexo + "</td>" +
						"<td class='text-center'>" + mascotas[i].fechaNacimiento + "</td>" +
						"<td class='text-center'> <button class='btn btn-info btn-sm'>" +
						"<a style='color: white;'" +
						"href=/veterinarianan/public/verMascota/" + mascotas[i].idMascota + "'><i class='fas fa-eye'></i></a></button>"+
						"</td> </tr>"
						$('#tbodyMascotas' + estadoMascota).append(fila);
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