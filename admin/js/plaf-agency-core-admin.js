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

	$( document ).on( 'click', '#plaf-orbit-sync-btn', function ( e ) {
		e.preventDefault();

		if ( typeof plafAdmin === 'undefined' ) {
			return;
		}

		var $btn    = $( this );
		var $status = $( '#plaf-orbit-sync-status' );

		$btn.prop( 'disabled', true ).text( 'Sincronizando…' );

		$.post(
			plafAdmin.ajaxUrl,
			{
				action : 'plaf_orbit_sync',
				nonce  : plafAdmin.syncNonce,
			},
			function ( response ) {
				if ( response.success ) {
					$status.text( 'Sincronizado correctamente: ' + response.data.last_sync ).css( 'color', 'green' );
				} else {
					$status.text( response.data.message || 'Error al sincronizar.' ).css( 'color', 'red' );
				}
			}
		).always( function () {
			$btn.prop( 'disabled', false ).text( 'Sync ahora' );
		} );
	} );
} )( jQuery );
