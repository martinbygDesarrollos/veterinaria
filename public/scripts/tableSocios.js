let menorID = 0;
let maxID = 0;
let estadoSocios = 1;
const urlBase = '/veterinarianan/public';

function cargarTabla(){
	estadoSocios = document.getElementById('estadoSocioVista').value;

	$.ajax({
		async: false,
		url: urlBase + "/getSociosPagina",
		type: "POST",
		data: {
			ultimoID: menorID,
			estadoSocios: estadoSocios
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorID = response.min;
			maxID = response.max;
			$('#tbodySocios' + estadoSocios).empty();
			var socios = response.socios;
			for(var i = 0; i < socios.length; i ++ ){
				var fila = "<tr><td class='text-center'>"+ socios[i].idSocio +"</td>" +
				"<td class='text-center'>" + socios[i].nombre +"</td>" +
				"<td class='text-center'>" + socios[i].telefono +"</td>" +
				"<td class='text-center'>" + socios[i].cuota + "</td>" +
				"<td class='text-center'> <button class='btn btn-info btn-sm'>" +
				"<a style='color: white;'" +
				"href=/veterinarianan/public/verSocio/" + socios[i].idSocio + "'><i class='fas fa-eye'></i></a></button>"+
				"</td> </tr>"
				$('#tbodySocios' + estadoSocios).append(fila);
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

function paginaPosterior(){
	$('#tbodySocios' + estadoSocios).empty();
	cargarTabla();
}

function paginaAnterior(){
	if(menorID != 0){
		menorID = parseInt(maxID) + 10;
		$('#tbodySocios' + estadoSocios).empty();
		cargarTabla();
	}
}

function buscarSocio(inputSearch){
	var aBuscar = inputSearch.value;
	if(aBuscar.length > 3){
		document.getElementById("irAtrasPagina").style.visibility = "hidden";
		document.getElementById("irAdelantePagina").style.visibility = "hidden";
		$('#tbodySocios' + estadoSocios).empty();
		$.ajax({
			async: false,
			url: urlBase + "/buscadorDeSocios",
			type: "POST",
			data: {
				nombreSocio: aBuscar,
				estadoSocio: estadoSocios
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				var socios = response;
				if(socios.length == 0){
					document.getElementById("noHayResultadosMensaje").style.display = "block";
				}else{
					document.getElementById("noHayResultadosMensaje").style.display = "none";
					for(var i = 0; i < socios.length; i ++ ){
						var fila = "<tr><td class='text-center'>"+ socios[i].idSocio +"</td>" +
						"<td class='text-center'>" + socios[i].nombre +"</td>" +
						"<td class='text-center'>" + socios[i].telefono +"</td>" +
						"<td class='text-center'>" + socios[i].cuota + "</td>" +
						"<td class='text-center'> <button class='btn btn-info btn-sm'>" +
						"<a style='color: white;'" +
						"href=/veterinarianan/public/verSocio/" + socios[i].idSocio + "'><i class='fas fa-eye'></i></a></button>"+
						"</td> </tr>"
						$('#tbodySocios' + estadoSocios).append(fila);
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