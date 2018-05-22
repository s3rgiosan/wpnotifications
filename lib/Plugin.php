<?php

namespace s3rgiosan\WP\Plugin\Notifications;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package Notifications
 * @since   1.0.0
 */
class Plugin {

	/**
	 * The settings key.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string
	 */
	protected $settings_key = 'wpnotifications_settings';

	/**
	 * The sent timestamp key.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private $sent_timestamp_key = 'wpnotifications_sent_timestamp';

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $name
	 */
	protected $name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since 1.0.0
	 * @param string $name    The plugin identifier.
	 * @param string $version Current version of the plugin.
	 */
	public function __construct( $name, $version ) {
		$this->name    = $name;
		$this->version = $version;
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
	 * Load the dependencies, define the locale, and set the hooks for the
	 * Dashboard and the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->set_locale();
		$this->define_notifications_hooks();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Returns the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Returns the settings key.
	 *
	 * @since  1.0.0
	 * @return string The settings key.
	 */
	public function get_settings_key() {
		return $this->settings_key;
	}

	/**
	 * Returns the post's sent timestamp key.
	 *
	 * @since  1.0.0
	 * @return string The post's sent timestamp key.
	 */
	public function get_sent_timestamp_key() {
		return $this->sent_timestamp_key;
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$i18n = new I18n();
		$i18n->set_domain( $this->get_name() );
		$i18n->load_plugin_textdomain();
	}

	/**
	 * Register all of the hooks related to the notifications functionality.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_notifications_hooks() {

		$components = [
			new Notifications\Comment\Email( $this ),
			new Notifications\Post\Email( $this ),
			new Notifications\Settings( $this ),
		];

		foreach ( $components as $component ) {
			$component->register();
		}
	}
}
