(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function() {

		console.log(LbrtyApiSettings.post_id);

		$(".lbrty-activate-button").on("click", function(e){
			e.preventDefault();
			$(".lbrty-card-activate").removeClass("closed");
		});


		$(".lbrty-view-key-button").on("click", function(e){
			e.preventDefault();
			$(".lbrty-license-box").toggleClass("open");
		});

		$('.lbrty-color-picker').wpColorPicker();

	});

})( jQuery );


function activateKey(e){
	e.preventDefault();

	jQuery.ajax({
		url: LbrtyApiSettings.root + 'libreli/v1/key/activatekey',
		type: 'POST',
		contentType: 'application/json',
		beforeSend: function (xhr) {
			xhr.setRequestHeader('X-WP-Nonce', LbrtyApiSettings.lbrty_nonce);

			console.log("...");

			jQuery('.lbrty_script_button').prop('disabled', true);
			jQuery('.lbrty_script_button').addClass('disabled');
			jQuery('.lbrty_activate_button_msg, .lbrty_activate_button_loader').removeClass('lbrty_hide');
			jQuery('.lbrty_activate_button_msg').html('Script Running Please Wait...');

			console.log(jQuery('[name="lbrty_settings_general_options[lbrty_license_key]"]').val());

		},
		data: JSON.stringify( {
			'license_key': jQuery('[name="lbrty_settings_general_options[lbrty_license_key]"]').val()
		})
		,
		success: function (response) {
			jQuery('.lbrty_script_button').prop('disabled', false);

		}

	}).done(function (results) {

		console.log('SUCCESS');
		console.log(results);

		// jQuery('#lbrty__activate-key').type = "password";
		location.reload(true);

		// jQuery('#lbrty__activate-key').prop('disabled', false);
		// jQuery('.lbrty_activate_button_loader').addClass('lbrty_hide');


		if(results !== null){
			jQuery('.lbrty_activate_button_msg').html(results.message);

			if(results.code == 'activated'){
				jQuery('.lbrty-license-box').addClass('lbrty-license-box--just-activated')
			}

		}else{
			jQuery('.lbrty_activate_button_msg').html("Error: results is null. Please contact support.");
		}

			jQuery('#lbrty__activate-key').addClass('lbrty_hide');

	}).fail(function (jqXHR, textStatus, errorThrown) {

		console.log('ERROR');
		console.log(jqXHR);
		console.log(textStatus);
		console.log('Script Failed. Error: ' + errorThrown);

		jQuery('#lbrty__activate-key').prop('disabled', false);
		jQuery('.lbrty_script_button').removeClass('disabled');

		jQuery('.lbrty_activate_button_loader').addClass('lbrty_hide');
		jQuery('.lbrty_activate_button_msg').html('ERROR: ' + jqXHR.responseJSON.message);

	});
}



function deactivateKey(e){
	e.preventDefault();

	jQuery.ajax({
		url: LbrtyApiSettings.root + 'libreli/v1/key/deactivatekey',
		type: 'POST',
		contentType: 'application/json',
		beforeSend: function (xhr) {
			xhr.setRequestHeader('X-WP-Nonce', LbrtyApiSettings.lbrty_nonce);

			console.log("...");

			jQuery('.lbrty_script_button').prop('disabled', true);
			jQuery('.lbrty_deactivate_button_msg, .lbrty_deactivate_button_loader').removeClass('lbrty_hide');
			jQuery('.lbrty_deactivate_button_msg').html('Script Running Please Wait...');

			console.log(jQuery('[name="lbrty_settings_general_options[lbrty_license_key]"]').val());

		},
		data: JSON.stringify( {
			'license_key': jQuery('[name="lbrty_settings_general_options[lbrty_license_key]"]').val()
		})
		,
		success: function (response) {
			jQuery('.lbrty_script_button').prop('disabled', false);

		}

	}).done(function (results) {

		console.log('SUCCESS');
		console.log(results);

		jQuery('[name="lbrty_settings_general_options[lbrty_license_key]"]').val("");
		location.reload(true);

		if(results !== null){
			jQuery('.lbrty_deactivate_button_msg').html(results.message);
		}else{
			jQuery('.lbrty_deactivate_button_msg').html("Error: results is null. Please contact support.");
		}

		// if (results.code == 'deactivated'){
			jQuery('#lbrty__deactivate-key').addClass('lbrty_hide');
		// }

	}).fail(function (jqXHR, textStatus, errorThrown) {

		console.log('ERROR');
		console.log(jqXHR);
		console.log(textStatus);
		console.log('Script Failed. Error: ' + errorThrown);

		jQuery('#lbrty__deactivate-key').prop('disabled', false);
		jQuery('.lbrty_deactivate_button_loader').addClass('lbrty_hide');
		jQuery('.lbrty_deactivate_button_msg').html('ERROR: ' + jqXHR.responseJSON.message);

	});
}


function perform_initial_lookup(e){
	e.preventDefault();

	// disable button
	jQuery('#lbrty_run_initial_book_lookup').addClass('disabled');

	let isbn = jQuery('[name="lbrty_isbn"]').val();
	let isbn13 = jQuery('[name="lbrty_isbn13"]').val();

	isbn = isbn.split(" ").join("").split("-").join("");
	isbn13 = isbn13.split(" ").join("").split("-").join("");

	if (isNaN(isbn) || isNaN(isbn13)){

		jQuery('.lbrty_initial_lookup_msg').removeClass('lbrty_hide');
		jQuery('.lbrty_initial_lookup_msg').html('ERROR: ' + "Please provide valid ISBN");

		return false;
	}

	jQuery.ajax({
		url: LbrtyApiSettings.root + 'libreli/v1/lookup/initial-lookup',
		type: 'POST',
		contentType: 'application/json',
		beforeSend: function (xhr) {
			xhr.setRequestHeader('X-WP-Nonce', LbrtyApiSettings.lbrty_nonce);


			jQuery('.lbrty_initial_lookup_msg').addClass('lbrty_hide');
			jQuery('#lbrty_run_initial_book_lookup').prop('disabled', true);
			jQuery('.lbrty_initial_lookup_progress').removeClass('lbrty_hide');


		},
		data: JSON.stringify( {
			'isbn': jQuery('[name="lbrty_isbn"]').val(),
			'isbn13': jQuery('[name="lbrty_isbn13"]').val(),
			'post_id': LbrtyApiSettings.post_id,
			'lbrty_amzn_aff': LbrtyApiSettings.lbrty_amzn_aff
		})
		,
		success: function (response) {

		}

	}).done(function (results) {

		console.log("success");
		console.log(results);

		jQuery('.lbrty_initial_lookup_progress').addClass('lbrty_hide');
		jQuery('.lbrty_initial_lookup_msg').removeClass('lbrty_hide');


		if(results){
			if(results.message != 'null' ){
				jQuery('.lbrty_initial_lookup_msg').html(results.message);
			}else{
				jQuery('.lbrty_initial_lookup_msg').html('Empty Message Returned.');
			}

		}

		// jQuery('.lbrty_initial_lookup_progress').delay( 10000 ).queue(function(next){
		// 	jQuery(this).addClass('lbrty_hide');
		// 	next();
		// });

		// var i = 0;var intervalId = setInterval(function(){
		// 	if(i === 10){
		// 		clearInterval(intervalId);
		// 	}
		// 		// console.log(i);
		// 		i++;
	  	// }, 1000);


	}).fail(function (jqXHR, textStatus, errorThrown) {

		console.log('ERROR');
		console.log(jqXHR);
		console.log(textStatus);
		console.log('Script Failed. Error: ' + errorThrown);


		jQuery('.lbrty_initial_lookup_progress').addClass('lbrty_hide');
		jQuery('.lbrty_initial_lookup_msg').removeClass('lbrty_hide');
		jQuery('.lbrty_initial_lookup_msg').html('ERROR: ' + jqXHR.responseJSON.message);

	});
}