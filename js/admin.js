/**
 * JS for admin UI of iG:Twitter Cards plugin
 *
 * @author: Amit Gupta
 * @since: 2013-02-18
 * @version: 2013-04-06
 * @version: 2013-06-06
 * @version: 2013-06-07
 */


jQuery(document).ready(function($) {

	var ig_tc_admin = {
		hide_jq_msg: function() {
			$.msg( 'unblock' );
		},
		show_height_per_aspect_ratios: function( value ) {
			if( typeof value == 'undefined' || value == null ) {
				return;
			}

			if( ! value || isNaN( value ) || parseInt( value ) < 0 ) {
				value = 0;
			} else {
				value = parseInt( value );
			}

			var aspect_ratios = [ '16:9', '4:3' ];

			for( var i = 0; i < aspect_ratios.length; i++ ) {
				var aspect_ratio = aspect_ratios[i].split( ':' );

				var height = parseInt( ( parseInt( aspect_ratio[1] ) * parseInt( value ) ) / parseInt( aspect_ratio[0] ) );

				var elem_id = aspect_ratio.join('-');
				$( '#ig_tc_player_aspect_ratio #' + elem_id ).html( height );
			}
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
	 * Calculate and show height based on specified width for all defined aspect ratios
	 */
	$('#ig_tc_player_width').on( 'keyup', function(){
		ig_tc_admin.show_height_per_aspect_ratios( $('#ig_tc_player_width').val() );
	} );

});


//EOF
