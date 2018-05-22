<?php

namespace s3rgiosan\WP\Plugin\Notifications\Notifications\Comment;

/**
 * Email notifications for comments
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
		// Disable the default core notification.
		\add_filter( 'pre_option_comments_notify', '__return_zero' );

		\add_action( 'wp_insert_comment', [ $this, 'queue' ] );
	}

	/**
	 * Queue a notification after a comment is made.
	 *
	 * @since  1.0.0
	 * @param  int $ID The comment ID.
	 * @return void
	 */
	public function queue( $ID ) {

		$comment = \get_comment( $ID );
		if ( empty( $comment->comment_approved ) ) {
			return;
		}

		$users = \get_users();
		foreach ( $users as $user ) {

			$user_options = \get_user_meta( $user->ID, $this->plugin->get_settings_key(), true );

			if ( ! empty( $user_options['comments'] ) &&  $user_options['comments'] === 'all' ) {
				$this->send( $comment, $user );
				continue;
			}

			if ( empty( $user_options['mentions'] ) ) {
				continue;
			}

			if ( $user_options['mentions'] !== 'yes' ) {
				continue;
			}

			$is_user_mentioned = $this->is_user_mentioned( $user, $comment->comment_content );
			if ( $is_user_mentioned ) {
				$this->send( $comment, $user );
			}
		}
	}

	/**
	 * Send a notification to a user.
	 *
	 * @since  1.0.0
	 * @param  \WP_Comment $comment The comment object.
	 * @param  \WP_User    $user    The user object.
 	 * @return void
	 */
	public function send( $comment, $user ) {
		\wp_mail(
			$user->user_email,
			$this->get_email_subject( $comment ),
			$this->get_email_body( $comment, $user ),
			$this->get_email_headers( [
				'type' => 'comment',
				'id'   => $comment->comment_ID,
			] )
		);
	}

	/**
	 * Get the email subject.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  \WP_Comment $comment The comment object.
	 * @return string
	 */
	private function get_email_subject( $comment ) {

		$remove_texturize = \remove_filter( 'the_title', 'wptexturize' );

		$subject = sprintf(
			\esc_html__( '[New comment] %s', 'wpnotifications' ),
			\apply_filters( 'the_title', \get_the_title( $comment->comment_post_ID ) )
		);

		if ( $remove_texturize ) {
			\add_filter( 'the_title', 'wptexturize' );
		}

		/**
		 * Filters the email subject.
		 *
		 * @since 1.0.0
		 * @param string      $subject The email subject.
		 * @param \WP_Comment $comment The comment object.
		 */
		return \apply_filters( 'wpnotifications_comment_email_subject', $subject, $comment );
	}

	/**
	 * Get the email body.
	 *
	 * @since  1.0.0
	 * @access private
	 * @param  \WP_Comment $object The comment object.
	 * @param  \WP_User    $user   The user object.
	 * @return string
	 */
	private function get_email_body( $object, $user ) {
		global $comment;

		$comment = $object;
		$comment->comment_content = $this->highlight_user_mention( $user, $comment->comment_content );

		$in_reply_to = '%s replied to <a href="%s">%s</a>';
		$quoted_text = '';

		if ( ! empty( $comment->comment_parent ) ) {

			$in_reply_to = sprintf(
				$in_reply_to,
				\get_comment_author( $comment->comment_ID ),
				\esc_url( \get_comment_link( $comment->comment_parent ) ),
				\get_comment_author( $comment->comment_parent )
			);

			$quoted_text = $this->get_summary( \get_comment( $comment->comment_parent )->comment_content );
		} else {
			$post = \get_post( $comment->comment_post_ID );

			$in_reply_to = sprintf(
				$in_reply_to,
				\get_comment_author( $comment->comment_ID ),
				\esc_url( \get_permalink( $comment->comment_post_ID ) ),
				\get_user_by( 'id', $post->post_author )->display_name
			);

			$quoted_text = $this->get_summary( \apply_filters( 'the_content', $post->post_content ) );
		}

		$vars = compact( 'comment', 'in_reply_to', 'quoted_text' );

		$body = $this->get_email_template( 'comment', $vars );

		/**
		 * Filters the email body.
		 *
		 * @since 1.0.0
		 * @param string      $body    The email body.
		 * @param \WP_Comment $comment The comment object.
		 */
		return \apply_filters( 'wpnotifications_comment_email_body', $body, $comment );
	}
}
