function findArticulo(value){

	return sendPost("searchArticuloByDescripcion", { textToSearch: value });
}

function getArticuloByDescripcion(value){
	return sendPost("getArticuloByDescripcion", { textToSearch: value });
}


function searchArticulos(value){

    if (value.length > 1){

		let response = findArticulo(value);
		if(response.result == 2){
			$('#datalistModalArticulo').empty();
			for (var i = 0; i < response.listResult.length; i++) {
				option = '<option value="' +response.listResult[i].descripcion +'"></option>';
				$("#datalistModalArticulo").append(option);
			}
		}
		else if(response.result == 1){
			$('#datalistModalArticulo').empty();
		}
	}
	else $('#datalistModalArticulo').empty();

}

function searchArticulosUpdate(value){
	
	if (value.length > 1){

		let response = findArticulo(value);
		if(response.result == 2){
			$('#datalistModalArticuloUpdate').empty();
			for (var i = 0; i < response.listResult.length; i++) {
				option = '<option value="' +response.listResult[i].descripcion +'"></option>';
				$("#datalistModalArticuloUpdate").append(option);
			}
		}
		else if(response.result == 1){
			$('#datalistModalArticuloUpdate').empty();
		}
	}
	else $('#datalistModalArticuloUpdate').empty();
}

function addArticulo(articulo){
	if (articulo.length > 0) {
		let response = getArticuloByDescripcion(articulo);
		if(response.result == 2 && response.listResult.length > 0){
			const added = articuloIsAdded("tbodyArticulos", response.listResult[0].id)
			if (!added){
				row = createRowArticuloToModal(response.listResult[0]);
				$("#tbodyArticulos").prepend(row);
				$('#datalistModalArticulo').children().remove()
				$('#idlistArticulos').val("");
			}
		}
	}

}

function addArticuloUpdate(articulo){
	if (articulo.length > 0) {
		let response = getArticuloByDescripcion(articulo);
		if(response.result == 2 && response.listResult.length > 0){
			const added = articuloIsAdded("tbodyArticulosUpdate", response.listResult[0].id)
			if (!added){
				row = createRowArticuloToModal(response.listResult[0]);
				$("#tbodyArticulosUpdate").prepend(row);
				$('#datalistModalArticuloUpdate').children().remove()
				$('#inputSearchArticuloUpdate').val("");
			}

		}
	}

}

function articuloIsAdded(tablaId, filaId){
	return document.querySelector(`#${tablaId} tr#${filaId}`) !== null;
}

function createRowArticuloToModal( object ){
	
	let row = '<tr id="'+object.id+'">'
	row += '<td>'+object.descripcion+'</td>';
	row += '<td><input name="'+object.id+'_cant" type="number" min=1 value=1 /></td>';
	row += '<td><button title="Quitar artículo de la lista" onclick="unselectArticulo('+object.id+')" class="btn btn-link"><i class="fas fa-trash-alt text-dark"></i></button></td>';
	row += '</tr>';
	return row;
}


function unselectArticulo(id){
	id.remove()
}



function matchArticulosHistoria(idHist, idMascota){

	if($("#tbodyArticulos tr").length > 0){
		const progressBar = loadPrograssBar();
		$('#progressbar h5').text("Subiendo artículos...");
		$("#progressbar").modal("show");
	
		let rows = [];
		$("#tbodyArticulos tr").map((i, e)=>{
			let cant = 0;
			if(document.getElementsByName(e.id+"_cant"))
				cant = document.getElementsByName(e.id+"_cant")[0].value
			
			rows.push({art:e.id, cant:cant})
	
		})
	
		sendAsyncPost("matchArticulosHistoria",{art:rows, idHist:idHist, idMascota:idMascota})
		.then(( response )=>{
			stopPrograssBar(progressBar);
			$('#progressbar').modal("hide");

			if (response.result == 2)
				$('#tbodyArticulos').empty();

		})
	}

}

function updateMatchArticulosHistoria(idMascota){

	let boton = document.getElementById('idButtonAddArticuloUpdate');
    let idHist = boton.dataset.hist;

	if($("#tbodyArticulosUpdate tr").length > 0){

		$('#modalArticulosUpdate').modal("hide");
		setTimeout(() => {
			$('#modalInfoArticulos').modal("hide");

			const progressBar = loadPrograssBar();
			$('#progressbar h5').text("Subiendo artículos...");
			$("#progressbar").modal("show");
		
			let rows = [];
			$("#tbodyArticulosUpdate tr").map((i, e)=>{
				let cant = 0;
				if(document.getElementsByName(e.id+"_cant"))
					cant = document.getElementsByName(e.id+"_cant")[0].value
				
				rows.push({art:e.id, cant:cant})
		
			})
		
			sendAsyncPost("matchArticulosHistoria",{art:rows, idHist:idHist, idMascota:idMascota})
			.then(( response )=>{

				setTimeout(() => {
					stopPrograssBar(progressBar);
					$('#progressbar').modal("hide");
					getArticulosByHistoria(idHist)
				}, 350);
				
			})
		}, 250);
		
	}

}


function getArticulosByHistoria(idHistoriaClinica){
	$("#tbodyInfoArticulos").empty()
	sendAsyncPost("getArticulosByHistoria",{idHist:idHistoriaClinica})
	.then(( response )=>{
		if(response.result == 2 && response.listResult.length > 0){
			for (let i = 0; i < response.listResult.length; i++) {
				row = createRowInfoArticuloToModal(response.listResult[i], idHistoriaClinica);
				$("#tbodyInfoArticulos").append(row);
			}

		}
	})

	let boton = document.getElementById('idButtonAddArticuloUpdate');
	boton.dataset.hist = idHistoriaClinica;

	$("#modalInfoArticulos").modal("show")
}


function createRowInfoArticuloToModal( object, idHistoriaClinica ){
	let fecha = new Date(object.fecha)
	fecha = fecha.toLocaleDateString("es-UY")

	let comp = "";
	let acciones = "";
	if (object.serie && object.numero){
		comp = getNameVoucher(object.tipo)+" "+object.serie+" "+object.numero
	}else if(!object.serie && !object.numero){
		//botón modificar
		acciones += '<button id="idButtonEnableUpdateArticulo" class="btn btn-link" onclick="enableUpdateArticulo('+object.id+')"><i class="fas fa-edit text-dark"></i></button><button id="idButtonUpdateArticulo" class="btn btn-success" title="Guardar cambios" onclick="updateArticulo('+object.id+')" disabled hidden><i class="fas fa-check"></i></button>'
		//botón borrar
		acciones += '<button class="btn btn-link" onclick="deleteArticulo('+object.id+','+idHistoriaClinica+')"><i class="fas fa-trash-alt text-dark"></i></button>'
	}


	if ( object.tipoPago && object.tipoPago != "" ){
		comp += " / "+object.tipoPago
	}

	let desc = ""
	if(object.descripcion)
		desc = object.descripcion

	let nombreUsuario = ""
	if (object.nombreUsuario)
		nombreUsuario = object.nombreUsuario


	let row = '<tr id="tr_'+object.id+'" >'
	row += '<td>'+nombreUsuario+'</td>';
	row += '<td>'+fecha+'</td>';
	row += '<td id="td_'+object.id+'_cantnueva" disabled hidden ><input name="'+object.id+'_cant" type="number" min=1 value="'+object.cantidad+'"/>';
	row += '<td id="td_'+object.id+'_cant">'+object.cantidad+'</td>';
	row += '<td>'+desc+'</td>';
	row += '<td>'+comp+'</td>';
	row += '<td>'+acciones+'</td></tr>';

	return row;
}


function enableUpdateArticulo(id){
	console.log("modificar", id)

	document.getElementById("td_"+id+"_cant").disabled = true //td que muestra la cantidad ingresada
	document.getElementById("td_"+id+"_cant").hidden = true
	document.getElementById("idButtonEnableUpdateArticulo").disabled = true //boton que permite editar
	document.getElementById("idButtonEnableUpdateArticulo").hidden = true

	document.getElementById("td_"+id+"_cantnueva").disabled = false //td input que tiene la cantidad nueva
	document.getElementById("td_"+id+"_cantnueva").hidden = false
	document.getElementById("idButtonUpdateArticulo").disabled = false//boton que actualiza la cantidad
	document.getElementById("idButtonUpdateArticulo").hidden = false
}

function desableUpdateArticulo(id){

	document.getElementById("td_"+id+"_cantnueva").disabled = true //td input que tiene la cantidad nueva
	document.getElementById("td_"+id+"_cantnueva").hidden = true
	document.getElementById("idButtonUpdateArticulo").disabled = true//boton que actualiza la cantidad
	document.getElementById("idButtonUpdateArticulo").hidden = true

	document.getElementById("td_"+id+"_cant").disabled = false //td que muestra la cantidad ingresada
	document.getElementById("td_"+id+"_cant").hidden = false
	document.getElementById("idButtonEnableUpdateArticulo").disabled = false //boton que permite editar
	document.getElementById("idButtonEnableUpdateArticulo").hidden = false

}


function updateArticulo(id){
	console.log("modificar", id)

	//deshabilitar boton y deshabilitar input, guardar nuevo valor y al terminar dejar todo desbloqueado
	document.getElementById("idButtonUpdateArticulo").disabled = true //boton que actualiza la cantidad
	const cant_nueva = document.getElementById("td_"+id+"_cantnueva").children[0].value;
	document.getElementById("td_"+id+"_cantnueva").disabled = true //td input que tiene la cantidad nueva 

	sendAsyncPost("setHistoriaArticulo",{idHistArt:id, campo:"cantidad", valor:cant_nueva})
	.then((response)=>{
		console.log(response)

		if(response.result == 2){
			const cant_nueva = document.getElementById("td_"+id+"_cantnueva").children[0].value;
			document.getElementById("td_"+id+"_cant").innerHTML = cant_nueva;
			document.getElementById("td_"+id+"_cant").innerText = cant_nueva;

			desableUpdateArticulo(id)
		}else{
			let cant_anterior = response.anterior;
			document.getElementById("td_"+id+"_cant").innerHTML = cant_anterior;
			document.getElementById("td_"+id+"_cant").innerText = cant_anterior;

			desableUpdateArticulo(id)
		}


	})

}


function deleteArticulo(id, idHistoriaClinica){
	console.log("borrar", id)
	showMessageConfirm(0, "Eliminar el artículo?", "Eliminar artículo", "modalInfoArticulos")
	$('#modalMessageConfirmBtnSi').off('click');
	$('#modalMessageConfirmBtnSi').click(function(){
		$('#modalMessageConfirm').modal('hide');
		sendAsyncPost("setHistoriaArticulo",{idHistArt:id, campo:"eliminado", valor:1})
		.then((response)=>{
			if(response.result == 2)
				getArticulosByHistoria(idHistoriaClinica)
			else
				showReplyMessage(response.result, "Error al eliminar el artículo", "Eliminar artículo", "modalInfoArticulos");
		})
	});
}
