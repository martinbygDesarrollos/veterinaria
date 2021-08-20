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
					showReplyMessage(response.result, response.message, "Modificar cuota", null);
					if(response.result == 2){
						$("#inputCuotaUno").val(response.quota.cuotaUno);
						$("#inputCuotaDos").val(response.quota.cuotaDos);
						$("#inputCuotaExtra").val(response.quota.cuotaExtra);
						$('#inputPlazoDeuda').val(response.quota.plazoDeuda);
					}
				}else showReplyMessage(1, "Debe ingresar el plazo para desactivar el socio para modificar los estados de los socios.", "Plazo deuda requerido", null);
			}else showReplyMessage(1, "Debe ingresar el monto extra por mascota para modificar las cuotas de los socios.", "Cuota extra requerida", null);
		}else showReplyMessage(1, "Debe ingresar el monto por dos mascotas para modificar las cuotas de los socios.", "Cuota dos requerida", null);
	}else showReplyMessage(1, "Debe ingresar el monto por una mascota para modificar las cuotas de los socios.", "Cuota uno requerida", null);
}

function selectUsuarioModificar(idUsuario, nombre, email){
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
	let usuario = $('#inputUsuario').val();
	let correo = $('#inputCorreo').val();

	if(usuario){
		if(correo){
			if(!validateEmail(correo)){
				showReplyMessage(1, "En caso de ingresar un correo, este debe ser valido.", "Correo no valido", null);
				return;
			}
		}
		let response = sendPost("crearUsuario", {usuario: usuario, correo: correo});
		showReplyMessage(response.result, response.message, "Crear usuario", null);
		if(response.result == 2)
			clearForm();
	}else showReplyMessage(1, "El usuario no puede ser modificado con el nombre vacio", "Usuario requerido", null);
}

function modificarUsuario(idUsuario){
	let usuario = $('#inputUsuario').val();
	let correo = $('#inputCorreo').val();

	if(usuario){
		if(correo){
			if(!validateEmail(correo)){
				showReplyMessage(1, "En caso de ingresar un correo, este debe ser valido.", "Correo no valido", null);
				return;
			}
		}
		let response = sendPost("modificarUsuario", {idUsuario: idUsuario, usuario: usuario, correo: correo});
		showReplyMessage(response.result, response.message, "Modificar usuario", null);
		if(response.result == 2){
			$('#' + idUsuario).replaceWith(createRow(response.user.idUsuario, response.user.nombre, response.user.email));
			clearForm();
		}
	}else showReplyMessage(1, "El usuario no puede ser modificado con el nombre de usuario vacio", "Usuario requerido", null);
}

function createRow(idUsuario, nombre, email){
	let row = "<tr id='"+ idUsuario +"'>";
	row += "<td class='text-center'>" + nombre + "</td>";
	row += "<td class='text-center col-5'><button class='btn btn-link btn-sm' onclick='selectUsuarioModificar('"+ idUsuario + "','" + nombre + "','"+ email +"')' data-toggle='tooltip' data-placement='top' title='Modificar'><i class='fas fa-edit text-dark'></i></button>";
	row += "<button class='btn btn-link btn-sm' data-toggle='tooltip' data-placement='top' title='Borrar'><i class='fas fa-trash-alt text-dark'></i></button>";
	row += "<button class='btn btn-link btn-sm' data-toggle='tooltip' data-placement='top' title='Restaurar contraseña'><i class='fas fa-eraser text-dark'></i></button></td></tr>";

	return row;
}