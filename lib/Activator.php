<?php

namespace s3rgiosan\WP\Plugin\Notifications;

/**
 * Fired during plugin activation
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package Notifications
 * @since   1.0.0
 */
class Activator {

	/**
	 * Activation handler.
	 *
	 * @since 1.0.0
	 * @param bool $network_wide True if WPMU superadmin uses "Network Activate" action,
	 *                           false if WPMU is disabled or plugin is activated on an
	 *                           individual blog.
	 */
	public static function activate( $network_wide = false ) {

		if ( $network_wide && \is_multisite() ) {

			$sites = \get_sites( [
				'number' => false,
			] );

			foreach ( $sites as $site ) {
				\switch_to_blog( $site['blog_id'] );
				static::single_activate( $network_wide );
			}

			\restore_current_blog();
			return;
		}

		static::single_activate( $network_wide );
	}

	/**
	 * Single activation handler.
	 *
	 * @since 1.0.0
	 * @param bool $network_wide True if WPMU superadmin uses "Network Activate" action,
	 *                           false if WPMU is disabled or plugin is activated on an
	 *                           individual blog.
	 */
	public static function single_activate( $network_wide ) {}
}
