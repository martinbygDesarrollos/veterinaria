console.log("imprimibles")

function downloadPdf(dateId, category){

	let date = $("#"+dateId).val()
	date = date.replaceAll("-","")

	sendAsyncPost("getCalendarDocument", {date:date, category: category})
	.then(( response )=>{ download(response) })

}


function downloadInternacion(){

	sendAsyncPost("getInternacionDocument")
	.then(( response )=>{ download(response) })

}

function download(response){

	if (response.result === 2){
	//acá enviar nombre del doc a descargar
		window.location.href = getSiteURL() + 'downloadpdf.php?n='+response.name;
	}else{
		showReplyMessage(response.result, "No se encontraron datos a descargar.", "Descargar datos", null)
	}

}