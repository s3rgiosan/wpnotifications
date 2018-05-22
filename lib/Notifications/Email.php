<?php

namespace s3rgiosan\WP\Plugin\Notifications\Notifications;

use s3rgiosan\WP\Plugin\Notifications\Plugin;

/**
 * Email notifications handler
 *
 * @package Notifications
 * @since   1.0.0
 */
class Email {

	/**
	 * The plugin's instance.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Plugin
	 */
	protected $plugin;

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
	 * Get a summary from a given text.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @param  string $text The text to summarize.
	 * @return string
	 */
	protected function get_summary( $text ) {
		$text = strip_tags( $text );
		return \wpautop( substr( $text, 0, 195 ) );
	}

	/**
	 * Check if the user is mentioned in a given text.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @param  \WP_User $user The user object.
	 * @param  string   $text The text to check.
	 */
	protected function is_user_mentioned( $user, $text ) {
		$text = strip_tags( \strip_shortcodes( $text ) );
		return (bool) preg_match( '#@' . $user->user_login . '\b#i', $text );
	}

	/**
	 * Highlight the user mentions in a given text.
	 *
	 * Also, replace the user mentions by its user login.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @param  \WP_User $user The user object.
	 * @param  string   $text The text to check.
	 */
	protected function highlight_user_mention( $user, $text ) {
		return preg_replace( '/(\@' . $user->user_login . ')(\b)/i', '<strong>$1</strong>$2', $text, 1 );
	}

	/**
	 * Get the email headers.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @param  array $args
	 * @return array
	 */
	protected function get_email_headers( $args ) {

		$headers = [];

		$headers[] = sprintf(
			'From: %s <%s>',
			$this->get_default_from_name(),
			$this->get_default_from_address()
		);

		/**
		 * Filters the "Reply-To" name.
		 *
		 * @since 1.0.0
		 * @param string $override The "Reply-To" name. Default empty.
		 * @param string $type     The type of notification.
		 * @param int    $id       The post/comment ID.
		 */
		$reply_to_name = \apply_filters( 'wpnotifications_email_reply_to_name', '', $args['type'], $args['id'] );

		/**
		 * Filters the "Reply-To" email.
		 *
		 * @since 1.0.0
		 * @param string $override The "Reply-To" email. Default empty.
		 * @param string $type     The type of notification.
		 * @param int    $id       The post/comment ID.
		 */
		$reply_to_email = \apply_filters( 'wpnotifications_email_reply_to_email', '', $args['type'], $args['id'] );

		if ( ! empty( $reply_to_name ) && ! empty( $reply_to_email ) ) {
			$headers[] = sprintf(
				'Reply-To: %s <%s>',
				$reply_to_name,
				$reply_to_email
			);
		}

		if ( $args['type'] === 'post' ) {
			$headers[] = sprintf(
				'Message-ID: <%s>',
				$this->get_domain_email_address( $args['id'] . '-0' )
			);
		} else {
			$post_id = \get_comment( $args['id'] )->comment_post_ID;

			$headers[] = sprintf(
				'Message-ID: <%s>',
				$this->get_domain_email_address( $post_id . '-' . $args['id'] )
			);

			$headers[] = sprintf(
				'In-Reply-To: <%s>',
				$this->get_domain_email_address( $post_id . '-0'  )
			);
		}

		$headers[] = 'Content-type: text/html';

		return implode( PHP_EOL, $headers );
	}

	/**
	 * Get the email template.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @param  string $type The type of notification.
	 * @param  array  $vars Template vars.
	 * @return void
	 */
	protected function get_email_template( $type, $vars = [] ) {

		$path = WPNOTIFICATIONS_PATH . '/templates/email/' . $type . '.php';

		ob_start();
		if ( file_exists( $path ) ) {
			extract( $vars );
			include $path;
		}

		return \wpautop( ob_get_clean() );
	}

	/**
	 * Get a default "From" email name.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @return string
	 */
	protected function get_default_from_name() {
		return \apply_filters( 'wpnotifications_email_from_name', \get_bloginfo( 'name' ) );
	}

	/**
	 * Get a default "From" email address.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @return string
	 */
	protected function get_default_from_address() {
		return \apply_filters( 'wpnotifications_email_from_address', $this->get_domain_email_address( 'noreply' ) );
	}

	/**
	 * Get a fake email address.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @param  string $mailbox A fake mailbox.
	 * @return string
	 */
	protected function get_domain_email_address( $mailbox ) {
		return $mailbox . '@' . parse_url( \home_url(), PHP_URL_HOST );
	}
}
