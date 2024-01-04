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


function modalDownloadPetsData(){

	let date = new Date()
	let month = date.getMonth() + 1;
	let year = date.getFullYear();
	let day = date.getDay();
	if(day < 10)
		day = "0" + day ;
	if(month < 10)
		month = "0" + month ;

	$("#idDownloadPetDateFrom").val(year + "-"+month+"-01" )
	$("#idDownloadPetDateTo").val(year + "-"+month+"-"+day )

	$("#modalDownloadPetsData").modal("show");

}

function downloadPetHistory(idMascota){

	let from = $("#idDownloadPetDateFrom").val()
	let to = $("#idDownloadPetDateTo").val()

	sendAsyncPost("downloadHistory", {idMascota:idMascota, desde:from, hasta:to})

	.then(( response )=>{
		if (response.result === 2){
		//acá enviar nombre del doc a descargar
			window.location.href = getSiteURL() + 'downloadpdf.php?n='+response.name;
		}else{
			showReplyMessage(response.result, "No se encontraron datos a descargar.", "Descargar datos", "modalDownloadPetsData")
		}
	})

}