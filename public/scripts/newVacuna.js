const urlBase = '/veterinaria/public';

function agregarNuevaVacuna(){
	var codigo = document.getElementById('inpCodigoVacuna').value || null;
	var nombre = document.getElementById('inpNombreVacuna').value || null;
	var laboratorio = document.getElementById('inpLaboratorioVacuna').value || null;

	if(!validarDatosVacuna(codigo, nombre, laboratorio)){
		$.ajax({
			async: false,
			url: urlBase + "/insertNewVacuna",
			type: "POST",
			data: {
				codigo: codigo,
				nombre: nombre,
				laboratorio: laboratorio
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					$('#colorRetorno').removeClass('alert-warning');
					$('#colorRetorno').removeClass('alert-danger');
					$('#colorRetorno').addClass('alert-success');
					document.getElementById('modalTituloRetorno').innerHTML = "Nueva vacuna";
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					$('#colorRetorno').removeClass('alert-warning');
					$('#colorRetorno').removeClass('alert-success');
					$('#colorRetorno').addClass('alert-danger');
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Nueva vacuna";
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				$('#colorRetorno').removeClass('alert-success');
				$('#colorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#buttonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
		$("#modalRetorno").modal();
	}
}

function validarDatosVacuna(codigo, nombre, laboratorio){
	conError = false;
	mensajeError = "";

	if(codigo == null){
		conError = true;
		mensajeError = "El campo código de la vacuna no puede ingresarse nulo."
	}else if(codigo.length < 5){
		conError = true;
		mensajeError = "El campo código de la vacuna debe tener al menos 5 caracteres para ser considerado valido.";
	}else if(nombre == null){
		conError = true;
		mensajeError = "El campo nombre de la vacuna no puede ser ingresado nulo.";
	}else if(nombre.length < 5){
		conError = true;
		mensajeError = "El campo nombre de la vacuna debe tener al menos 5 caracteres para ser considerado valido.";
	}else if(laboratorio == null){
		conError = true;
		mensajeError = "El campo laboratorio de la vacuna no puede ser ingresado nulo.";
	}else if(laboratorio.length < 5){
		conError = true;
		mensajeError = "El campo laboratorio debe tener al menos 5 caracteres para ser considerado valido.";
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