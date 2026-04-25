<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://plaf.agency
 * @since             1.0.0
 * @package           Plaf_Agency_Core
 *
 * @wordpress-plugin
 * Plugin Name:       PLAF Agency | Core Manager
 * Plugin URI:        https://plaf.agency
 * Description:       Gestor de plugins y settings de PLAF Agency.
 * Version:           1.0.0
 * Author:            PLAF Agency
 * Author URI:        https://plaf.agency/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plaf-agency-core
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLAF_AGENCY_CORE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plaf-agency-core-activator.php
 */
function activate_plaf_agency_core() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plaf-agency-core-activator.php';
	Plaf_Agency_Core_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plaf-agency-core-deactivator.php
 */
function deactivate_plaf_agency_core() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plaf-agency-core-deactivator.php';
	Plaf_Agency_Core_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plaf_agency_core' );
register_deactivation_hook( __FILE__, 'deactivate_plaf_agency_core' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-plaf-agency-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plaf_agency_core() {

	$plugin = new Plaf_Agency_Core();
	$plugin->run();

}
run_plaf_agency_core();
