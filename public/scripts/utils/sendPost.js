function sendPost(nameFunction, parameters){
	var result = null;
	$.ajax({
		async: false,
		url: getSiteURL() + nameFunction,
		type: "POST",
		data: parameters,
		success: function (response) {
			response = response.trim();
			var response = jQuery.parseJSON(response);

			result =  response;
		},
		error: function (response) {
			result = "error"
		},
	});
	return result;
}

function sendAsyncPost(nameFunction, parameters){

	return new Promise( function(resolve, reject){
		$.ajax({
			async: true,
			url: getSiteURL() + nameFunction,
			type: "POST",
			data: parameters,
			success: function (response) {
				if ( response ){
					response = response.trim();
					var response = jQuery.parseJSON(response);

					resolve(response);
				}else{

					resolve(response, {result:1, message: "timeout"});
				}
			},
			error: function (response) {
				result = "error"
			},
		});
	});
}

function sendAsyncPostFiles(nombreFuncion, formData){ //fromData es un new FormData(this)
	return new Promise( function(resolve, reject){
		 $.ajax({
	        url: getSiteURL() + nombreFuncion,
	        type: 'POST',
	        data: formData,
	        success: function (response) {
	            response = response.trim();
				var response = jQuery.parseJSON(response);
				resolve(response);
	        },
	        error: function (jqXHR, textStatus, errorThrown) {
				var response = {result:0, message:errorThrown}
				resolve(response);
			},
	        cache: false,
	        contentType: false,
	        processData: false
	    });
	});
}


function sendPostFiles(nombreFuncion, formData){ //fromData es un new FormData(this)
	let result = {result:0, message:""}
	$.ajax({
		async:false,
		url: getSiteURL() + nombreFuncion,
		type: 'POST',
		data: formData,
		success: function (response) {
		    response = response.trim();
			result = jQuery.parseJSON(response);
		},
		error: function (jqXHR, textStatus, errorThrown) {
			result = {result:0, message:errorThrown}
		},
		cache: false,
		contentType: false,
		processData: false
	});

	return result;
}