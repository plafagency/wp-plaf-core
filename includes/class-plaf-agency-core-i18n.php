<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://plaf.agency
 * @since      1.0.0
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 * @author     PLAF Agency <hola@plaf.agency>
 */
class Plaf_Agency_Core_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'plaf-agency-core',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
