const urlBase = '/veterinarianan/public';

let menorIdHistorialUsuario = 0;
let maxIdHistorialUsuario = 0;

function cargarHistorialUsuario(){
	$.ajax({
		async: false,
		url: urlBase + "/getHistorialUsuariosPagina",
		type: "POST",
		data: {
			ultimoID: menorIdHistorialUsuario,
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			menorIdHistorialUsuario = response.min;
			maxIdHistorialUsuario = response.max;
			var historialUsuarios = response.historial;
			console.log(response)
			$('#tbodyHistorialUsuarios').empty();
			if(historialUsuarios.length == 0){
				document.getElementById("noHayResultadosHistorialUsuariosMensaje").style.display = "block";
				document.getElementById("irAdelantePaginaHistorialUsuarios").style.display = "none";
				document.getElementById("irAtrasPaginaHistorialUsuarios").style.display = "none";
			}else{
				if(historialUsuarios.length < 5){
					document.getElementById("irAdelantePaginaHistorialUsuarios").style.display = "none";
				}else{
					document.getElementById("irAtrasPaginaHistorialUsuarios").style.display = "block";
					document.getElementById("irAdelantePaginaHistorialUsuarios").style.display = "block";
				}
				document.getElementById("noHayResultadosHistorialUsuariosMensaje").style.display = "none";
				for(var i = 0; i < historialUsuarios.length; i ++ ){
					var fila = "<tr><td class='text-center'>" + historialUsuarios[i].fecha +"</td>" +
					"<td class='text-center'>" + historialUsuarios[i].nombre +"</td>" +
					"<td class='text-center'>" + historialUsuarios[i].funcion + "</td>" +
					"<td class='text-center'>" + historialUsuarios[i].observacion +"</td></tr>";
					$('#tbodyHistorialUsuarios').append(fila);
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
}

function paginaPosteriorHistorialUsuarios(){
	cargarHistorialUsuario();
}

function paginaAnteriorHistorialUsuarios(){
	if(menorIdHistorialUsuario != 0){
		menorIdHistorialUsuario = parseInt(maxIdHistorialUsuario) + 10;
		cargarHistorialUsuario();
	}
}