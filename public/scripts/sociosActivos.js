let menorID = 0;
let maxID = 0;
const urlBase = '/veterinarianan/public';
function cargarTabla(){
	$.ajax({
		async: false,
		url: urlBase + "/getSociosPagina",
		type: "POST",
		data: {
			ultimoID: menorID
		},
		success: function (response) {
			response = response.trim();
			response = jQuery.parseJSON(response);
			console.log("response SUCCESS: ",response);
			menorID = response.min;
			maxID = response.max;
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
				$('#tbodySociosActivos').append(fila);
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
	$('#tbodySociosActivos').empty();
	cargarTabla();
}

function paginaAnterior(){
	if(menorID != 0){
		menorID = parseInt(maxID) + 10;
		$('#tbodySociosActivos').empty();
		cargarTabla();
	}
}

function findSocio(){

}