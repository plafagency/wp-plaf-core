<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://plaf.agency
 * @since      1.0.0
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 * @author     PLAF Agency <hola@plaf.agency>
 */
class Plaf_Agency_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plaf_Agency_Core_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLAF_AGENCY_CORE_VERSION' ) ) {
			$this->version = PLAF_AGENCY_CORE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'plaf-agency-core';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_login_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plaf_Agency_Core_Loader. Orchestrates the hooks of the plugin.
	 * - Plaf_Agency_Core_i18n. Defines internationalization functionality.
	 * - Plaf_Agency_Core_Admin. Defines all hooks for the admin area.
	 * - Plaf_Agency_Core_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-plaf-agency-core-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-plaf-agency-core-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-plaf-agency-core-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-plaf-agency-core-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-plaf-agency-core-login.php';

		$this->loader = new Plaf_Agency_Core_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plaf_Agency_Core_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Plaf_Agency_Core_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Plaf_Agency_Core_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'add_cron_schedules' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_post_plaf_save_whitelabel', $plugin_admin, 'save_whitelabel_settings' );
		$this->loader->add_action( 'admin_post_plaf_save_orbit', $plugin_admin, 'save_orbit_settings' );
		$this->loader->add_action( 'wp_dashboard_setup', $plugin_admin, 'customize_dashboard' );
		$this->loader->add_action( 'wp_ajax_plaf_orbit_sync', $plugin_admin, 'handle_orbit_sync_ajax' );
		$this->loader->add_action( 'plaf_orbit_daily_sync', $plugin_admin, 'sync_to_orbit' );
		$this->loader->add_action( 'upgrader_process_complete', $plugin_admin, 'log_update_event', 10, 2 );
		$this->loader->add_action( 'plaf_orbit_monthly_cleanup', $plugin_admin, 'run_monthly_cleanup' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Plaf_Agency_Core_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Register all hooks related to the WordPress login page customization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_login_hooks() {

		$plugin_login = new Plaf_Agency_Core_Login();

		$this->loader->add_action( 'login_enqueue_scripts', $plugin_login, 'enqueue_login_styles' );
		$this->loader->add_action( 'login_head', $plugin_login, 'inject_powered_by' );
		$this->loader->add_filter( 'login_headerurl', $plugin_login, 'login_header_url' );
		$this->loader->add_filter( 'login_headertext', $plugin_login, 'login_header_text' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plaf_Agency_Core_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
