const urlBase = '/veterinaria/public';

function fijarCostoCuota(){
	$("#modalFijarCuota").modal('hide');
	var cuotaUno = document.getElementById("inpCuotaUna").value || null;
	var cuotaDos = document.getElementById("inpCuotaDos").value || null;
	var cuotaExtra = document.getElementById("inpCuotaExtra").value || null;

	if(!validarDatosCuota(cuotaUno, cuotaDos, cuotaExtra)){
		$.ajax({
			async: false,
			url: urlBase + "/updateCuotaSocio",
			type: "POST",
			data: {
				cuotaUna: cuotaUno,
				cuotaDos: cuotaDos,
				cuotaExtra: cuotaExtra
			},
			success: function (response) {
				response = response.trim();
				response = jQuery.parseJSON(response);
				console.log("response SUCCESS: ",response);
				if(response.retorno){
					$('#modalColorRetorno').removeClass('alert-danger');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-success');
					document.getElementById('modalTituloRetorno').innerHTML = "Modificar cuota";
					$("#modalButtonRetorno").click(function(){
						window.location.reload();
					});
				}else{
					$('#modalColorRetorno').removeClass('alert-success');
					$('#modalColorRetorno').removeClass('alert-warning');
					$('#modalColorRetorno').addClass('alert-danger');
					document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar cuota";
					$("#modalButtonRetorno").click(function(){
						$("#modalRetorno").modal('hide');
					});
				}
				document.getElementById("modalMensajeRetorno").innerHTML = response.mensaje;

			},
			error: function (response) {
				console.log("response ERROR:" + eval(response));
				document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar cuota";
				$('#modalColorRetorno').removeClass('alert-success');
				$('#modalColorRetorno').removeClass('alert-warning');
				$('#modalColorRetorno').addClass('alert-danger');
				$("#modalRetorno").modal();
				$("#modalButtonRetorno").click(function(){
					$("#modalRetorno").modal("hide");
				});
			},
		});
		$("#modalRetorno").modal();
	}

}

function validarDatosCuota(cuotaUno, cuotaDos, cuotaExtra){
	var conError  = false;
	var mensajeError = "";
	var uno = parseInt(cuotaUno);
	var dos = parseInt(cuotaDos);
	var extra = parseInt(cuotaExtra);

	if(uno == null){
		conError = true;
		mensajeError = "No puede fijar las nuevas cuotas sin especificar la cuota para socios con una mascota.";
	}else if(dos == null ){
		conError = true;
		mensajeError = "No puede fijar las nuevas cuotas sin especificar la cuota para socios con dos mascota.";
	}else if(extra == null){
		conError = true;
		mensajeError = "No puede fijar las nuevas cuotas sin especificar la cuota para socios con más de dos mascota.";
	}else if(uno >= dos){
		conError = true;
		mensajeError = "Esta fijando una cuota por mascota única mayor a la cuota por dos mascotas.";
	}else if(extra > uno){
		conError = true;
		mensajeError = "El valor para la cuota extra no puede superar el valor de la cuota para una mascota";
	}else if(extra > dos){
		conError = true;
		mensajeError = "El valor para la cuota extra no puede superar el valor de la cuota para dos mascotas";
	}

	if(conError){

		$("#modalColorRetorno").addClass('alert-warning');
		document.getElementById('modalTituloRetorno').innerHTML = "Error: Modificar cuota";
		document.getElementById("modalMensajeRetorno").innerHTML = mensajeError;
		$("#modalButtonRetorno").click(function(){
			$("#modalRetorno").modal("hide");
		});
		$("#modalRetorno").modal();
	}

	return conError;

}

function fijarContraseñaAdministrador(){

}