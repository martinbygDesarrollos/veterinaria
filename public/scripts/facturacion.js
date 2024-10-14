console.log("facturacion, modal detalle del saldo")

function openModalSaldo(idCliente){
	console.log("abrir modal cliente ", idCliente);
	loadListSaldo(idCliente);
	$("#modalSaldo").modal("show")
}

function closeModalSaldo(){
	console.log("cerrar modal");

	$("#modalSaldo").modal("hide")
}

function loadListSaldo(idClient){

	sendAsyncPost("getFacturasPendientesCliente", {idClient:idClient})
	.then((response)=>{
		if (response.result == 2){
			$("#tbodySaldo").empty()
			for (let i = 0; i < response.listResult.length; i++) {
				let factura = response.listResult[i]
				let row = createRowInfoSaldo(factura);
				$("#tbodySaldo").append(row);
			}
		}
	})

}


function createRowInfoSaldo(obj){
	console.log(obj)

	const year = parseInt(obj.fecha.substring(0, 4));  // 2024
	const month = parseInt(obj.fecha.substring(4, 6)) - 1; // 09 (meses en JS van de 0 a 11, por eso restamos 1)
	const day = parseInt(obj.fecha.substring(6, 8));   // 17

	let serie = "";
	if (obj.serie)
		serie = obj.serie

	let numero = "";
	if (obj.numero)
		numero = obj.numero

	let comp = "";
	if ((obj.serie && obj.numero) || obj.tipo == "999"){
		comp = getNameVoucher(obj.tipo)+" "+serie+" "+numero
	}

	let row = '<tr id="'+obj.id+'">'
	row += '<td>'+day+'/'+month+'/'+year+'</td>';
	row += '<td>'+comp+'</td>';
	row += '<td>$'+obj.importe+'</td>';
	row += '<td>$'+obj.saldo+'</td>';
	row += '</tr>';
	return row;

}