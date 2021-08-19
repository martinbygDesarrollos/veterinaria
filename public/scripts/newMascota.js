function insertNewMascota(inputButton){
	let idSocio = inputButton.name;
	let nombre = $('#inputNombre').val() || null;
	let raza = $('#inputRaza').val() || null;
	let especie = $('#inputEspecie').val() || null;
	let fechaNacimiento = $('#inputNacimiento').val() || null;
	let sexo = $('#inputSexo').val() || null;
	let color = $('#inputColor').val() || null;
	let pelo = $('#inputPelo').val() || null;
	let chip = $('#inputChip').val() || null;
	let observaciones = $('#inputObservaciones').val() || null;
	let pedigree = $('#inputPedegree').val() || null;

	if(nombre){
		if(especie){
			if(raza){
				let data = {
					nombre: nombre,
					raza: raza,
					especie: especie,
					nacimiento: fechaNacimiento,
					sexo: sexo,
					color: color,
					pelo: pelo,
					chip: chip,
					observaciones: observaciones,
					idSocio: idSocio,
					pedigree: pedigree
				};
				console.log(data)
				let response = sendPost("insertNewMascota", data);
				showReplyMessage(response.result, response.message, "Agregar mascota", null);
				if(response.result == 2)
					clearComponents();
			}else showReplyMessage(1, "Debe ingresar de la mascota para agregarla", "Raza campo requerido", null);
		}else showReplyMessage(1, "Debe ingresar de la mascota para agregarla", "Especie campo requerido", null);
	}else showReplyMessage(1, "Debe ingresar de la mascota para agregarla", "Nombre campo requerido", null);
}

function clearComponents(){
	$('#inputNombre').val("");
	$('#inputRaza').val("");
	$('#inputEspecie').val("");
	$('#inputFechaNacimiento').val(getDateForInput());
	$('#inputSexo').val(0);
	$('#inputColor').val("");
	$('#inputPelo').val("");
	$('#inputChip').val("");
	$('#inputObservaciones').val("");
	$('#inputPedegree').val(0);
}

function keyEnterPress(eventEnter, value, size){
	if(eventEnter.keyCode == 13){
		if(eventEnter.srcElement.id == "inputNombre")
			$('#inputEspecie').focus();
		else if(eventEnter.srcElement.id == "inputEspecie")
			$('#inputRaza').focus();
		else if(eventEnter.srcElement.id == "inputRaza")
			$('#inputNacimiento').focus();
		else if(eventEnter.srcElement.id == "inputNacimiento")
			$('#inputColor').focus();
		else if(eventEnter.srcElement.id == "inputColor")
			$('#inputPelo').focus();
		else if(eventEnter.srcElement.id == "inputPelo")
			$('#inputChip').focus();
		else if(eventEnter.srcElement.id == "inputChip")
			$('#btnConfirm').click();
	}else if(value != null && value.length == size) {
		return false;
	}
}