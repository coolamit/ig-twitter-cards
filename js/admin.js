/**
 * JS for admin UI of iG:Twitter Cards plugin
 *
 * @author: Amit Gupta
 * @since: 2013-02-18
 * @version: 2013-04-06
 */


jQuery(document).ready(function($) {

	var ig_tc_admin = {
		hide_jq_msg: function() {
			$.msg( 'unblock' );
		},
		get_aspect_ratio: function() {
			var aspect_ratio = $('#ig_tc_player_aspect_ratio').val();

			console.log( 'aspect_ratio = ' + aspect_ratio );

			if( ! aspect_ratio || aspect_ratio === null || aspect_ratio.indexOf( ':' ) <= 0 ) {
				return false;
			}

			return aspect_ratio.split( ':' );
		}
	}

	var loading_img = $("<img />").attr('src', ig_tc.plugins_url + '/images/ajax-loader.gif');	//pre-load ajax animation, just-in-case

	ig_tc.form_handler = function(){
		$.msg({
			msgID : 1,
			bgPath : ig_tc.plugins_url + '/images/',
			autoUnblock : false,
			clickUnblock : false,
			klass : 'black-on-white',
			content : 'Saving Options &nbsp;&nbsp; <img src="' + ig_tc.plugins_url + '/images/ajax-loader.gif" id="loading-img" />'
		});
		$.post(
			ajaxurl,
			{
				action: 'ig-tc-save-opts',
				_ig_tc_nonce: ig_tc.nonce,
				options: $('form#ig-tc-admin-form').serialize()
			},
			function(data) {
				setTimeout( ig_tc_admin.hide_jq_msg, 2000 );
				if( ! data || ! data.nonce || ! data.msg ) {
					$.msg( 'replace', '<span class="ig-tc-error">Unable to save options</span>' );
				} else {
					ig_tc.nonce = data.nonce;
					$.msg( 'replace', data.msg );

					//if field ID is set then set focus on it
					if( data.field ) {
						$( '#' + data.field ).focus();
					}

					//if option values have been sent back then set them up
					if( data.options ) {
						$.each( data.options, function( index, value ){
							$('#'+index).val( value );
						} );
					}
				}
			},
			"json"
		);
	};

	$('form#ig-tc-admin-form').on( 'submit', function(){
		ig_tc.form_handler();	//handle form data
		return false;	//prevent browser from submitting the form
	} );


	/**
	 **********************************
	 ******** For Metabox UI **********
	 **********************************
	 */

	/**
	 * Show/Hide player card options based on selected card type
	 */
	$('#ig_tc_card_type').on( 'change', function(){
		var card_type = $('#ig_tc_card_type').val();
		if( card_type == 'player' ) {
			$('.ig-tc-mb-player-ui').slideDown( 'slow', 'swing' );
		} else {
			$('.ig-tc-mb-player-ui').slideUp( 'slow', 'swing' );
		}
	} );
	$('#ig_tc_card_type').trigger( 'change' );

	/**
	 * Calculate height based specified width and aspect ratio selected
	 */
	$('#ig_tc_player_width').on( 'keyup', function(){
		var aspect_ratio = ig_tc_admin.get_aspect_ratio();

		if( aspect_ratio === false ) {
			return;
		}

		$('#ig_tc_player_height').val( parseInt( ( parseInt( aspect_ratio[1] ) * parseInt( $('#ig_tc_player_width').val() ) ) / parseInt( aspect_ratio[0] ) ) );
	} );

	/**
	 * Calculate width based specified height and aspect ratio selected
	 */
	$('#ig_tc_player_height').on( 'keyup', function(){
		var aspect_ratio = ig_tc_admin.get_aspect_ratio();

		if( aspect_ratio === false ) {
			return;
		}

		$('#ig_tc_player_width').val( parseInt( ( parseInt( aspect_ratio[0] ) * parseInt( $('#ig_tc_player_height').val() ) ) / parseInt( aspect_ratio[1] ) ) );
	} );

});


//EOF
