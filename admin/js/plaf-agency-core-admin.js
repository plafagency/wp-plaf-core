/* PLAF Agency Core - Admin JS */
( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.plaf-upload-btn', function ( e ) {
		e.preventDefault();

		var $btn       = $( this );
		var $field     = $btn.closest( '.plaf-logo-field' );
		var $input     = $field.find( '.plaf-logo-id' );
		var $preview   = $field.find( '.plaf-logo-preview' );
		var $removeBtn = $field.find( '.plaf-remove-btn' );

		var frame = wp.media( {
			title    : 'Seleccionar logo',
			button   : { text: 'Usar este logo' },
			multiple : false,
			library  : { type: 'image' },
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			$input.val( attachment.id );
			$preview.attr( 'src', attachment.url ).show();
			$removeBtn.show();
			$btn.text( 'Cambiar logo' );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.plaf-remove-btn', function ( e ) {
		e.preventDefault();

		var $field = $( this ).closest( '.plaf-logo-field' );
		$field.find( '.plaf-logo-id' ).val( '' );
		$field.find( '.plaf-logo-preview' ).hide().attr( 'src', '' );
		$( this ).hide();
		$field.find( '.plaf-upload-btn' ).text( 'Seleccionar logo' );
	} );
} )( jQuery );
