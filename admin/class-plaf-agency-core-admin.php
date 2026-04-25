<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://plaf.agency
 * @since      1.0.0
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/admin
 * @author     PLAF Agency <hola@plaf.agency>
 */
class Plaf_Agency_Core_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private string $version;

	/** Hook suffix for the White Label submenu page. */
	private string $whitelabel_hook = '';

	/** Hook suffix for the Orbit submenu page. */
	private string $orbit_hook = '';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name    The name of this plugin.
	 * @param    string    $version        The version of this plugin.
	 */
	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Registers the top-level "PLAF Agency" menu and its submenus.
	 * Fires on: admin_menu
	 */
	public function add_admin_menu(): void {
		add_menu_page(
			__( 'PLAF Agency', 'plaf-agency-core' ),
			__( 'PLAF Agency', 'plaf-agency-core' ),
			'manage_options',
			'plaf-agency-core',
			'__return_null',
			'dashicons-shield',
			60
		);

		$this->whitelabel_hook = add_submenu_page(
			'plaf-agency-core',
			__( 'White Label', 'plaf-agency-core' ),
			__( 'White Label', 'plaf-agency-core' ),
			'manage_options',
			'plaf-whitelabel',
			[ $this, 'render_whitelabel_page' ]
		);

		$this->orbit_hook = add_submenu_page(
			'plaf-agency-core',
			__( 'Orbit', 'plaf-agency-core' ),
			__( 'Orbit', 'plaf-agency-core' ),
			'manage_options',
			'plaf-orbit',
			[ $this, 'render_orbit_page' ]
		);

		// Remove the duplicate top-level menu item WordPress creates automatically.
		remove_submenu_page( 'plaf-agency-core', 'plaf-agency-core' );
	}

	/**
	 * Renders the White Label settings page.
	 */
	public function render_whitelabel_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permiso para acceder a esta página.', 'plaf-agency-core' ) );
		}
		require_once plugin_dir_path( __FILE__ ) . 'partials/plaf-agency-core-whitelabel-display.php';
	}

	/**
	 * Renders the Orbit integration settings page.
	 */
	public function render_orbit_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permiso para acceder a esta página.', 'plaf-agency-core' ) );
		}
		require_once plugin_dir_path( __FILE__ ) . 'partials/plaf-agency-core-orbit-display.php';
	}

	/**
	 * Handles the White Label settings form submission.
	 * Fires on: admin_post_plaf_save_whitelabel
	 */
	public function save_whitelabel_settings(): void {
		if ( ! isset( $_POST['plaf_whitelabel_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['plaf_whitelabel_nonce'] ) ), 'plaf_save_whitelabel' )
		) {
			wp_die( esc_html__( 'Verificación de seguridad fallida.', 'plaf-agency-core' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permiso para realizar esta acción.', 'plaf-agency-core' ) );
		}

		update_option( 'plaf_client_logo_id', (int) ( $_POST['plaf_client_logo_id'] ?? 0 ) );

		wp_safe_redirect( add_query_arg( 'saved', '1', wp_get_referer() ) );
		exit;
	}

	/**
	 * Handles the Orbit integration settings form submission.
	 * Fires on: admin_post_plaf_save_orbit
	 */
	public function save_orbit_settings(): void {
		if ( ! isset( $_POST['plaf_orbit_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['plaf_orbit_nonce'] ) ), 'plaf_save_orbit' )
		) {
			wp_die( esc_html__( 'Verificación de seguridad fallida.', 'plaf-agency-core' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No tenés permiso para realizar esta acción.', 'plaf-agency-core' ) );
		}

		$orbit_endpoint = sanitize_url( wp_unslash( $_POST['plaf_orbit_endpoint'] ?? '' ) );
		if ( ! empty( $orbit_endpoint ) ) {
			update_option( 'plaf_orbit_endpoint', $orbit_endpoint );
		}

		$orbit_api_key = sanitize_text_field( wp_unslash( $_POST['plaf_orbit_api_key'] ?? '' ) );
		if ( ! empty( $orbit_api_key ) ) {
			update_option( 'plaf_orbit_api_key', $orbit_api_key );
		}

		$this->sync_to_orbit();

		wp_safe_redirect( add_query_arg( 'saved', '1', wp_get_referer() ) );
		exit;
	}

	/**
	 * Sends current site info to Orbit via the configured endpoint.
	 */
	/**
	 * Sends current site info to Orbit. Returns an array with 'ok' bool and 'error' string on failure.
	 *
	 * @return array{ok: bool, error?: string, http_code?: int}
	 */
	public function sync_to_orbit(): array {
		$api_key  = get_option( 'plaf_orbit_api_key', '' );
		$endpoint = get_option( 'plaf_orbit_endpoint', '' );

		if ( empty( $api_key ) ) {
			return [ 'ok' => false, 'error' => 'API Key no configurada.' ];
		}
		if ( empty( $endpoint ) ) {
			return [ 'ok' => false, 'error' => 'Endpoint no configurado.' ];
		}

		$response = wp_remote_post(
			$endpoint,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'body'    => wp_json_encode( [
					'site_url'       => home_url(),
					'site_name'      => get_bloginfo( 'name' ),
					'wp_version'     => get_bloginfo( 'version' ),
					'php_version'    => phpversion(),
					'active_plugins' => get_option( 'active_plugins', [] ),
				] ),
				'timeout' => 10,
			]
		);

		if ( is_wp_error( $response ) ) {
			return [ 'ok' => false, 'error' => $response->get_error_message() ];
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $http_code ) {
			$body = wp_remote_retrieve_body( $response );
			return [ 'ok' => false, 'http_code' => $http_code, 'error' => "HTTP $http_code: $body" ];
		}

		update_option( 'plaf_orbit_last_sync', current_time( 'c' ) );
		return [ 'ok' => true ];
	}

	/**
	 * AJAX handler for the manual "Sync ahora" button.
	 * Fires on: wp_ajax_plaf_orbit_sync
	 */
	public function handle_orbit_sync_ajax(): void {
		check_ajax_referer( 'plaf_orbit_sync', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Sin permisos.' ], 403 );
		}

		$result = $this->sync_to_orbit();

		if ( $result['ok'] ) {
			wp_send_json_success( [ 'last_sync' => get_option( 'plaf_orbit_last_sync', '' ) ] );
		} else {
			wp_send_json_error( [ 'message' => $result['error'] ?? 'Error desconocido.' ] );
		}
	}

	/**
	 * Customizes the WordPress dashboard widgets.
	 * - Removes WordPress Events and News for everyone.
	 * - For non-admins: keeps only At a Glance, Activity, and Quick Draft.
	 * Fires on: wp_dashboard_setup
	 */
	public function customize_dashboard(): void {
		global $wp_meta_boxes;

		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );

		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		$allowed    = [ 'dashboard_right_now', 'dashboard_activity', 'dashboard_quick_press' ];
		$contexts   = [ 'normal', 'side', 'column3', 'column4' ];
		$priorities = [ 'high', 'core', 'default', 'low' ];

		foreach ( $contexts as $context ) {
			foreach ( $priorities as $priority ) {
				if ( empty( $wp_meta_boxes['dashboard'][ $context ][ $priority ] ) ) {
					continue;
				}
				foreach ( array_keys( $wp_meta_boxes['dashboard'][ $context ][ $priority ] ) as $id ) {
					if ( ! in_array( $id, $allowed, true ) ) {
						remove_meta_box( $id, 'dashboard', $context );
					}
				}
			}
		}
	}

	/**
	 * Enqueues the admin stylesheet.
	 * Fires on: admin_enqueue_scripts
	 */
	public function enqueue_styles( string $hook ): void {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plaf-agency-core-admin.css', [], $this->version, 'all' );
	}

	/**
	 * Enqueues the admin JavaScript. Loads the WP media uploader only on our settings page.
	 * Fires on: admin_enqueue_scripts
	 */
	public function enqueue_scripts( string $hook ): void {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plaf-agency-core-admin.js', [ 'jquery' ], $this->version, true );

		if ( $hook === $this->whitelabel_hook ) {
			wp_enqueue_media();
		}

		if ( $hook === $this->orbit_hook ) {
			wp_localize_script(
				$this->plugin_name,
				'plafAdmin',
				[
					'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
					'syncNonce' => wp_create_nonce( 'plaf_orbit_sync' ),
					'lastSync'  => get_option( 'plaf_orbit_last_sync', '' ),
				]
			);
		}
	}

}
