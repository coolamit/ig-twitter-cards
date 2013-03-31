/**
 * JS for admin UI of iG:Twitter Cards plugin
 *
 * @author: Amit Gupta
 * @since: 2013-02-18
 * @version: 2013-04-01
 */


jQuery(document).ready(function($) {

	function hide_jq_msg() {
		$.msg( 'unblock' );
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
				setTimeout( hide_jq_msg, 2000 );
				if( ! data || ! data.nonce || ! data.msg ) {
					$.msg( 'replace', '<span class="ig-tc-error">Unable to save options</span>' );
				} else {
					ig_tc.nonce = data.nonce;
					$.msg( 'replace', data.msg );

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
	 * For the meta-box UI
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

});


//EOF
