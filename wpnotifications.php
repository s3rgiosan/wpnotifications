<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Simple Notifications and Alerts
 * Plugin URI:        https://github.com/s3rgiosan/wpnotifications
 * Description:       Simple email notifications and alerts for WordPress.
 * Version:           1.0.0
 * Author:            Sérgio Santos
 * Author URI:        http://s3rgiosan.com/
 * License:           GPL-2.0
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpnotifications
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/s3rgiosan/wpnotifications
 * GitHub Branch:     master
 */

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in lib/Activator.php
 */
\register_activation_hook( __FILE__, '\s3rgiosan\WP\Plugin\Notifications\Activator::activate' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in lib/Deactivator.php
 */
\register_deactivation_hook( __FILE__, '\s3rgiosan\WP\Plugin\Notifications\Deactivator::deactivate' );

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
\add_action( 'plugins_loaded', function () {
	define( 'WPNOTIFICATIONS_PATH', \plugin_dir_path( __FILE__ ) );

	$plugin = new s3rgiosan\WP\Plugin\Notifications\Plugin( 'wpnotifications', '1.0.0' );
	$plugin->run();
} );
