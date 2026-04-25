<?php

/**
 * Handles white-label customization of the WordPress login page.
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 */
class Plaf_Agency_Core_Login {

	/**
	 * Enqueues inline CSS to replace the WordPress login logo with the client logo.
	 * Fires on: login_enqueue_scripts
	 */
	public function enqueue_login_styles(): void {
		$client_logo_id = (int) get_option( 'plaf_client_logo_id', 0 );

		if ( ! $client_logo_id ) {
			return;
		}

		$client_logo = wp_get_attachment_image_src( $client_logo_id, 'full' );

		if ( ! $client_logo ) {
			return;
		}

		[ $url, $width, $height ] = $client_logo;

		$max_width = 320;
		if ( $width > $max_width ) {
			$ratio  = $max_width / $width;
			$width  = $max_width;
			$height = (int) round( $height * $ratio );
		}

		$css = sprintf(
			'#login h1 a { background-image: url(%s); background-size: contain; width: %dpx; height: %dpx; }',
			esc_url( $url ),
			$width,
			$height
		);

		wp_register_style( 'plaf-login', false, [], null );
		wp_enqueue_style( 'plaf-login' );
		wp_add_inline_style( 'plaf-login', $css );
	}

	/**
	 * Injects the "Powered by [agency logo]" block below the client logo via JS.
	 * login_header fires before #login in the DOM, so we use insertAdjacentElement
	 * from login_head (in <head>) to place the block right after the h1.
	 * Fires on: login_head
	 */
	public function inject_powered_by(): void {
		$logo_url = plugin_dir_url( dirname( __FILE__ ) ) . 'admin/images/plaf-agency-logo.png';
		?>
		<style>
		.plaf-powered-by { display: flex; align-items: center; justify-content: center; gap: 6px; margin: -12px 0 20px; }
		.plaf-powered-by span { color: #50575e; font-size: 12px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
		.plaf-powered-by img { height: 18px; width: auto; }
		</style>
		<script>
		document.addEventListener( 'DOMContentLoaded', function () {
			var h1 = document.querySelector( '#login h1' );
			if ( ! h1 ) return;
			var div = document.createElement( 'div' );
			div.className = 'plaf-powered-by';
			div.innerHTML = '<span>Powered by<\/span><img src="<?php echo esc_url( $logo_url ); ?>" alt="PLAF Agency" />';
			h1.insertAdjacentElement( 'afterend', div );
		} );
		</script>
		<?php
	}

	/**
	 * Changes the login logo link to point to the site home URL.
	 * Fires on: login_headerurl (filter)
	 */
	public function login_header_url( string $url ): string {
		return home_url( '/' );
	}

	/**
	 * Changes the login logo title to the site name.
	 * Fires on: login_headertext (filter)
	 */
	public function login_header_text( string $text ): string {
		return get_bloginfo( 'name' );
	}
}
