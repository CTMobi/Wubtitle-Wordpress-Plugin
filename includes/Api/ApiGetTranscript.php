<?php
/**
 * This file implements the logic to get transcripts for videos.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Wubtitles\Api
 */

namespace Wubtitle\Api;

use \Wubtitle\Core\Sources\YouTube;

/**
 * Manages ajax and sends http request.
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
	 * Gets yotube video transcription and returns it.
	 */
	public function get_transcript_yt() {
		if ( ! isset( $_POST['urlVideo'], $_POST['urlSubtitle'], $_POST['_ajax_nonce'], $_POST['videoTitle'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ) );
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$url_video    = sanitize_text_field( wp_unslash( $_POST['urlVideo'] ) );
		$url_subtitle = sanitize_text_field( wp_unslash( $_POST['urlSubtitle'] ) );
		$video_title  = sanitize_text_field( wp_unslash( $_POST['videoTitle'] ) );

		$from = 'transcript_post_type';
		if ( isset( $_POST['from'] ) ) {
			$from = sanitize_text_field( wp_unslash( $_POST['from'] ) );
		}

		$url_parts    = wp_parse_url( $url_video );
		$allowed_urls = array(
			'www.youtube.com',
			'www.youtu.be',
		);
		if ( ! in_array( $url_parts['host'], $allowed_urls, true ) ) {
			wp_send_json_error( __( 'Url not a valid youtube url', 'wubtitle' ) );
		}
		$url_subtitle_parts    = wp_parse_url( $url_subtitle );
		$query_subtitle_params = array();
		parse_str( $url_subtitle_parts['query'], $query_subtitle_params );
		$lang = $query_subtitle_params['lang'];

		$query_video_params = array();
		parse_str( $url_parts['query'], $query_video_params );
		$id_video = $query_video_params['v'] . $lang;

		$data_posts = $this->get_data_transcript( $id_video, $from );
		if ( $data_posts ) {
			wp_send_json_success( $data_posts );
		}
		$video_source  = new YouTube();
		$response      = $video_source->send_job_to_backend( $id_video );
		$response_code = wp_remote_retrieve_response_code( $response );

		$message = array(
			'400' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ),
			'401' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ),
			'403' => __( 'Unable to create transcriptions. Invalid product license', 'wubtitle' ),
			'500' => __( 'Could not contact the server', 'wubtitle' ),
			'429' => __( 'Error, no more video left for your subscription plan', 'wubtitle' ),
		);
		if ( 201 !== $response_code ) {
			wp_send_json_error( $message[ $response_code ] );
		}
		$transcript = $video_source->get_subtitle_to_url( $url_subtitle, $id_video, $video_title, $from );
		if ( ! $transcript ) {
			wp_send_json_error( __( 'Transcript not avaiable for this video.', 'wubtitle' ) );
		}
		wp_send_json_success( $transcript );
	}


	/**
	 * Gets video info e returns it.
	 */
	public function get_video_info() {
		if ( ! isset( $_POST['url'] ) || ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ) );
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
			wp_send_json_error( __( 'Url not a valid youtube url', 'wubtitle' ) );
		}
		$query_params = array();
		parse_str( $url_parts['query'], $query_params );
		if ( ! array_key_exists( 'v', $query_params ) ) {
			wp_send_json_error( __( 'Url not a valid youtube url', 'wubtitle' ) );
		}
		$id_video     = $query_params['v'];
		$get_info_url = "https://www.youtube.com/get_video_info?video_id=$id_video";

		$file_info = array();

		$response = wp_remote_get(
			$get_info_url,
			array(
				'headers' => array( 'Accept-Language' => get_locale() ),
			)
		);
		$file     = wp_remote_retrieve_body( $response );

		parse_str( $file, $file_info );
		if ( 'fail' === $file_info['status'] ) {
			wp_send_json_error( __( 'Url not a valid youtube url', 'wubtitle' ) );
		}
		$title_video = json_decode( $file_info['player_response'] )->videoDetails->title;
		$languages   = json_decode( $file_info['player_response'] )->captions->playerCaptionsTracklistRenderer->captionTracks;
		$video_info  = array(
			'languages' => $languages,
			'title'     => $title_video,
		);
		wp_send_json_success( $video_info );
	}
	/**
	 * Gets internal video transcription and returns it.
	 */
	public function get_transcript_internal_video() {
		if ( ! isset( $_POST['id'] ) || ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ) );
		}
		$from = '';
		if ( isset( $_POST['from'] ) ) {
			$from = sanitize_text_field( wp_unslash( $_POST['from'] ) );
		}
		$nonce    = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		$id_video = sanitize_text_field( wp_unslash( $_POST['id'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$args  = array(
			'post_type'      => 'transcript',
			'posts_per_page' => 1,
			'meta_key'       => 'wubtitle_transcript',
			'meta_value'     => $id_video,
		);
		$posts = get_posts( $args );
		if ( empty( $posts ) ) {
			wp_send_json_error( __( 'Error: this video doesn\'t have subtitles yet. It is necessary to generate them to obtain the transcription', 'wubtitle' ) );
		}
		if ( 'classic_editor' === $from ) {
			$response = array(
				'post_title'   => $posts[0]->post_title,
				'post_content' => $posts[0]->post_content,
			);
			wp_send_json_success( $response );
		}
		wp_send_json_success( $posts[0]->ID );
	}

	/**
	 * Get transcript.
	 */
	public function get_transcript() {
		// phpcs:disable
		if ( ! isset( $_POST['url'] ) || ! isset( $_POST['source'] ) || ! isset( $_POST['from'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ) );
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
			wp_send_json_error( __( 'Url not a valid youtube url', 'wubtitle' ) );
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
			'400' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ),
			'401' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ),
			'403' => __( 'Unable to create transcriptions. Invalid product license', 'wubtitle' ),
			'500' => __( 'Could not contact the server', 'wubtitle' ),
			'429' => __( 'Error, no more video left for your subscription plan', 'wubtitle' ),
		);
		if ( 201 !== $response_code ) {
			wp_send_json_error( $message[ $response_code ] );
		}

		$transcript = $video_source->get_subtitle( $id_video, $from );

		if ( ! $transcript ) {
			wp_send_json_error( __( 'Transcript not avaiable for this video.', 'wubtitle' ) );
		}

		wp_send_json_success( $transcript );
	}
	/**
	 * Gets data if post exists and returns it.
	 *
	 * @param int    $id_video unique id of the video.
	 * @param string $from indicates the caller source.
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
