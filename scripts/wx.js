// JavaScript Document
// ###############################################
// #
// # 			Custom Javascript for wx 
// #
// ###############################################
//
// Load default content function
//
$(document).ready(function() {
	$('#content').load('resources/wxCurrent.php');					// Default page to load
	// Navigation content load
	$('#navigation a').click(function() {
		var page = $(this).attr('href'),							// Set variable and match to attribute
			loader = $('<div />', { class: 'loader' }); 
		$('#content').append(loader);
		$('#content').load('resources/' + page + '.php');			// Load the page that has been requested
		return false;												// Prevent default action
	});
	$('#credit a').click(function() {
		var page = $(this).attr('href');							// Set variable and match to attribute
		$('#content').load('resources/' + page + '.php');			// Load the page that has been requested		
		return false;												// Prevent default action
	});
});

//
// Current Page Functions
//
function currentFunctions() {
	$(document).ready(function() {
		//alert('currentfunctions ready to load weather.php');
		var loader = $('<div />', { class: 'loader' });
		$('#weather').append(loader);
		$('#weather').load('resources/functions/weather.php');
	});
	
	//alert('currentFunctions Fired');
	// Standard Link Handler
	$('#content a').click(function() {
		//alert('Click Event Fired');
		var page = $(this).attr('href');							// Set variable and match to attribute
		$('#content').load('resources/' + page + '.php');			// Load the page that has been requested
		return false;												// Prevent default action
	});
}
//
function weatherFunctions() {
	//alert('currentFunctions Fired');
	// Standard Link Handler
	$('#alerts a').click(function() {
		//alert('Click Event Fired');
		var page = $(this).attr('href');							// Set variable and match to attribute
		$('#content').load('resources/' + page + '.php');			// Load the page that has been requested
		return false;												// Prevent default action
	});
}

//
// Imagery Page Functions
//
function imageryFunctions() {
	//alert('imagery Fired');
	// Standard Link Handler
	$('#imageryControls a').click(function() {
		//alert(' Imagery Click Event Fired');
		var res = {
			loader: $('<div />', { class: 'loader' }),				// For a div of class loader
			container: $('#content')								// within container with class
		}
		var that = $(this),
			image = that.attr('href'),
			url = 'resources/wxImagery.php',
			method = 'POST',
			data = {
				image: image
		};
		$.ajax({
			url: url,
			type: method,
			data: data,
			beforeSend: function() {
				//alert('ajax before send');
				res.container.append(res.loader);					// Append the loader to the container
			},
			success: function(response) {							// When the request has been successful
				//alert('ajax success');
				res.container.html(response);						// Append the data from the requested page to the container
				res.container.find(res.loader).remove();			// remove the loader
			}
		});
		return false;												// Prevent default action
	});
}
//
// Info Page Functions
//
function infoFunctions() {
	//alert('currentFunctions Fired');
	// Standard Link Handler
	$('#content a').click(function() {
		//alert('Click Event Fired');
		var page = $(this).attr('href');							// Set variable and match to attribute
		$('#content').load('resources/' + page + '.php');			// Load the page that has been requested
		return false;												// Prevent default action
	});
}
//
// Authorisation functions
//
function authFunctions() {
	//alert('authFunctions Fired');
	//
	// Standard Link Handler
	$('#content a').click(function() {
		//alert('Click Event Fired');
		var page = $(this).attr('href');							// Set variable and match to attribute
		$('#content').load('resources/' + page + '.php');			// Load the page that has been requested
		return false;												// Prevent default action
	});
	//
	// Auth Form
	$('form.auth').on('submit', function() {						// on the submission of the Authentication form
		var that = $(this),
			url = that.attr('action'),								// Get the url submitted on the form
			method = that.attr('method'),							// Get the method specified on the form
			data = {};												// set the data element as javascript object
		that.find('[name]').each(function(index, value) {
			var that = $(this),
				name = that.attr('name');
				value = that.val();
			data[name] = value;
		});	
		$.ajax({													// Make an ajax call
			url: url,												// to the php page specified in the form
			type: method,											// using the method specified on the form
			data: data,												// Sending the data transformed above
			success: function() {									// When the request has been successful
				$('#content').load(url);
			}
		});
		return false;
	});
}
//
// Admin Controls Page Specific Functions
//
function wxAdminFunctions() {
	//alert('wxAdminFunctions Fired');
	//
	// Standard Link Handler
	$('#content a').click(function() {
		//alert('Click Event Fired');
		var page = $(this).attr('href');							// Set variable and match to attribute
		$('#content').load('resources/' + page + '.php');			// Load the page that has been requested
		return false;												// Prevent default action
	});
	//
	// Ajax Handler
	var res = {
	loader: $('<div />', { class: 'loader' }),						// For a div of class loader
	container: $('#adminResponse')									// within container with class
	}
	$('form.ajax').on('change', function() {						// on the submission of an Ajax class form
		//alert('ajax FormHandler Fired');
		var that = $(this),
			url = that.attr('action'),								// Get the url submitted on the form
			method = that.attr('method'),							// Get the method specified on the form
			data = {};												// set the data element as javascript object
		that.find('[name]').each(function(index, value) {
			var that = $(this),
				name = that.attr('name');
				if(that.attr('checked')) {							// Handle Check box objects as they have n value
					var value = true;
				} else {
					var value = that.val();
				}
			data[name] = value;
		});
		$.ajax({													// Make an ajax call
			url: url,												// to the php page specified in the form
			type: method,											// using the method specified on the form
			data: data,												// Sending the data transformed above
			beforeSend: function() {								// Before sending the request
				res.container.append(res.loader);					// Append the loader to the container
			},
			success: function(response) {							// When the request has been successful
				res.container.html(response);						// Append the data from the requested page to the container
				res.container.find(res.loader).remove();			// remove the loader
			}
		});
		return false;
	});
	// Load messages as defaul
	res.container.load('resources/functions/admin.php');
}
//
// Admin page Functions
//
function adminFunctions() {
	//alert('adminFunctions Fired');
	var res = {
		loader: $('<div />', { class: 'loader' }),					// For a div of class loader
		container: $('#adminResponse')								// within container with class
	}
	//
	$('#pagination a').click(function() {
		//alert('pagination click');
		var that = $(this),
			pagenum = that.attr('data-page'),
			request = that.attr('data-request'),
			table = that.attr('data-table'),
			filter = that.attr('data-filter'),
			url = 'resources/functions/' + that.attr('href'),
			method = 'POST',
			post = 'page=' + pagenum,
			data = {
				page: pagenum,
				request: request,
				table: table,
				filter: filter
				};
		$.ajax({
			url: url,
			type: method,
			data: data,
			beforeSend: function() {
				//alert('ajax before send');
				res.container.append(res.loader);					// Append the loader to the container
			},
			success: function(response) {							// When the request has been successful
				//alert('ajax success');
				res.container.html(response);						// Append the data from the requested page to the container
				res.container.find(res.loader).remove();			// remove the loader
			}
		});
	return false;													// Prevent default action
	});	
	// Standard form submit
	$('form.ajax').on('submit', function() {						// on the submission of an Ajax class form
		//alert('ajax FormHandler Fired');
		var that = $(this),
			url = that.attr('action'),								// Get the url submitted on the form
			method = that.attr('method'),							// Get the method specified on the form
			data = {};												// set the data element as javascript object
		that.find('[name]').each(function(index, value) {
			var that = $(this),
				name = that.attr('name'),
				value = that.val();
			data[name] = value;
		});
		$.ajax({													// Make an ajax call
			url: url,												// to the php page specified in the form
			type: method,											// using the method specified on the form
			data: data,												// Sending the data transformed above
			beforeSend: function() {								// Before sending the request
				res.container.append(res.loader);					// Append the loader to the container
			},
			success: function(response) {							// When the request has been successful
				res.container.html(response);						// Append the data from the requested page to the container
				res.container.find(res.loader).remove();			// remove the loader
			}
		});
		return false;
	});
}
//
// Analysis page Functions
//
function analysisFunctions() {
	// Start the loader
	//$('#content').append($('<div />', { class: 'loader' }));
	//alert('analysisFunctions Fired');
	var res = {
	loader: $('<div />', { class: 'loader' }),						// For a div of class loader
	container: $('#analysisResults')								// within container with class
	}
	$('form.ajax').on('submit', function() {						// on the submission of an Ajax class form
		//alert('ajax FormHandler Fired');
		var that = $(this),
		url = that.attr('action'),									// Get the url submitted on the form
		method = that.attr('method'),								// Get the method specified on the form
		data = {};													// set the data element as javascript object
		that.find('[name]').each(function(index, value) {
			var that = $(this),
				name = that.attr('name'),
				value = that.val();
			data[name] = value;
		});
		$.ajax({
			url: url,
			type: method,
			data: data,
			beforeSend: function() {
				res.container.append(res.loader);					// Append the loader to the container
			},
			success: function(response) {							// When the request has been successful
			//alert(response);
			res.container.html(response);						// Append the data from the requested page to the container
			res.container.find(res.loader).remove();			// remove the loader
			}
		});
		return false;
	});
}
