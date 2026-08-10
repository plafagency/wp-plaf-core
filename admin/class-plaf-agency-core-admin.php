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

		$wordfence = $this->get_wordfence_summary();
		$updraft   = $this->get_updraft_summary();
		$ssl       = $this->get_ssl_summary();
		$integrity = $this->get_core_integrity_summary();
		$cleanup   = get_option( 'plaf_last_cleanup', null );

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

					'wordfence_active'       => null !== $wordfence,
					'attacks_blocked'        => $wordfence['attacks_blocked'] ?? null,
					'security_issues_found'  => $wordfence['issues_found'] ?? null,
					'last_scan_at'           => $wordfence['last_scan_at'] ?? null,
					'files_intact'           => $integrity['files_intact'] ?? null,

					'ssl_valid'      => $ssl['valid'],
					'ssl_expires_at' => $ssl['expires_at'],

					'updraft_active'     => null !== $updraft,
					'backups_count'      => $updraft['backups_count'] ?? null,
					'backup_destination' => $updraft['destination'] ?? null,
					'last_backup_at'     => $updraft['last_backup_at'] ?? null,

					'updates_log' => $this->get_updates_log(),

					'db_space_freed_kb'    => $cleanup['space_freed_kb'] ?? null,
					'db_revisions_deleted' => $cleanup['revisions_deleted'] ?? null,
					'db_tables_optimized'  => $cleanup['tables_optimized'] ?? null,
				] ),
				'timeout' => 15,
			]
		);

		if ( is_wp_error( $response ) ) {
			return [ 'ok' => false, 'error' => $response->get_error_message() ];
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $http_code ) {
			if ( 404 === $http_code ) {
				return [ 'ok' => false, 'http_code' => $http_code, 'error' => 'Endpoint no encontrado (404). Verificá que la URL sea correcta y que la función esté desplegada.' ];
			}
			$body = wp_remote_retrieve_body( $response );
			// If it's a JSON error from the function, try to extract the message
			$json = json_decode( $body, true );
			$error_msg = isset( $json['error'] ) ? $json['error'] : substr( $body, 0, 200 );
			return [ 'ok' => false, 'http_code' => $http_code, 'error' => "HTTP $http_code: $error_msg" ];
		}

		update_option( 'plaf_orbit_last_sync', current_time( 'c' ) );
		return [ 'ok' => true ];
	}

	/**
	 * Registers the "monthly" cron schedule used by the DB cleanup job.
	 * Fires on: cron_schedules
	 *
	 * @param array<string, array{interval: int, display: string}> $schedules Existing schedules.
	 * @return array<string, array{interval: int, display: string}>
	 */
	public function add_cron_schedules( array $schedules ): array {
		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = [
				'interval' => 30 * DAY_IN_SECONDS,
				'display'  => __( 'Una vez al mes', 'plaf-agency-core' ),
			];
		}
		return $schedules;
	}

	/**
	 * Reads Wordfence's own scan/firewall data, if the plugin is active.
	 * Read-only: never touches Wordfence's tables or options.
	 *
	 * @return array{attacks_blocked: int, issues_found: int, last_scan_at: string|null}|null
	 */
	private function get_wordfence_summary(): ?array {
		global $wpdb;

		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}

		$issues_table = $wpdb->prefix . 'wfIssues';
		$blocks_table = $wpdb->prefix . 'wfBlockedIPLog';

		$issues_found = 0;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $issues_table ) ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is validated above via SHOW TABLES.
			$issues_found = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$issues_table}` WHERE status = 'new'" );
		}

		$attacks_blocked = 0;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $blocks_table ) ) ) {
			$since = gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name is validated above via SHOW TABLES.
			$attacks_blocked = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) FROM `{$blocks_table}` WHERE blockDate >= %s", $since )
			);
		}

		$last_scan_ts = (int) get_option( 'wordfence_lastScanCompleted', 0 );

		return [
			'attacks_blocked' => $attacks_blocked,
			'issues_found'    => $issues_found,
			'last_scan_at'    => $last_scan_ts > 0 ? gmdate( 'c', $last_scan_ts ) : null,
		];
	}

	/**
	 * Reads UpdraftPlus's own backup history, if the plugin is active.
	 * Read-only: never triggers or configures backups.
	 *
	 * @return array{backups_count: int, last_backup_at: string|null, destination: string|null}|null
	 */
	private function get_updraft_summary(): ?array {
		if ( ! function_exists( 'updraft_get_updraftplus_instance' ) && ! class_exists( 'UpdraftPlus' ) ) {
			return null;
		}

		$history = get_option( 'updraft_backup_history', [] );
		if ( ! is_array( $history ) || empty( $history ) ) {
			return [ 'backups_count' => 0, 'last_backup_at' => null, 'destination' => null ];
		}

		$since_ts     = strtotime( '-30 days' );
		$in_period    = array_filter( array_keys( $history ), fn( $ts ) => is_numeric( $ts ) && (int) $ts >= $since_ts );
		$last_ts      = max( array_keys( $history ) );
		$last_backup  = $history[ $last_ts ] ?? [];
		$destinations = (array) ( $last_backup['service'] ?? [] );

		return [
			'backups_count'  => count( $in_period ),
			'last_backup_at' => gmdate( 'c', (int) $last_ts ),
			'destination'    => ! empty( $destinations ) ? implode( ',', (array) $destinations ) : null,
		];
	}

	/**
	 * Checks the site's own SSL certificate validity and expiration.
	 *
	 * @return array{valid: bool|null, expires_at: string|null}
	 */
	private function get_ssl_summary(): array {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! $host ) {
			return [ 'valid' => null, 'expires_at' => null ];
		}

		$context = stream_context_create( [ 'ssl' => [ 'capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false ] ] );
		$client  = @stream_socket_client( "ssl://{$host}:443", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context );

		if ( ! $client ) {
			return [ 'valid' => null, 'expires_at' => null ];
		}

		$params = stream_context_get_params( $client );
		fclose( $client );

		if ( empty( $params['options']['ssl']['peer_certificate'] ) ) {
			return [ 'valid' => null, 'expires_at' => null ];
		}

		$cert = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );
		if ( ! $cert || empty( $cert['validTo_time_t'] ) ) {
			return [ 'valid' => null, 'expires_at' => null ];
		}

		$expires_ts = (int) $cert['validTo_time_t'];

		return [
			'valid'      => $expires_ts > time(),
			'expires_at' => gmdate( 'c', $expires_ts ),
		];
	}

	/**
	 * Compares core files against the WordPress.org checksums API.
	 *
	 * @return array{files_intact: bool}|null
	 */
	private function get_core_integrity_summary(): ?array {
		global $wp_version;

		$locale   = get_locale();
		$response = wp_remote_get(
			"https://api.wordpress.org/core/checksums/1.0/?version={$wp_version}&locale={$locale}",
			[ 'timeout' => 10 ]
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['checksums'] ) || ! is_array( $data['checksums'] ) ) {
			return null;
		}

		$abspath_len = strlen( ABSPATH );
		foreach ( $data['checksums'] as $relative_path => $expected_md5 ) {
			// Skip files that admins commonly customize / that don't ship identically everywhere.
			if ( str_starts_with( $relative_path, 'wp-content/' ) ) {
				continue;
			}
			$full_path = ABSPATH . $relative_path;
			if ( ! file_exists( $full_path ) ) {
				return [ 'files_intact' => false ];
			}
			if ( md5_file( $full_path ) !== $expected_md5 ) {
				return [ 'files_intact' => false ];
			}
		}

		return [ 'files_intact' => true ];
	}

	/**
	 * Returns the log of core/plugin/theme updates applied in the last 30 days.
	 *
	 * @return array<int, array{type: string, name: string, date: string}>
	 */
	private function get_updates_log(): array {
		$log   = get_option( 'plaf_update_log', [] );
		$since = strtotime( '-30 days' );

		return array_values(
			array_filter(
				is_array( $log ) ? $log : [],
				fn( $entry ) => isset( $entry['date'] ) && strtotime( $entry['date'] ) >= $since
			)
		);
	}

	/**
	 * Logs a core/plugin/theme update so it can be reported to Orbit later.
	 * Fires on: upgrader_process_complete
	 *
	 * @param WP_Upgrader          $upgrader    The upgrader instance (unused).
	 * @param array<string, mixed> $hook_extra  Details about what was upgraded.
	 */
	public function log_update_event( $upgrader, array $hook_extra ): void {
		if ( empty( $hook_extra['type'] ) ) {
			return;
		}

		$type = (string) $hook_extra['type'];
		$name = 'core';

		if ( 'plugin' === $type ) {
			$name = is_array( $hook_extra['plugins'] ?? null ) ? implode( ', ', $hook_extra['plugins'] ) : (string) ( $hook_extra['plugin'] ?? 'plugin' );
		} elseif ( 'theme' === $type ) {
			$name = is_array( $hook_extra['themes'] ?? null ) ? implode( ', ', $hook_extra['themes'] ) : (string) ( $hook_extra['theme'] ?? 'theme' );
		}

		$log   = get_option( 'plaf_update_log', [] );
		$log   = is_array( $log ) ? $log : [];
		$log[] = [ 'type' => $type, 'name' => $name, 'date' => current_time( 'c' ) ];

		// Keep only the last 90 days to avoid unbounded growth.
		$since = strtotime( '-90 days' );
		$log   = array_values( array_filter( $log, fn( $entry ) => isset( $entry['date'] ) && strtotime( $entry['date'] ) >= $since ) );

		update_option( 'plaf_update_log', $log );
	}

	/**
	 * Runs a real database cleanup: old revisions, expired transients, spam/trashed
	 * comments, then OPTIMIZE TABLE on all tables. Persists the result so the next
	 * sync can report it to Orbit.
	 * Fires on: plaf_orbit_monthly_cleanup
	 *
	 * @return array{space_freed_kb: int, revisions_deleted: int, tables_optimized: int}
	 */
	public function run_monthly_cleanup(): array {
		global $wpdb;

		$size_before = (int) $wpdb->get_var(
			"SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = DATABASE()"
		);

		$revisions_deleted = (int) $wpdb->query(
			"DELETE p FROM {$wpdb->posts} p WHERE p.post_type = 'revision'"
		);

		$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved IN ('spam', 'trash')" );

		$expired_transients = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_timeout\\_%'"
		);
		foreach ( $expired_transients as $timeout_option ) {
			$transient = str_replace( '_transient_timeout_', '', $timeout_option );
			if ( (int) get_option( $timeout_option ) < time() ) {
				delete_transient( $transient );
			}
		}

		$tables           = $wpdb->get_col( 'SHOW TABLES' );
		$tables_optimized = 0;
		foreach ( $tables as $table ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table names come from SHOW TABLES, not user input.
			if ( false !== $wpdb->query( "OPTIMIZE TABLE `{$table}`" ) ) {
				++$tables_optimized;
			}
		}

		$size_after = (int) $wpdb->get_var(
			"SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = DATABASE()"
		);

		$result = [
			'space_freed_kb'    => max( 0, (int) round( ( $size_before - $size_after ) / 1024 ) ),
			'revisions_deleted' => $revisions_deleted,
			'tables_optimized'  => $tables_optimized,
			'ran_at'            => current_time( 'c' ),
		];

		update_option( 'plaf_last_cleanup', $result );

		return $result;
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
