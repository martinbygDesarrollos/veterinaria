$("#buttonConfirmModalArticulo").click(function(){

	document.getElementById("buttonConfirmModalArticulo").hidden = true
	document.getElementById("buttonConfirmModalArticulo").disabled = true
	document.getElementById("buttonConfirmModalGuardadoArticulo").hidden = false


	let cookie = "";

	$("#tbodyArticulos tr").map((i, e)=>{
		if (i == 0)
			cookie = e.id
		else
			cookie += ","+e.id

	})

	document.cookie = "articulos="+cookie;


	setTimeout(() => {
		console.log("boton")
		document.getElementById("buttonConfirmModalGuardadoArticulo").hidden = true
		document.getElementById("buttonConfirmModalArticulo").disabled = false
		document.getElementById("buttonConfirmModalArticulo").hidden = false
	  }, 300);
	

})


function searchArticulos(value){

    if (value.length > 1){

		let response = sendPost("searchArticuloByDescOrCodeBar", { textToSearch: value });
		console.log(response);
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

function addArticulo(articulo){
	if (articulo.length > 0) {
		let response = sendPost("searchArticuloByDescOrCodeBar", { textToSearch: articulo });
		console.log(response);
		if(response.result == 2 && response.listResult.length > 0){
			row = createRowArticuloToModal(response.listResult[0]);
			$("#tbodyArticulos").prepend(row);
			$('#datalistModalArticulo').children().remove()
			$('#idlistArticulos').val("");

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
			console.log(response)
			stopPrograssBar(progressBar);
			$('#progressbar').modal("hide");
		})
	}

}


function getArticulosByHistoria(idHistoriaClinica){
	$("#tbodyInfoArticulos").empty()
	sendAsyncPost("getArticulosByHistoria",{idHist:idHistoriaClinica})
	.then(( response )=>{
		console.log(response)
		if(response.result == 2 && response.listResult.length > 0){
			for (let i = 0; i < response.listResult.length; i++) {
				row = createRowInfoArticuloToModal(response.listResult[i]);
				$("#tbodyInfoArticulos").append(row);
			}

			$("#modalInfoArticulos").modal("show")
		}
	})
}


function createRowInfoArticuloToModal( object ){
	let fecha = new Date(object.fecha)
	fecha = fecha.toLocaleDateString("es-UY")

	let comp = "";
	if (object.serie && object.numero)
		comp = getNameVoucher(object.tipo)+" "+object.serie+" "+object.numero

	let desc = ""
	if(object.descripcion)
		desc = object.descripcion

	let row = '<tr>'
	row += '<td>'+fecha+'</td>';
	row += '<td>'+object.cantidad+'</td>';
	row += '<td>'+desc+'</td>';
	row += '<td>'+comp+'</td></tr>';

	return row;
}
