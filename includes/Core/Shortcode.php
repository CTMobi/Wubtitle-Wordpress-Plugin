<?php
/**
 * This file handles shortcode.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Ear2Words\Core
 */

namespace Ear2Words\Core;

/**
 * This class describes shortcodes methods.
 */
class Shortcode {
	/**
	 * Init class actions.
	 */
	public function run() {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register all shortcode.
	 */
	public function register_shortcodes() {
		add_shortcode( 'transcript', array( $this, 'shortcode_transcript' ) );
	}

	/**
	 * Register transcript shortcode callback.
	 *
	 * @param array $atts parametri passati dallo shortcode.
	 */
	public function shortcode_transcript( $atts ) {
		$post = get_post( $atts['id'] );

		if ( null === $post ) {
			return;
		}

		if ( 'transcript' === $post->post_type ) {
			return apply_filters( 'the_content', $post->post_content );
		}
		wp_reset_postdata();
	}

}
