console.log("imprimibles")

function downloadDomicilios(){

	let date = $("#idInputTodayDomi").val()
	date = date.replaceAll("-","")

	sendAsyncPost("getDomiciliosDocument", {date:date, category: "domicilios"})
	.then(( response )=>{
		console.log(response);
		if (response.result === 2){
		//acá enviar nombre del doc a descargar
			window.location.href = getSiteURL() + 'downloadpdf.php?n='+response.name;
		}else{
			showReplyMessage(response.result, "No se encontraron domicilios a descargar.", "Descargar datos", null)
		}
	})


}




function downloadCirugias(){

	let date = $("#idInputTodayCalendar").val()
	date = date.replaceAll("-","")

	sendAsyncPost("getCirugiasDocument", {date:date, category: "cirugia"})
	.then(( response )=>{
		console.log(response);
		if (response.result === 2){
		//acá enviar nombre del doc a descargar
			window.location.href = getSiteURL() + 'downloadpdf.php?n='+response.name;
		}else{
			showReplyMessage(response.result, "No se encontraron domicilios a descargar.", "Descargar datos", null)
		}
	})


}