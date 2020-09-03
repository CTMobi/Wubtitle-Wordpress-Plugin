<?php
/**
 * In this file is implemented the logic to get transcripts for videos.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Wubtitles\Api
 */

namespace Wubtitle\Api;

use \Wubtitle\Core\Sources\YouTube;
use \Wubtitle\Core\Sources\Vimeo;

/**
 * Manages ajax and sends http request.
 */
class ApiGetTranscript {

	/**
	 * Init class action.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'wp_ajax_get_transcript', array( $this, 'get_transcript' ) );
		add_action( 'wp_ajax_get_transcript_embed', array( $this, 'get_transcript_embed' ) );
		add_action( 'wp_ajax_get_transcript_internal_video', array( $this, 'get_transcript_internal_video' ) );
		add_action( 'wp_ajax_get_video_info', array( $this, 'get_video_info' ) );
	}

	/**
	 * Gets youtube video transcription and returns it.
	 *
	 * @return void
	 */
	public function get_transcript_embed() {
		if ( ! isset( $_POST['urlVideo'], $_POST['subtitle'], $_POST['_ajax_nonce'], $_POST['videoTitle'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ) );
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$url_video   = sanitize_text_field( wp_unslash( $_POST['urlVideo'] ) );
		$subtitle    = sanitize_text_field( wp_unslash( $_POST['subtitle'] ) );
		$video_title = sanitize_text_field( wp_unslash( $_POST['videoTitle'] ) );

		$from = 'transcript_post_type';
		if ( isset( $_POST['from'] ) ) {
			$from = sanitize_text_field( wp_unslash( $_POST['from'] ) );
		}

		$url_parts    = wp_parse_url( $url_video );
		$allowed_urls = array(
			'www.youtube.com',
			'www.youtu.be',
			'vimeo.com',
		);
		if ( ! in_array( $url_parts['host'], $allowed_urls, true ) ) {
			wp_send_json_error( __( 'Url not a valid youtube or vimeo url', 'wubtitle' ) );
		}

		if ( 'vimeo.com' === $url_parts['host'] ) {
			$video_source = new Vimeo();
			$id_video     = basename( $url_parts['path'] );
			$data_posts   = $this->get_data_transcript( $id_video, $from );
			if ( $data_posts ) {
				wp_send_json_success( $data_posts );
			}
			$transcript = $video_source->get_transcript( $id_video, $video_title, $from, $subtitle );
			if ( ! $transcript['success'] ) {
				wp_send_json_error( $transcript['message'] );
			}
			wp_send_json_success( $transcript['data'] );
		}

		$url_subtitle_parts    = wp_parse_url( $subtitle );
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
		$video_source = new YouTube();
		$transcript   = $video_source->get_transcript( $id_video, $video_title, $from, $subtitle );
		if ( ! $transcript['success'] ) {
			wp_send_json_error( $transcript['message'] );
		}
		wp_send_json_success( $transcript['data'] );
	}


	/**
	 * Gets video info e returns it.
	 *
	 * @return void
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
			'vimeo.com',
		);
		if ( ! array_key_exists( 'host', $url_parts ) || ! in_array( $url_parts['host'], $allowed_urls, true ) ) {
			wp_send_json_error( __( 'Url not a valid youtube or vimeo url', 'wubtitle' ) );
		}
		if ( 'vimeo.com' === $url_parts['host'] ) {
			$video_source = new Vimeo();
			$response     = $video_source->get_video_info( $url_parts );
			if ( ! $response['success'] ) {
				wp_send_json_error( $response['message'] );
			}
			wp_send_json_success( $response );
		}
		$video_source = new Youtube();
		$response     = $video_source->get_video_info( $url_parts );
		if ( ! $response ) {
			wp_send_json_error( __( 'Url not a valid youtube url', 'wubtitle' ) );
		}
		wp_send_json_success( $response );
	}
	/**
	 * Gets internal video transcriptions and returns it.
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public function get_transcript() {
		if ( ! isset( $_POST['url'], $_POST['source'], $_POST['from'], $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ) );
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$url_video = sanitize_text_field( wp_unslash( $_POST['url'] ) );
		$source    = sanitize_text_field( wp_unslash( $_POST['source'] ) );
		$from      = sanitize_text_field( wp_unslash( $_POST['from'] ) );

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
	 * @param string $id_video unique id of the video.
	 * @param string $from indicates the caller source.
	 * @return bool|int|string
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
