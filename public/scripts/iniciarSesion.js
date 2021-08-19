function iniciarSesion(){
	var usuario = $('#inpUsuario').val() || null;
	var pass = $('#inpPass').val() || null;

	if(usuario){
		if(pass){
			let response = sendPost("iniciarSesion", {usuario: usuario, pass: pass});
			if(response.result == 2)
				window.location.href = getSiteURL();
			else showReplyMessage(response.result, response.message, "Iniciar sesisón", null);
		}else showReplyMessage(1, "Debe ingresar la contraseña para iniciar sesión", "Contraseña campo requerido", null);
	}else showReplyMessage(1, "Debe ingresar un usuario para iniciar sesión", "Usuario campo requerido", null);
}