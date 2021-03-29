function showReplyMessage(color, mensaje,enHisotiral, titulo){

	if(color == 'danger'){
		$('#modalColorRetorno').removeClass('alert-success');
		$('#modalColorRetorno').removeClass('alert-warning');
		$('#modalColorRetorno').addClass('alert-' + color);
		document.getElementById('modalTituloRetorno').innerHTML = "Error: " + titulo;
	}else if(color == 'success'){
		$('#modalColorRetorno').removeClass('alert-danger');
		$('#modalColorRetorno').removeClass('alert-warning');
		$('#modalColorRetorno').addClass('alert-' + color);
		document.getElementById('modalTituloRetorno').innerHTML = titulo;
	}else if(color == 'warning'){
		$('#modalColorRetorno').removeClass('alert-success');
		$('#modalColorRetorno').removeClass('alert-danger');
		$('#modalColorRetorno').addClass('alert-' + color);
		document.getElementById('modalTituloRetorno').innerHTML = "Datos no validos en: " + titulo;
	}

	if(enHisotiral){
		document.getElementById('modalEnHistorialRetorno').innerHTML = enHisotiral;
	}

	document.getElementById('modalMensajeRetorno').innerHTML = mensaje;
	$("#modalRetorno").modal();
}