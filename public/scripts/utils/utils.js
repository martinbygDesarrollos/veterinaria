function validateCI(ci){
	ci = ci.replace(/\D/g, '');

	var dig = ci[ci.length - 1];
	ci = ci.replace(/[0-9]$/, '');
	return (dig == validation_digit(ci));
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