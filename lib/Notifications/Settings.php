<?php

namespace s3rgiosan\WP\Plugin\Notifications\Notifications;

use s3rgiosan\WP\Plugin\Notifications\Plugin;

/**
 * Notifications settings
 *
 * @package Notifications
 * @since   1.0.0
 */
class Settings {

	/**
	 * The default options.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	public $default_options = [
		'posts'    => 'all',
		'comments' => 'all',
		'mentions' => 'yes',
	];

	/**
	 * The plugin's instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    Plugin
	 */
	private $plugin;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param Plugin $plugin This plugin's instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		\add_action( 'show_user_profile', [ $this, 'show_user_profile_fields' ] );
		\add_action( 'edit_user_profile', [ $this, 'show_user_profile_fields' ] );
		\add_action( 'personal_options_update', [ $this, 'save_user_profile_fields' ] );
		\add_action( 'edit_user_profile_update', [ $this, 'save_user_profile_fields' ] );
	}

	/**
	 * Show custom user profile fields.
	 *
	 * @param \WP_User $user The \WP_User object of the user being edited.
	 * @return void
	 */
	public function show_user_profile_fields( $user ) {

		$fields = [
			'posts' => [
				'label'   => \esc_html( 'Posts', 'wpnotifications' ),
				'options' => [
					'all'  => \esc_html( 'Send me an email for every new post', 'wpnotifications' ),
					'none' => \esc_html( 'Don\'t send me new post emails', 'wpnotifications' ),
				],
			],
			'comments' => [
				'label'   => \esc_html( 'Comments', 'wpnotifications' ),
				'options' => [
					'all'  => \esc_html( 'Send me an email for every new comment', 'wpnotifications' ),
					'none' => \esc_html( 'Don\'t send me new comment emails', 'wpnotifications' ),
				],
			],
			'mentions' => [
				'label'  => \esc_html( 'Mentions', 'wpnotifications' ),
				'options' => [
					'yes' => \esc_html( 'Make sure I get an email if someone @mentions my username', 'wpnotifications' ),
					'no'  => \esc_html( 'Respect my post and comment notification settings', 'wpnotifications' ),
				],
			],
		];

		$user_options = \get_user_meta( $user->ID, $this->plugin->get_settings_key(), true );

		printf( '<h2>%s</h2>', \esc_html( 'Notifications', 'wpnotifications' )  );

		echo '<table class="form-table">';
		foreach ( $fields as $field_key => $field_atts ) {
			$field_id = $this->get_field_id( $field_key );

			$current = $this->default_options[ $field_key ];
			if ( ! empty( $user_options[ $field_key ] ) ) {
				$current = $user_options[ $field_key ];
			}

			$options = '';
			foreach ( $field_atts['options'] as $key => $value ) {
				$options .= sprintf(
					'<option value="%1$s"%2$s>%3$s</option>',
					\esc_attr( $key ),
					\selected( $key, $current, false ),
					\esc_html( $value )
				);
			}

			$description = '';
			if ( ! empty( $field_atts['description'] ) ) {
				$description = sprintf( '<p class="description">%s</p>', \esc_html( $field_atts['description'] ) );
			}

			printf(
				'<tr>
					<th>
						<label for="%1$s">%2$s</label>
					</th>
					<td>
						<select id="%1$s" name="%1$s">
							%3$s
						</select>
						%4$s
					</td>
				</tr>',
				\esc_attr( $field_id ),
				$field_atts['label'],
				$options,
				$description
			);
		}
		echo '</table>';
	}

	/**
	 * Save custom user profile fields.
	 *
	 * @param int $user_id The user ID of the user being edited.
	 * @return void
	 */
	public function save_user_profile_fields( $user_id ) {

		if ( ! \current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$user_options = $this->default_options;
		foreach ( $user_options as $key => $option ) {
			$field_id = $this->get_field_id( $key );

			if ( empty( $_POST[ $field_id ] ) ) {
				continue;
			}

			$user_options[ $key ] = \esc_attr( $_POST[ $field_id ] );
		}

		\update_user_meta( $user_id, $this->plugin->get_settings_key(), $user_options );
	}

	/**
	 * Get a field ID based on its field key.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  string $key The field key.
	 * @return string The field ID.
	 */
	private function get_field_id( $key ) {
		return "wpnotifications-notifications-{$key}";
	}
}
