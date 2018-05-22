<?php

namespace s3rgiosan\WP\Plugin\Notifications\Notifications\Post;

/**
 * Email notifications for posts
 *
 * @package Notifications
 * @since   1.0.0
 */
class Email extends \s3rgiosan\WP\Plugin\Notifications\Notifications\Email {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		\add_action( 'publish_post', [ $this, 'queue' ] );
	}

	/**
	 * Queue a notification after a post is published.
	 *
	 * @since  1.0.0
	 * @param  int $ID Post ID.
	 * @return void
	 */
	public function queue( $ID ) {

		$post = \get_post( $ID );
		if ( ! $post ) {
			return;
		}

		// Update sent timestamp.
		\update_post_meta( $post->ID, $this->plugin->get_sent_timestamp_key(), time() );

		$users = \get_users();
		foreach ( $users as $user ) {

			$user_options = \get_user_meta( $user->ID, $this->plugin->get_settings_key(), true );

			if ( ! empty( $user_options['posts'] ) &&  $user_options['posts'] === 'all' ) {
				$this->send( $post, $user );
				continue;
			}

			if ( empty( $user_options['mentions'] ) ) {
				continue;
			}

			if ( $user_options['mentions'] !== 'yes' ) {
				continue;
			}

			$is_user_mentioned = $this->is_user_mentioned( $user, $post->post_content );
			if ( $is_user_mentioned ) {
				$this->send( $post, $user );
			}
		}
	}

	/**
	 * Send a notification to a user.
	 *
	 * @since  1.0.0
	 * @param  \WP_Post $post The post object.
	 * @param  \WP_User $user The user object.
 	 * @return void
	 */
	public function send( $post, $user ) {
		\wp_mail(
			$user->user_email,
			$this->get_email_subject( $post ),
			$this->get_email_body( $post, $user ),
			$this->get_email_headers( [
				'type' => 'post',
				'id'   => $post->ID,
			] )
		);
	}

	/**
	 * Get the email subject.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  \WP_Post $post The post object.
	 * @return string
	 */
	private function get_email_subject( $post ) {

		$remove_texturize = \remove_filter( 'the_title', 'wptexturize' );

		$subject = sprintf(
			\esc_html__( '[New post] %s', 'wpnotifications' ),
			\apply_filters( 'the_title', \get_the_title( $post->ID ) )
		);

		if ( $remove_texturize ) {
			\add_filter( 'the_title', 'wptexturize' );
		}

		/**
		 * Filters the email subject.
		 *
		 * @since 1.0.0
		 * @param string   $subject The email subject.
		 * @param \WP_Post $post    The post object.
		 */
		return \apply_filters( 'wpnotifications_post_email_subject', $subject, $post );
	}

	/**
	 * Get the email body.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  \WP_Post $object The post object.
	 * @param  \WP_User $user   The user object.
	 * @return string
	 */
	private function get_email_body( $object, $user ) {
		global $post;

		$post = $object;
		$post->post_content = $this->highlight_user_mention( $user, $post->post_content );

		\setup_postdata( $post );

		$author_wrote = sprintf(
			'%s <a href="%s">wrote</a>',
			\get_user_by( 'id', $post->post_author )->display_name,
			\esc_url( \get_permalink( $post->ID ) )
		);

		$vars = compact( 'post', 'author_wrote' );

		$body = $this->get_email_template( 'post', $vars );

		/**
		 * Filters the email body.
		 *
		 * @since 1.0.0
		 * @param string   $body The email body.
		 * @param \WP_Post $post The post object.
		 */
		return \apply_filters( 'wpnotifications_post_email_body', $body, $post );
	}
}
