function fijarCostoCuota(){
	$('#modalFijarCuota').modal('hide')
	let cuotaUno = $("#inputCuotaUno").val() || null;
	let cuotaDos = $("#inputCuotaDos").val() || null;
	let cuotaExtra = $("#inputCuotaExtra").val() || null;
	let plazoDeuda = $('#inputPlazoDeuda').val() || null;
	if(cuotaUno){
		if(cuotaDos){
			if(cuotaExtra){
				if(plazoDeuda){
					let response = sendPost('updateAllQuotaSocio', {cuotaUno: cuotaUno, cuotaDos: cuotaDos, cuotaExtra: cuotaExtra, plazoDeuda: plazoDeuda});
					showReplyMessage(response.result, response.message, "Cuota", null);
					if(response.result == 2){
						$("#inputCuotaUno").val(response.quota.cuotaUno);
						$("#inputCuotaDos").val(response.quota.cuotaDos);
						$("#inputCuotaExtra").val(response.quota.cuotaExtra);
						$('#inputPlazoDeuda').val(response.quota.plazoDeuda);
					}
				}else showReplyMessage(1, "Debe ingresar el plazo para desactivar el cliente para modificar los estados de los clientes.", "Cuota", null);
			}else showReplyMessage(1, "Debe ingresar el monto extra por mascota para modificar las cuotas de los clientes.", "Cuota", null);
		}else showReplyMessage(1, "Debe ingresar el monto por dos mascotas para modificar las cuotas de los clientes.", "Cuota", null);
	}else showReplyMessage(1, "Debe ingresar el monto por una mascota para modificar las cuotas de los clientes.", "Cuota", null);
}

function selectUsuarioModificar(idUsuario, nombre, email){

	if(!nombre){
		let response = sendPost("getUsuario", {idUsuario: idUsuario});
		if(response.result == 2){
			nombre = response.objectResult.nombre;
			email = response.objectResult.email;
		}
	}


	$('#inputUsuario').val(nombre);
	$('#inputCorreo').val(email);
	$('#btnNuevoUsuario').html("Modificar")
	$('#btnNuevoUsuario').off('click');
	$('#btnNuevoUsuario').click(function(){
		modificarUsuario(idUsuario);
	});
}

function clearForm(){
	$('#inputUsuario').val("");
	$('#inputCorreo').val("");
	$('#btnNuevoUsuario').html("Agregar");
	$('#btnNuevoUsuario').off('click');
	$('#btnNuevoUsuario').click(function(){
		crearUsuario()
	});
}

function crearUsuario(){
	let usuario = $('#inputUsuario').val() || null;
	let correo = $('#inputCorreo').val() || null;

	if(usuario){
		let response = sendPost("crearUsuario", {usuario: usuario, correo: correo});
		showReplyMessage(response.result, response.message, "Usuarios", null);
		if(response.result == 2){
			let row = createRow(response.newUser.idUsuario, response.newUser.nombre, response.newUser.email);
			$('#tbodyUsers').append(row);
			clearForm();
		}
	}else showReplyMessage(1, "El usuario no puede ser ingresado con el nombre vacio", "Usuarios", null);
}

function modificarUsuario(idUsuario){
	let usuario = $('#inputUsuario').val();
	let correo = $('#inputCorreo').val();

	if(usuario){
		if(correo){
			if(!validateEmail(correo)){
				showReplyMessage(1, "En caso de ingresar un correo, este debe ser valido.", "Usuarios", null);
				return;
			}
		}
		let response = sendPost("modificarUsuario", {idUsuario: idUsuario, usuario: usuario, correo: correo});
		showReplyMessage(response.result, response.message, "Usuarios", null);
		if(response.result == 2){
			$('#' + idUsuario).replaceWith(createRow(response.user.idUsuario, response.user.nombre, response.user.email));
			clearForm();
		}
	}else showReplyMessage(1, "El usuario no puede ser modificado con el nombre de usuario vacio", "Usuarios", null);
}

function deleteUser(idUser){
	let response = sendPost("deleteUser", {idUser: idUser});
	showReplyMessage(response.result, response.message, "Usuarios", null);
	if(response.result == 2)
		$('#' + idUser).remove();
}

function cleanPassword(idUser){
	let response = sendPost('cleanPassword', {idUser: idUser});
	showReplyMessage(response.result, response.message, "Usuarios", null);
}

function createRow(idUsuario, nombre, email){
	let row = "<tr id='"+ idUsuario +"'>";
	row += "<td class='text-center'>" + nombre + "</td>";
	row += "<td class='text-center col-5'>";
	row += "<button class='btn btn-link btn-sm' onclick='selectUsuarioModificar("+ idUsuario + ")' data-toggle='tooltip' data-placement='top' title='Modificar'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link btn-sm' onclick='deleteUser("+ idUsuario + ")' data-toggle='tooltip' data-placement='top' title='Borrar'><i class='fas fa-trash-alt text-dark'></i></button>";
	row += "<button class='btn btn-link btn-sm' onclick='cleanPassword("+ idUsuario + ")'data-toggle='tooltip' data-placement='top' title='Restaurar contraseña'><i class='fas fa-eraser text-dark'></i></button></td></tr>";

	return row;
}




function searchClientOrPetToUnify( value ){
	if (value.length > 0 ){
		sendAsyncPost("getClientOrPetByInput", {value: value, indexLimit: 0})
		.then(( response )=>{
			$("#tbodyUnifyPets").empty();
			if ( response.result == 2 ){
				let list = response.listResult;
				for (var i = 0; i < list.length; i++) {
					let row = createRowUnifyPets(list[i]);
					$("#tbodyUnifyPets").append(row);
				}
			}
		})
	} else
		$("#tbodyUnifyPets").empty();
}


function createRowUnifyPets(obj){

	idmascota = "";
	if ( obj.idMascota ){
		idmascota = obj.idMascota;
	}

	nommascota = "";
	if ( obj.nomMascota ){
		nommascota = obj.nomMascota;
	}

	idsocio = "";
	if ( obj.idSocio ){
		idsocio = obj.idSocio;
	}

	nomsocio = "";
	if ( obj.nomSocio ){
		nomsocio = obj.nomSocio;
	}


	//agregar color a la fila de la tabla según la deuda del cliente
	tipoClient = calculateColorRowByClient(obj.tipo, obj.deudor);

	mascotaViva = "";
	if ( obj.fechaFallecimiento != null && obj.fechaFallecimiento != "" ){
		mascotaViva = "FALLECIDO ";
		row = '<tr class="'+tipoClient.class+' subtexto"  style="color:red; font-weight: bold;">';
	}else {
		row = '<tr class="'+tipoClient.class+'">';
	}

	row += '<td>'+idmascota+'</td>';
	row += '<td>'+mascotaViva+nommascota+'</td>';
	row += '<td>'+idsocio+'</td>';
	row += '<td>'+nomsocio+'</td>';

	if ( idmascota != "" ){
		row += '<td><button type="button" onclick="selectPetsToUnify(`'+idmascota+'`, `'+nommascota+'`)" class="btn btn-info"><i class="fas fa-plus-circle" ></i></button></td>';
	}else{
		row += '<td><button type="button" class="btn btn-info" disabled><i class="fas fa-plus-circle" ></i></button></td>';
	}


	row += '</tr>';

	return row;
}



function selectPetsToUnify(idmascota, nommascota){
	let petone = $("#idUnifyPetOne").val();
	if( petone ){
		$("#idUnifyPetTwo").val(idmascota);
		$("#idUnifyPetTwoName").text(idmascota+" - "+nommascota);
	}else{
		$("#idUnifyPetOne").val(idmascota);
		$("#idUnifyPetOneName").text(idmascota+" - "+nommascota);
	}


}


function cleanUnifyPetsForm(){

	$("#idUnifyPetTwo").val("");
	$("#idUnifyPetTwoName").text("");
	$("#idUnifyPetOne").val("");
	$("#idUnifyPetOneName").text("");
	$("#idInputPetsToUnify").val("");
	$("#tbodyUnifyPets").empty();
}



$("#formUnifyPets").submit((e)=>{
	e.preventDefault();


	//poner pantalla de carga
	var formData = new FormData(document.getElementById("formUnifyPets"));
	sendAsyncPostFiles("unifyPetCards", formData)
	.then((response)=>{

		showReplyMessage(response.result,response.message,"Unificar mascotas", null );
	})

})

function whatsappConnect(element){
	element.disabled = true;
	document.getElementById("spinnerWhatsappLogin").hidden = false;
	document.getElementById("imageWhatsappLogin").hidden = true;
	document.getElementById("pStatusWhatsapp").hidden = true;
	$("#pStatusWhatsapp strong").empty();

	sendAsyncPost("whatsappVerifyStatus")
	.then((response)=>{
		element.disabled = false;
		document.getElementById("spinnerWhatsappLogin").hidden = true;

		if(response.result == 2){
			if(response.qr){
				$("#nav-whatsapp img").attr("src", "data:image/png;base64,"+response.qr);
				document.getElementById("imageWhatsappLogin").hidden = false;

			}
			$("#pStatusWhatsapp strong").text(response.message)
			document.getElementById("pStatusWhatsapp").hidden = false;

		}else{
			showReplyMessage(response.result, response.message, "Autenticar whatsapp", null);
		}
	})
	.catch((err)=>{
		console.log("catch", err);
		element.disabled = false;
		document.getElementById("spinnerWhatsappLogin").hidden = true;
	})

}

$("#selectClientTypeWhatsapp" ).on( "change", ()=>{
	$("#nav-profile-tab-whatsapp" ).trigger("click");
})



$("#nav-profile-tab-whatsapp" ).on( "click", ()=>{
	
	let type = $("#selectClientTypeWhatsapp").val();
	let client = $("#selectClientTypeWhatsapp option:selected").text();

	sendAsyncPost("getAllWhatsappClientByType", {type:type})
	.then((response)=>{
		$("#tbodyClientsWhatsapp").empty();
		$("#pClientsWhatsapp").text("Total clientes ("+client+"):  "+response.listResult.length);
		if ( response.result == 2 ){
			$("#pClientsWhatsapp").text("Total clientes ("+client+"):  "+response.listResult.length);

			response.listResult.map((socio)=>{

				let tel = socio.telefax || "";
				let cel = socio.telefono || "";

				$("#tbodyClientsWhatsapp").append('<tr id="trWhatsapp'+socio.idSocio+'" data-wa1="'+tel+'" data-wa2="'+cel+'" ><td><a href="./ver-socio/'+socio.idSocio+'">'+socio.nombre+'</a></td><td>'+cel+'</td><td>'+tel+'</td><td></td><td><button type="button" class="btn btn-light" onclick="enviarWhatsappIndividual(this)" >Enviar</button><div id="trWhatsappSpinner'+socio.idSocio+'" class="spinner-border" role="status" hidden><span class="sr-only">Loading...</span></div></td></tr>');
			})
		}
	})

});

var noEnviados = [];

$("#btnEnviarWhatsapp").on( "click", ()=>{
	//let id = loadPrograssBar()
	let message = $("#nav-whatsapp textarea" ).val();
	if(message && message != ""){

		$("#tbodyClientsWhatsapp tr").map((index, element)=>{

			$("#"+element.id+" td").eq(3).empty()
			$("#"+element.id+" button")[0].click()

		});
	}else{
		showReplyMessage(1, "Ingrese mensaje", "Whatsapp");
		return;
	}
	//stopPrograssBar(id)

})


function enviarWhatsappIndividual(button){

	let id = loadPrograssBar()

	let status = sendPost("whatsappVerifyStatus")
	if(status.result == 2){
		if(status.qr){
			$("#nav-whatsapp img").attr("src", "data:image/png;base64,"+status.qr);
			document.getElementById("imageWhatsappLogin").hidden = false;

		}
		$("#pStatusWhatsapp strong").text(status.message)
		document.getElementById("pStatusWhatsapp").hidden = false;
		stopPrograssBar(id)

	}else{
		stopPrograssBar(id)
		showReplyMessage(status.result, status.message, "Whatsapp");
		return false;
	}


	let message = $("#nav-whatsapp textarea" ).val();

	if(message && message != ""){

		button.disabled = true;

		let element = button.parentNode.parentNode;
		$("#"+element.id+" td").eq(3).empty()

		let phone = element.dataset.wa1;
		let response = sendPost("enviarWhatsapp", {to:phone, message:message})

		if( response.result == 2 ){
			button.disabled = false;
			$("#"+element.id+" td")[3].append("enviado");
			stopPrograssBar(id)

		}else{

			if (response.message == "No se obtuvo respuesta del servidor."){
				stopPrograssBar(id)
				showReplyMessage(1, response.message, "Whatsapp");
				button.disabled = false;
				$("#"+element.id+" td")[3].append("error");
				return false;
			}


			let phone = element.dataset.wa2;
			let responseWa2 = sendPost("enviarWhatsapp", {to:phone, message:message})
			if( responseWa2.result == 2 ){
				stopPrograssBar(id)
				button.disabled = false;
				$("#"+element.id+" td")[3].append("enviado");
				
			}else{
				stopPrograssBar(id)
				button.disabled = false;
				$("#"+element.id+" td")[3].append("error");
				
			}
		}
	}
	else{
		stopPrograssBar(id)
		showReplyMessage(1, "Ingrese mensaje", "Whatsapp");
		return false;
	}
}

$("#nav-whatsapp textarea" ).change(()=>{
	$("#tbodyClientsWhatsapp tr").map((index, element)=>{
		$("#"+element.id+" td").eq(3).empty()
	});
})


document.addEventListener('DOMContentLoaded', () => {
	const input = document.getElementById('searchWhatsappTable');
	const tableBody = document.getElementById('tbodyClientsWhatsapp');

	input.addEventListener('keyup', function () {
		const filter = input.value.toLowerCase();
		const rows = tableBody.getElementsByTagName('tr');

		for (let row of rows) {
		const text = row.textContent.toLowerCase();
		row.style.display = text.includes(filter) ? '' : 'none';
		}
	});
});
