const urlBase = '/veterinarianan/public';

function iniciarSesion(){
	var usuario = document.getElementById('inpUsuario').value || null;
	var pass = document.getElementById('inpPass').value || null;

	if(!verificarDatosUsuario(usuario, pass)){
		$.ajax({
			async: false,
			url: urlBase + "/iniciarSesion",
			type: "POST",
			data: {
				usuario: usuario,
				pass: pass
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);

				if(response.retorno){
					if(response.primerSesion == 1){
						showReplyMessage('sucess', response.mensaje, null,  "Iniciar sesisón");
						$("#modalButtonRetorno").click(function(){
							window.location.href = urlBase;
						});
					}else{
						window.location.href = urlBase;
					}
				}else{
					showReplyMessage('danger', response.mensajeError, null, "Iniciar sesisón");
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
}

function verificarDatosUsuario(usuario, pass){

	var conError = false;
	var mensajeError = "";

	if(usuario == null){
		conError = true;
		mensajeError = "Debe ingresar un usuario para iniciar sesión";
	}else if(usuario.length < 4){
		conError = true;
		mensajeError = "El sistema no permite nombres de usuario con menos de 4 caracteres.";
	}else if(pass == null){
		conError = true;
		mensajeError = "Debe ingresar una contraseña para iniciar sesión.";
	}else if(pass.length < 5){
		conError = true;
		mensajeError = "El sistema no permite contraseñas con menos de 5 caracteres.";
	}

	if(conError){
		showReplyMessage('warning', mensajeError , null, "Iniciar sesisón");
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
	}
	return conError;
}