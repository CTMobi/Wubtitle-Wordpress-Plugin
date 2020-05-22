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
		add_action( 'wp_ajax_get_transcript_yt', array( $this, 'get_transcript_yt' ) );
		add_action( 'wp_ajax_get_transcript_internal_video', array( $this, 'get_transcript_internal_video' ) );
		add_action( 'wp_ajax_get_video_info', array( $this, 'get_video_info' ) );
	}

	/**
	 * Recupera le trascrizioni per il video yt e le ritorna.
	 */
	public function get_transcript_yt() {
		if ( ! isset( $_POST['urlVideo'] ) || ! isset( $_POST['urlSubtitle'] ) || ! isset( $_POST['_ajax_nonce'] ) || ! isset( $_POST['videoTitle'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'ear2words' ) );
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$url_video    = sanitize_text_field( wp_unslash( $_POST['urlVideo'] ) );
		$url_subtitle = sanitize_text_field( wp_unslash( $_POST['urlSubtitle'] ) );
		$video_title  = sanitize_text_field( wp_unslash( $_POST['videoTitle'] ) );

		$url_parts    = wp_parse_url( $url_video );
		$allowed_urls = array(
			'www.youtube.com',
			'www.youtu.be',
		);
		if ( ! in_array( $url_parts['host'], $allowed_urls, true ) ) {
			wp_send_json_error( __( 'Url not a valid youtube url', 'ear2words' ) );
		}
		$url_subtitle_parts    = wp_parse_url( $url_subtitle );
		$query_subtitle_params = array();
		parse_str( $url_subtitle_parts['query'], $query_subtitle_params );
		$lang = $query_subtitle_params['lang'];

		$query_video_params = array();
		parse_str( $url_parts['query'], $query_video_params );
		$id_video = $query_video_params['v'] . $lang;

		$data_posts = $this->get_data_transcript( $id_video, 'transcript_post_type' );
		if ( $data_posts ) {
			wp_send_json_success( $data_posts );
		}
		$video_source  = new YouTube();
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
		$transcript = $video_source->get_subtitle_to_url( $url_subtitle, $id_video, $video_title );
		if ( ! $transcript ) {
			wp_send_json_error( __( 'Transcript not avaiable for this video.', 'ear2words' ) );
		}
		wp_send_json_success( $transcript );
	}
	/**
	 * Recupera le informazioni del video.
	 */
	public function get_video_info() {
		if ( ! isset( $_POST['url'] ) || ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'ear2words' ) );
		}
		$url_video = sanitize_text_field( wp_unslash( $_POST['url'] ) );
		$nonce     = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$url_parts    = wp_parse_url( $url_video );
		$allowed_urls = array(
			'www.youtube.com',
			'www.youtu.be',
		);
		if ( ! array_key_exists( 'host', $url_parts ) || ! in_array( $url_parts['host'], $allowed_urls, true ) ) {
			wp_send_json_error( __( 'Url not a valid youtube url', 'ear2words' ) );
		}
		$query_params = array();
		parse_str( $url_parts['query'], $query_params );
		if ( ! array_key_exists( 'v', $query_params ) ) {
			wp_send_json_error( __( 'Url not a valid youtube url', 'ear2words' ) );
		}
		$id_video     = $query_params['v'];
		$get_info_url = "https://www.youtube.com/get_video_info?video_id=$id_video";

		$file_info = array();

		$response = wp_remote_get( $get_info_url );
		$file     = wp_remote_retrieve_body( $response );

		parse_str( $file, $file_info );
		$title_video = json_decode( $file_info['player_response'] )->videoDetails->title;
		$languages   = json_decode( $file_info['player_response'] )->captions->playerCaptionsTracklistRenderer->captionTracks;
		$video_info  = array(
			'languages' => $languages,
			'title'     => $title_video,
		);
		wp_send_json_success( $video_info );
	}
	/**
	 * Recupera le trascrizioni per il video interno e le ritorna.
	 */
	public function get_transcript_internal_video() {
		if ( ! isset( $_POST['id'] ) || ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'ear2words' ) );
		}
		$nonce    = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		$id_video = sanitize_text_field( wp_unslash( $_POST['id'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$args  = array(
			'post_type'      => 'transcript',
			'posts_per_page' => 1,
			'meta_key'       => 'ear2words_transcript',
			'meta_value'     => $id_video,
		);
		$posts = get_posts( $args );
		if ( empty( $posts ) ) {
			wp_send_json_error( __( 'Error, Transcription not found', 'ear2words' ) );
		}
		wp_send_json_success( $posts[0]->ID );
	}

	/**
	 * Get transcript.
	 */
	public function get_transcript() {
		// phpcs:disable
		if ( ! isset( $_POST['url'] ) || ! isset( $_POST['source'] ) || ! isset( $_POST['from'] ) ) {
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

		if ( ! $transcript ) {
			wp_send_json_error( __( 'Transcript not avaiable for this video.', 'ear2words' ) );
		}

		wp_send_json_success( $transcript );
	}
	/**
	 * Recupera i dati se il post esiste e li ritorna.
	 *
	 * @param int    $id_video id univoco del video.
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
