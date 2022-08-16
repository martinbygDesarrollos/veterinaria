function validateCI(ci){
	if ( ci ){
		ci = ci.replace(/\D/g, '');

		var dig = ci[ci.length - 1];
		ci = ci.replace(/[0-9]$/, '');
		return (dig == validation_digit(ci));
	} return false;
}

function validation_digit(ci){
	var a = 0;
	var i = 0;
	if(ci.length <= 6){
		for(i = ci.length; i < 7; i++){
			ci = '0' + ci;
		}
	}
	for(i = 0; i < 7; i++){
		a += (parseInt("2987634"[i]) * parseInt(ci[i])) % 10;
	}
	if(a%10 === 0){
		return 0;
	}else{
		return 10 - a % 10;
	}
}

function validateEmail(email) {
	const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(String(email).toLowerCase());
}

function getSiteURL(){
	let url = window.location.href;
	if(url.includes("localhost") || url.includes("intranet.gargano.com.uy") )
		return '/veterinarianan/public/';
	else
		return '/';
}

function getDateForInput(){
	let date = new Date()

	let day = date.getDate()
	let month = date.getMonth() + 1
	let year = date.getFullYear()

	if(month < 10){
		month = "0" + month ;
	}

	if(day < 10){
		day = "0" + day;
	}

	return `${year}-${month}-${day}`;
}

function getDateForShow(){
	let date = new Date()

	let day = date.getDate()
	let month = date.getMonth() + 1
	let year = date.getFullYear()

	if(month < 10){
		month = "0" + month ;
	}

	if(day < 10){
		day = "0" + day;
	}

	return `${day}/${month}/${year}`;
}

function downloadFile( id ){

	let url = getSiteURL() + 'descargar/' + id;
  	window.location.href = url;
}

function getCurrentDate(){
	var today = new Date();
	var date = null;
	var day = null;
	var month = null;
	var year = null;

	day = today.getDate();
	month = today.getMonth()+1;
	year = today.getFullYear();

	if( day.toString().length == 1 ){
		day = '0'+today.getDate();
	}

	if( month.toString().length == 1 ) {
		month = '0'+(today.getMonth()+1)
	}

	date = year+'-'+month+'-'+day;
	return date;
}

function getLastDayMonth(currentDate){
	let date = new Date(currentDate + 'T00:00');
	date = new Date(date.getFullYear(), date.getMonth()+1, 0);

	let day = date.getDate();
	let month = date.getMonth() + 1;
	let year = date.getFullYear();

	if(month < 10) month = "0" + month ;
	if(day < 10) day = "0" + day;
	//return `${year}-${month}-${day}`;
	date = year+'-'+month+'-'+day;
	return date;
}

function getTimestamp(){
	var today = new Date();
	var date = null;
	var day = null;
	var month = null;
	var year = null;
	var hour = null;
	var minute = null;
	var second = null;

	day = today.getDate();
	month = today.getMonth()+1;
	year = today.getFullYear();
	hour = today.getHours();
	minute = today.getMinutes();
	second = today.getSeconds();

	if( day.toString().length == 1 ){
		day = '0'+today.getDate();
	}

	if( month.toString().length == 1 ) {
		month = '0'+(today.getMonth()+1)
	}

	if( hour.toString().length == 1 ){
		hour = '0'+today.getHours();
	}

	if( minute.toString().length == 1 ){
		minute = '0'+today.getMinutes();
	}

	if( second.toString().length == 1 ){
		second = '0'+today.getSeconds();
	}

	date = String(year) + String(month) + String(day) + String(hour) + String(minute) + String(second);
	return date;
}

function redirectToWhatsapp( phone, message ){

	console.log("utils function redirectToWhatsapp", phone, message);
	if ( phone ){
		window.open("https://wa.me/"+phone+"?text="+message, '_blank');
	}else
		window.open("https://wa.me/");


	//enviar whatsapp por celu
	//whatsapp://send?phone=59892459188
}

function calculateColorRowByClient(tipo, deudor){

	classForClient = "";
	tipoCliente = "";

	if ( tipo == 0 ){ //NO SOCIO
		tipoCliente = "<br>(Cliente)";
		classForClient = "rowNosocio";
	}else if ( tipo == 1 ){ //SOCIO
		tipoCliente = "<br>(Socio)";
		if ( deudor )
			classForClient = "rowWarning";
	}else if ( tipo == 3 ){ //EX SOCIO
		tipoCliente = "<br>(Ex socio)";
		if ( deudor )
			classForClient = "rowExsocioWarning";
		else
			classForClient = "rowExsocio";
	}else if ( tipo == 2 ) //ONG
		tipoCliente = "<br>(ONG)";

	return {tipo: tipoCliente, class:classForClient};
}