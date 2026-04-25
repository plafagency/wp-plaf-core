<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://plaf.agency
 * @since      1.0.0
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 * @author     PLAF Agency <hola@plaf.agency>
 */
class Plaf_Agency_Core_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'plaf_orbit_daily_sync' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'plaf_orbit_daily_sync' );
		}
	}

}
