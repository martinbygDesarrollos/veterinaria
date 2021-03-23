const urlBase = '/veterinaria/public';

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
						document.getElementById('modalTituloRetorno').innerHTML = "Primer inico de sesión.";
						$('#modalColorRetorno').removeClass('alert-danger');
						$('#modalColorRetorno').addClass('alert-success');
						document.getElementById('modalMensajeRetorno').innerHTML = response.mensaje;
						$("#modalButtonRetorno").click(function(){
							window.location.href = urlBase;
						});
						$("#modalRetorno").modal();
					}else{
						window.location.href = urlBase;
					}
				}else{
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Iniciar sesión";
					document.getElementById('modalMensajeRetorno').innerHTML = response.mensajeError;
					$('#modalColorRetorno').addClass('alert-danger');
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal("hide");
					});
					$("#modalRetorno").modal();
				}
			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Iniciar sesión";
				document.getElementById('modalMensajeRetorno').innerHTML = "Ocurrio un error y no pudo comunicarse con el servidor, porfavor vuelva a intentarlo.";
				$('#modalColorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
				$("#modalRetorno").modal();
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
		$("#modalColorRetorno").addClass('alert-warning');
		document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva vacuna";
		document.getElementById("modalMensajeRetorno").innerHTML = mensajeError;
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
		$("#modalRetorno").modal();
	}

	return conError;
}