function findArticulo(value){

	return sendPost("searchArticuloByDescOrCodeBar", { textToSearch: value });
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
		let response = findArticulo(articulo);
		if(response.result == 2 && response.listResult.length > 0){
			row = createRowArticuloToModal(response.listResult[0]);
			$("#tbodyArticulos").prepend(row);
			$('#datalistModalArticulo').children().remove()
			$('#idlistArticulos').val("");

		}
	}

}

function addArticuloUpdate(articulo){
	if (articulo.length > 0) {
		let response = findArticulo(articulo);
		if(response.result == 2 && response.listResult.length > 0){
			row = createRowArticuloToModal(response.listResult[0]);
			console.log(row)
			$("#tbodyArticulosUpdate").prepend(row);
			$('#datalistModalArticuloUpdate').children().remove()
			$('#inputSearchArticuloUpdate').val("");

		}
	}

}

function createRowArticuloToModal( object ){
	
	let row = '<tr id="'+object.id+'">'
	row += '<td>'+object.descripcion+'</td>';
	row += '<td><input name="'+object.id+'_cant" type="number" min=1 value=1 /></td>';
	row += '<td><button title="Quitar artículo de la lista" onclick="unselectArticulo('+object.id+')" class="btn bg-light"><i class="fas fa-trash-alt text-danger"></i></button></td>';
	row += '</tr>';
	return row;
}


function unselectArticulo(id){
	id.remove()
}



function matchArticulosHistoria(idHist, idMascota){

	if($("#tbodyArticulos tr").length > 0){
		const progressBar = loadPrograssBar();
		$('#progressbar h5').text("Subiendo archivos...");
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
				row = createRowInfoArticuloToModal(response.listResult[i]);
				$("#tbodyInfoArticulos").append(row);
			}

		}
	})

	let boton = document.getElementById('idButtonAddArticuloUpdate');
	boton.dataset.hist = idHistoriaClinica;

	$("#modalInfoArticulos").modal("show")
}


function createRowInfoArticuloToModal( object ){
	let fecha = new Date(object.fecha)
	fecha = fecha.toLocaleDateString("es-UY")

	let comp = "";
	let acciones = "";
	if (object.serie && object.numero){
		comp = getNameVoucher(object.tipo)+" "+object.serie+" "+object.numero
	}else if(!object.serie && !object.numero){
		//botón modificar
		acciones += '<button class="btn btn-link" onclick="updateArticulo('+object.id+')"><i class="fas fa-edit text-dark"></i></button>'
		//botón borrar
		//acciones += '<button class="btn btn-link" onclick="deleteArticulo('+object.id+')"><i class="fas fa-trash-alt text-dark"></i></button>'
	}


	if ( object.tipoPago && object.tipoPago != "" ){
		comp += " / "+object.tipoPago
	}

	let desc = ""
	if(object.descripcion)
		desc = object.descripcion

	let row = '<tr id="tr_'+object.id+'" >'
	row += '<td>'+fecha+'</td>';
	row += '<td id="td_'+object.id+'_cantnueva" disabled hidden ><input name="'+object.id+'_cant" type="number" min=1 value="'+object.cantidad+'"/>';
	row += '<td id="td_'+object.id+'_cant">'+object.cantidad+'</td>';
	row += '<td>'+desc+'</td>';
	row += '<td>'+comp+'</td>';
	row += '<td>'+acciones+'</td></tr>';

	return row;
}


function updateArticulo(id){
	console.log("modificar", id)

	document.getElementById("td_"+id+"_cant").disabled = true
	document.getElementById("td_"+id+"_cant").hidden = true

	document.getElementById("td_"+id+"_cantnueva").disabled = false
	document.getElementById("td_"+id+"_cantnueva").hidden = false
}


function deleteArticulo(id){
	console.log("borrar", id)
}