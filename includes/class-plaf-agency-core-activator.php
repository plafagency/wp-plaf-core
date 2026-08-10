<?php

/**
 * Fired during plugin activation
 *
 * @link       https://plaf.agency
 * @since      1.0.0
 *
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Plaf_Agency_Core
 * @subpackage Plaf_Agency_Core/includes
 * @author     PLAF Agency <hola@plaf.agency>
 */
class Plaf_Agency_Core_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! wp_next_scheduled( 'plaf_orbit_daily_sync' ) ) {
			wp_schedule_event( time(), 'daily', 'plaf_orbit_daily_sync' );
		}
		if ( ! wp_next_scheduled( 'plaf_orbit_monthly_cleanup' ) ) {
			wp_schedule_event( time(), 'monthly', 'plaf_orbit_monthly_cleanup' );
		}
	}

}
