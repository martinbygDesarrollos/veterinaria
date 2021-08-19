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
				response = response.trim();
				var response = jQuery.parseJSON(response);

				resolve(response);
			},
			error: function (response) {
				result = "error"
			},
		});
	});
}