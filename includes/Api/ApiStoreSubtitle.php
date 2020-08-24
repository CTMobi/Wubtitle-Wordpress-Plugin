<?php
/**
 * In this file is created a new endpoint for file store
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Wubtitle\Api
 */

namespace Wubtitle\Api;

use WP_REST_Response;
use \Firebase\JWT\JWT;
use \download_url;

/**
 * This class manages file storage.
 */
class ApiStoreSubtitle {
	/**
	 * Init class action.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'rest_api_init', array( $this, 'register_store_subtitle_route' ) );
		add_action( 'rest_api_init', array( $this, 'register_error_jobs_route' ) );
	}

	/**
	 * Creates new REST route.
	 *
	 * @return void
	 */
	public function register_store_subtitle_route() {
		register_rest_route(
			'wubtitle/v1',
			'/store-subtitle',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'auth_and_get_subtitle' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * JWT authentication.
	 *
	 * @param \WP_REST_Request $request request values.
	 * @return WP_REST_Response
	 */
	public function auth_and_get_subtitle( $request ) {
		$headers        = $request->get_headers();
		$jwt            = $headers['jwt'][0];
		$params         = $request->get_param( 'data' );
		$db_license_key = get_option( 'wubtitle_license_key' );
		try {
			JWT::decode( $jwt, $db_license_key, array( 'HS256' ) );
		} catch ( \Exception $e ) {
			$error = array(
				'errors' => array(
					'status' => '403',
					'title'  => 'Authentication Failed',
					'source' => $e->getMessage(),
				),
			);

			$response = new WP_REST_Response( $error );

			$response->set_status( 403 );

			return $response;
		}
		return $this->get_subtitle( $params );
	}

	/**
	 * Gets the subtitle file, save it and add video posts meta.
	 *
	 * @param array<mixed> $params file parameters.
	 * @return WP_REST_Response
	 */
	public function get_subtitle( $params ) {
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		$url            = $params['url'];
		$transcript_url = $params['transcript'];
		$file_name      = explode( '?', basename( $url ) )[0];
		$id_attachment  = $params['attachmentId'];
		$temp_file      = download_url( $url );
		update_option( 'wubtitle_seconds_done', $params['duration'] );
		update_option( 'wubtitle_jobs_done', $params['jobs'] );

		if ( is_wp_error( $temp_file ) ) {
			$error = array(
				'errors' => array(
					'status' => '404',
					'title'  => 'Invalid URL',
					'source' => 'URL not found',
				),
			);

			$response = new WP_REST_Response( $error );

			$response->set_status( 404 );

			return $response;
		}

		$file = array(
			'name'     => $file_name,
			'type'     => 'text/vtt',
			'tmp_name' => $temp_file,
			'error'    => 0,
			'size'     => filesize( $temp_file ),
		);

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . '/wp-admin/includes/image.php';
		}
		$id_file_vtt = \media_handle_sideload( $file, 0 );

		if ( is_wp_error( $id_file_vtt ) ) {
			$error = array(
				'errors' => array(
					'status' => '500',
					'title'  => 'Download Failed',
					'source' => 'Download Failed',
				),
			);

			$response = new WP_REST_Response( $error );

			$response->set_status( 500 );

			return $response;
		}

		update_post_meta( $id_attachment, 'wubtitle_subtitle', $id_file_vtt );
		update_post_meta( $id_attachment, 'wubtitle_status', 'draft' );
		update_post_meta( $id_file_vtt, 'is_subtitle', 'true' );

		$transcript_response = wp_remote_get( $transcript_url );

		$transcript = wp_remote_retrieve_body( $transcript_response );

		$this->add_post_trascript( $transcript, $id_attachment );

		$message = array(
			'message' => array(
				'status' => '200',
				'title'  => 'Success',
				'source' => 'File received',
			),
		);

		$response = new WP_REST_Response( $message );

		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Generates post transcription.
	 *
	 * @param string $transcript transcription text.
	 * @param int    $id_attachment video id.
	 * @return void
	 */
	public function add_post_trascript( $transcript, $id_attachment ) {
		$related_attachment = get_post( $id_attachment );
		if ( empty( $related_attachment ) ) {
			return;
		}
		$trascript_post = array(
			'post_title'   => $related_attachment->post_title,
			'post_content' => $transcript,
			'post_status'  => 'publish',
			'post_type'    => 'transcript',
			'meta_input'   => array(
				'wubtitle_transcript' => $id_attachment,
			),
		);
		wp_insert_post( $trascript_post );
	}

	/**
	 * Creates a new endpoint to manage filed jobs.
	 *
	 * @return void
	 */
	public function register_error_jobs_route() {
		register_rest_route(
			'wubtitle/v1',
			'/error-jobs',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_jobs_failed' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}
	/**
	 * Gets failed jobs.
	 *
	 * @param \WP_REST_Request $request request values.
	 * @return WP_REST_Response|array<array<string>>
	 */
	public function get_jobs_failed( $request ) {
		$params   = $request->get_param( 'data' );
		$job_id   = $params['jobId'];
		$args     = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'meta_key'       => 'wubtitle_job_uuid',
			'meta_value'     => $job_id,
		);
		$job_meta = get_posts( $args );
		if ( empty( $job_meta[0] ) ) {
			$response = new WP_REST_Response(
				array(
					'errors' => array(
						'status' => '404',
						'title'  => 'Invalid Job uuid',
					),
				)
			);

			$response->set_status( 404 );

			return $response;
		}

		$id_attachment = $job_meta[0]->ID;
		update_post_meta( $id_attachment, 'wubtitle_status', 'error' );
		$message = array(
			'data' => array(
				'status' => '200',
				'title'  => 'Success',
			),
		);

		return $message;
	}
}
