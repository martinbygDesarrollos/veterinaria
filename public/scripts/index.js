const urlBase = '/veterinarianan/public';

function realizarBusqueda(){
	var buscar = document.getElementById('inpTextBusqueda').value || null;

	if(buscar != null){
		if(buscar.length > 3){
			window.location.href = urlBase + "/obtenerBusqueda/" + buscar;
		}else{
			showReplyMessage('warning', "Para realizar una busqueda rapida, debe ingresar al menos 4 caracteres.", null, "Buscar");
			$("#modalButtonRetorno").click(function(){
				$("#modalRetorno").modal("hide");
			});
		}
	}
}