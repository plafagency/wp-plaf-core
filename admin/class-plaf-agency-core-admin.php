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
	}

}
