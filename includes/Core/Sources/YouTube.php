<?php
/**
 * This file describes handle operation on subtitle.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Wubtitle\Core
 */

namespace Wubtitle\Core\Sources;

/**
 * This class handle subtitles.
 */
class YouTube implements \Wubtitle\Core\VideoSource {

	/**
	 * Sends job to backend endpoint.
	 *
	 * @param string $id_video id video youtube.
	 * @return array<string>|\WP_Error
	 */
	public function send_job_to_backend( $id_video ) {
		$response = wp_remote_post(
			WUBTITLE_ENDPOINT . 'job/create',
			array(
				'method'  => 'POST',
				'headers' => array(
					'licenseKey'   => get_option( 'wubtitle_license_key' ),
					'domainUrl'    => get_site_url(),
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode(
					array(
						'source' => 'YOUTUBE',
						'data'   => array(
							'youtubeId' => $id_video,
						),
					)
				),
			)
		);
		return $response;
	}

	/**
	 * Gets the trascription.
	 *
	 * @param string $url_subtitle url youtube subtitle.
	 * @param string $id_video id video.
	 * @param string $title_video video title.
	 * @param string $from where the request starts.
	 * @return bool|string|int|\WP_Error
	 */
	public function get_subtitle_to_url( $url_subtitle, $id_video, $title_video, $from = '' ) {
		if ( empty( $url_subtitle ) ) {
			return false;
		}
		$url_subtitle = $url_subtitle . '&fmt=json3';
		$response     = wp_remote_get( $url_subtitle );
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$text = '';
		foreach ( json_decode( $response['body'] )->events as $event ) {
			if ( isset( $event->segs ) ) {
				foreach ( $event->segs as $seg ) {
					$text .= $seg->utf8;
				}
			}
		}
		$text           = str_replace( "\n", ' ', $text );
		$trascript_post = array(
			'post_title'   => $title_video,
			'post_content' => $text,
			'post_type'    => 'transcript',
			'post_status'  => 'publish',
			'meta_input'   => array(
				'_video_id'          => $id_video,
				'_transcript_source' => 'youtube',
			),
		);
		$id_transcript  = wp_insert_post( $trascript_post );

		return 'default_post_type' === $from ? $id_transcript : $text;
	}

	/**
	 * Gets the transcription.
	 *
	 * @param string $id_video id youtube video.
	 * @param string $from where the request starts.
	 * @return bool|string|int|\WP_Error
	 */
	public function get_subtitle( $id_video, $from ) {
		$get_info_url = "https://www.youtube.com/get_video_info?video_id=$id_video";

		$file_info = array();

		$response = wp_remote_get( $get_info_url );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		$file = wp_remote_retrieve_body( $response );

		parse_str( $file, $file_info );

		$title_video    = json_decode( $file_info['player_response'] )->videoDetails->title;
		$caption_tracks = json_decode( $file_info['player_response'] )->captions->playerCaptionsTracklistRenderer->captionTracks;

		$url = $this->find_url( $caption_tracks );

		if ( '' === $url ) {
			return false;
		}

		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return '';
		}

		$text = '';

		foreach ( json_decode( $response['body'] )->events as $event ) {
			if ( isset( $event->segs ) ) {
				foreach ( $event->segs as $seg ) {
					$text .= $seg->utf8;
				}
			}
		}

		$text = str_replace( "\n", ' ', $text );

		if ( 'default_post_type' === $from ) {
			$text           = '<!-- wp:paragraph --><p>' . $text . '</p><!-- /wp:paragraph -->';
			$trascript_post = array(
				'post_title'   => sanitize_text_field( $title_video ),
				'post_content' => $text,
				'post_type'    => 'transcript',
				'post_status'  => 'publish',
				'meta_input'   => array(
					'_video_id'          => $id_video,
					'_transcript_source' => 'youtube',
				),
			);
			$transcript_id  = wp_insert_post( $trascript_post );
			return $transcript_id;
		}
		return $text;
	}


	/**
	 * Finds the url of auto-generated captions
	 *
	 * @param mixed $caption_tracks array of objects.
	 * @return string
	 */
	public function find_url( $caption_tracks ) {
		$url = '';
		foreach ( $caption_tracks as  $track ) {
			// phpcs:disable
			// phpcs reports "Object property baseUrl is not in valid snake_case format", but it is an object obtained from youtube.
			if ( isset( $track->kind ) && isset( $track->baseUrl ) && 'asr' === $track->kind ) {
				$url = $track->baseUrl . '&fmt=json3&xorb=2&xobt=3&xovt=3';
			}
			// phpcs:enable
		}
		return $url;
	}

	/**
	 * Calls the backend endpoint and then retrieve the transcripts.
	 *
	 * @param string $url_video url of the youtube video.
	 * @param string $from where the request starts.
	 * @return array<string,int|bool|string|\WP_Error>
	 */
	public function send_job_and_get_transcription( $url_video, $from ) {
		$url_parts    = wp_parse_url( $url_video );
		$query_params = array();
		parse_str( $url_parts['query'], $query_params );
		$id_video = $query_params['v'];
		$args     = array(
			'post_type'      => 'transcript',
			'posts_per_page' => 1,
			'meta_key'       => '_video_id',
			'meta_value'     => $id_video,
		);
		$posts    = get_posts( $args );
		if ( ! empty( $posts ) && 'default_post_type' === $from ) {
			$response = array(
				'success' => true,
				'data'    => $posts[0]->ID,
			);
			return $response;
		}

		$response      = $this->send_job_to_backend( $id_video );
		$response_code = wp_remote_retrieve_response_code( $response );
		$message       = array(
			'400' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ),
			'401' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ),
			'403' => __( 'Unable to create transcriptions. Invalid product license', 'wubtitle' ),
			'500' => __( 'Could not contact the server', 'wubtitle' ),
			'429' => __( 'Error, no more video left for your subscription plan', 'wubtitle' ),
		);
		if ( 201 !== $response_code ) {
			$response = array(
				'success' => false,
				'data'    => $message[ $response_code ],
			);
			return $response;
		}

		$response_subtitle = $this->get_subtitle( $id_video, $from );

		if ( ! $response_subtitle ) {
			$response = array(
				'success' => false,
				'data'    => __( 'Transcript not avaiable for this video.', 'wubtitle' ),
			);
			return $response;
		}

		$response = array(
			'success' => true,
			'data'    => $response_subtitle,
		);

		return $response;
	}

	/**
	 * Get youtube video info
	 *
	 * @param array<string> $url_parts parts of url.
	 *
	 * @return array<mixed>|false
	 */
	public function get_video_info( $url_parts ) {
		$query_params = array();
		parse_str( $url_parts['query'], $query_params );
		if ( ! array_key_exists( 'v', $query_params ) ) {
			return false;
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
			return false;
		}
		$title_video = json_decode( $file_info['player_response'] )->videoDetails->title;
		$languages   = json_decode( $file_info['player_response'] )->captions->playerCaptionsTracklistRenderer->captionTracks;
		$video_info  = array(
			'source'    => 'youtube',
			'languages' => $languages,
			'title'     => $title_video,
		);
		return $video_info;
	}

	/**
	 * Get transcript video.
	 *
	 * @param string $id_video id video.
	 * @param string $url_subtitle url video youtube subtitle.
	 * @param string $video_title video title.
	 * @param string $from where the request comes from.
	 *
	 * @return array<mixed>
	 */
	public function get_transcript( $id_video, $url_subtitle, $video_title, $from ) {
		$response      = $this->send_job_to_backend( $id_video );
		$response_code = wp_remote_retrieve_response_code( $response );

		$message = array(
			'400' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ),
			'401' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'wubtitle' ),
			'403' => __( 'Unable to create transcriptions. Invalid product license', 'wubtitle' ),
			'500' => __( 'Could not contact the server', 'wubtitle' ),
			'429' => __( 'Error, no more video left for your subscription plan', 'wubtitle' ),
		);
		if ( 201 !== $response_code ) {
			return array(
				'success' => false,
				'message' => $message[ $response_code ],
			);
		}
		$transcript = $this->get_subtitle_to_url( $url_subtitle, $id_video, $video_title, $from );
		if ( ! $transcript ) {
			return array(
				'success' => false,
				'message' => __( 'Transcript not avaiable for this video.', 'wubtitle' ),
			);
		}
		return array(
			'success' => true,
			'data'    => $transcript,
		);
	}
}
