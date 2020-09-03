<?php
/**
 * This file describes Vimeo operation.
 *
 * @author     Alessio Catania
 * @since      1.0.7
 * @package    Wubtitle\Core
 */

namespace Wubtitle\Core\Sources;

use Wubtitle\Utils\VimeoHelper;

/**
 * This class handle subtitles.
 */
class Vimeo implements \Wubtitle\Core\VideoSource {

	/**
	 * Sends job to backend endpoint.
	 *
	 * @param string $id_video id video youtube.
	 * @return array<mixed>|\WP_Error
	 */
	public function send_job_to_backend( $id_video ) {
		$response = wp_remote_post(
			WUBTITLE_ENDPOINT . 'vimeo/create',
			array(
				'method'  => 'POST',
				'headers' => array(
					'licenseKey'   => get_option( 'wubtitle_license_key' ),
					'domainUrl'    => get_site_url(),
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode(
					array(
						'source' => 'VIMEO',
						'data'   => array(
							'vimeoId' => $id_video,
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
	 * @param string $id_video id video.
	 * @param string $title_video video title.
	 * @param string $text transcription content.
	 * @param string $from where the request starts.
	 * @return bool|string|int|\WP_Error
	 */
	public function insert_transcript( $id_video, $title_video, $text, $from = '' ) {
		$trascript_post = array(
			'post_title'   => $title_video,
			'post_content' => $text,
			'post_type'    => 'transcript',
			'post_status'  => 'publish',
			'meta_input'   => array(
				'_video_id'          => $id_video,
				'_transcript_source' => 'vimeo',
			),
		);
		$id_transcript  = wp_insert_post( $trascript_post );

		return 'default_post_type' === $from ? $id_transcript : $text;
	}

	/**
	 * Get transcript video.
	 *
	 * @param string $id_video id video.
	 * @param string $video_title video title.
	 * @param string $from where the request comes from.
	 *
	 * @return array<mixed>
	 */
	public function get_transcript( $id_video, $video_title, $from ) {
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
			return array(
				'success' => false,
				'message' => $message[ $response_code ],
			);
		}
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$text          = $response_body->data->text ?? false;

		$transcript = $this->insert_transcript( $id_video, $video_title, $text, $from );
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

	/**
	 * Get youtube video info
	 *
	 * @param array<string> $url_parts parts of url.
	 *
	 * @return array<mixed>
	 */
	public function get_video_info( $url_parts ) {
		$video_id    = basename( $url_parts['path'] );
		$body        = array(
			'data' => array(
				'id' => $video_id,
			),
		);
		$license_key = get_option( 'wubtitle_license_key' );
		if ( empty( $license_key ) ) {
			return array(
				'success' => false,
				'message' => __( 'License key is missing', 'wubtitle' ),
			);
		}
		$response      = wp_remote_post(
			WUBTITLE_ENDPOINT . 'vimeo/info',
			array(
				'method'  => 'POST',
				'headers' => array(
					'licenseKey'   => $license_key,
					'domainUrl'    => get_site_url(),
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$code_response = ! is_wp_error( $response ) ? wp_remote_retrieve_response_code( $response ) : '500';
		$message       = array(
			'400' => __( 'An error occurred. Please try again in a few minutes', 'wubtitle' ),
			'401' => __( 'An error occurred. Please try again in a few minutes', 'wubtitle' ),
			'403' => __( 'Access denied', 'wubtitle' ),
			'500' => __( 'Could not contact the server', 'wubtitle' ),
			''    => __( 'Could not contact the server', 'wubtitle' ),
		);
		if ( 200 !== $code_response ) {
			return array(
				'success' => false,
				'message' => $message[ $code_response ],
			);
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$languages     = array_reduce( $response_body->data->languages, array( $this, 'languages_reduce' ), array() );
		$url_oembed    = 'https://vimeo.com/api/oembed.json?url=https://vimeo.com/' . $video_id;
		$video_info    = wp_remote_get( $url_oembed );

		$video_info_body = json_decode( wp_remote_retrieve_body( $video_info ) );

		$response = array(
			'success'   => 'true',
			'source'    => 'vimeo',
			'languages' => $languages,
			'title'     => $video_info_body->title,
		);

		return $response;
	}
	/**
	 * Callback function array_reduce
	 *
	 * @param mixed $accumulator empty array.
	 * @param mixed $item object to reduce.
	 *
	 * @return mixed
	 */
	public function languages_reduce( $accumulator, $item ) {
		$helpers       = new VimeoHelper();
		$languages     = $helpers->get_languages();
		$accumulator[] = array(
			'code' => $item,
			'name' => $languages[ $item ],
		);
		return $accumulator;
	}
}
