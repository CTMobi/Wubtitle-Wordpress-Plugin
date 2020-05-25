<?php
/**
 * This file describes handle operation on subtitle.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Ear2Words\Core
 */

namespace Ear2Words\Core\Sources;

/**
 * This class handle subtitles.
 */
class YouTube implements \Ear2Words\Core\VideoSource {

	/**
	 * Effettua la chiamata all'endpoint.
	 *
	 * @param string $id_video il body della richiesta da inviare.
	 */
	public function send_job_to_backend( $id_video ) {
		$response = wp_remote_post(
			ENDPOINT . 'job/create',
			array(
				'method'  => 'POST',
				'headers' => array(
					'licenseKey'   => get_option( 'ear2words_license_key' ),
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
	 * Recupera la trascrizione.
	 *
	 * @param string $url_subtitle url sottotitoli youtube.
	 * @param string $id_video id video.
	 * @param string $title_video titolo video.
	 * @param string $from da dove parte la richiesta.
	 */
	public function get_subtitle_to_url( $url_subtitle, $id_video, $title_video, $from = '' ) {
		if ( '' === $url_subtitle ) {
			return false;
		}
		$url_subtitle = $url_subtitle . '&fmt=json3';
		$response     = wp_remote_get( $url_subtitle );
		$text         = '';
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
	 * Recupera la trascrizioni.
	 *
	 * @param string $id_video id del video youtube.
	 * @param string $from post type dal quale viene fatta la richiesta.
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
	 * Esegue la chiamata e poi recupera le trascrizioni.
	 *
	 * @param array $caption_tracks array di oggetti trascrizioni.
	 */
	public function find_url( $caption_tracks ) {
		$url = '';
		foreach ( $caption_tracks as  $track ) {
			if ( isset( $track->kind ) && 'asr' === $track->kind ) {
				// phpcs:disable
				// phpcs segnala "Object property baseUrl is not in valid snake_case format", ma è un oggetto ottenuto da youtube.
				$url = $track->baseUrl . '&fmt=json3&xorb=2&xobt=3&xovt=3';
				// phpcs:enable
			}
		}
		return $url;
	}

	/**
	 * Esegue la chiamata e poi recupera le trascrizioni.
	 *
	 * @param string $url_video url del video youtube.
	 * @param string $from post type dal quale viene fatta la richiesta.
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
			'400' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'ear2words' ),
			'401' => __( 'An error occurred while creating the transcriptions. Please try again in a few minutes', 'ear2words' ),
			'403' => __( 'Unable to create transcriptions. Invalid product license', 'ear2words' ),
			'500' => __( 'Could not contact the server', 'ear2words' ),
			'429' => __( 'Error, no more video left for your subscription plan', 'ear2words' ),
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
				'data'    => __( 'Transcript not avaiable for this video.', 'ear2words' ),
			);
			return $response;
		}

		$response = array(
			'success' => true,
			'data'    => $response_subtitle,
		);

		return $response;
	}
}
