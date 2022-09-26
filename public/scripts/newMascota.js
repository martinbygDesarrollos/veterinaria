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
	let peso = $('#inputPeso').val() || null;

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
					pedigree: pedigree,
					peso: peso
				};
				sendAsyncPost("insertNewMascota", data)
				.then((response)=>{
					if(response.result == 2){
						clearComponents();
						url = getSiteURL()+"ver-mascota/"+response.idMascota;
						window.location.href = url;
					}else{
						showReplyMessage(response.result, response.message, "Mascota", null);
					}
				})

			}else showReplyMessage(1, "Debe ingresar raza de la mascota para agregarla", "Mascota", null);
		}else showReplyMessage(1, "Debe ingresar especie de la mascota para agregarla", "Mascota", null);
	}else showReplyMessage(1, "Debe ingresar nombre de la mascota para agregarla", "Mascota", null);
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