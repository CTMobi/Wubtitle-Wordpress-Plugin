<?php
/**
 * Questo file implementa la chiamata http.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Ear2Words\Api
 */

namespace Ear2Words\Api;

use \Ear2words\Core\Sources\YouTube;

/**
 * Questa classe gestisce il custom hook ajax
 */
class ApiGetTranscript {

	/**
	 * Init class action.
	 */
	public function run() {
		add_action( 'wp_ajax_get_transcript', array( $this, 'get_transcript' ) );
	}

	/**
	 * Get transcript.
	 */
	public function get_transcript() {
		// phpcs:disable
		if ( ! isset( $_POST['url'] ) && ! isset( $_POST['source'] ) && ! isset( $_POST['from'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'ear2words' ) );
		}

		$url_video = sanitize_text_field( wp_unslash( $_POST['url'] ) );
		$source = sanitize_text_field( wp_unslash( $_POST['source'] ) );
		$from = sanitize_text_field( wp_unslash( $_POST['from'] ) );
		// phpcs:enable

		switch ( $source ) {
			case 'youtube':
				$video_source = new YouTube();
				break;
			default:
				return;
		}

		$url_parts    = wp_parse_url( $url_video );
		$allowed_urls = array(
			'www.youtube.com',
			'www.youtu.be',
		);
		if ( ! in_array( $url_parts['host'], $allowed_urls, true ) ) {
			wp_send_json_error( __( 'Url not a valid youtube url', 'ear2words' ) );
		}
		$query_params = array();
		parse_str( $url_parts['query'], $query_params );
		$id_video = $query_params['v'];

		$data_posts = $this->get_data_transcript( $id_video, $from );
		if ( $data_posts ) {
			wp_send_json_success( $data_posts );
		}

		$response      = $video_source->send_job_to_backend( $id_video );
		$response_code = wp_remote_retrieve_response_code( $response );
		$message       = array(
			'400' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'ear2words' ),
			'401' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'ear2words' ),
			'403' => __( 'Unable to create transcriptions. Invalid product license', 'ear2words' ),
			'500' => __( 'Could not contact the server', 'ear2words' ),
			'429' => __( 'Error, no more video left for your subscription plan', 'ear2words' ),
		);
		if ( 201 !== $response_code ) {
			wp_send_json_error( $message[ $response_code ] );
		}
		$transcript = $video_source->get_subtitle( $id_video, $from );

		wp_send_json_success( $transcript );
	}
	/**
	 * Recupera i dati se il post esiste e li ritorna.
	 *
	 * @param int    $id_video id del video.
	 * @param string $from indica da dove viene eseguita la chiamata.
	 */
	public function get_data_transcript( $id_video, $from ) {
		$args  = array(
			'post_type'      => 'transcript',
			'posts_per_page' => 1,
			'meta_key'       => '_video_id',
			'meta_value'     => $id_video,
		);
		$posts = get_posts( $args );
		if ( ! empty( $posts ) && 'default_post_type' === $from ) {
			return $posts[0]->ID;
		}
		if ( ! empty( $posts ) && 'transcript_post_type' === $from ) {
			return $posts[0]->post_content;
		}
		return false;
	}

}
