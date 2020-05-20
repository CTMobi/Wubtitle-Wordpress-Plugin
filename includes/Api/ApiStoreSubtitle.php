<?php
/**
 * Questo file crea un nuovo endpoint per lo store del file .
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Ear2Words\Api
 */

namespace Ear2Words\Api;

use WP_REST_Response;
use \Firebase\JWT\JWT;
use \download_url;

/**
 * Questa classe gestisce lo store dei file vtt.
 */
class ApiStoreSubtitle {
	/**
	 * Init class action.
	 */
	public function run() {
		add_action( 'rest_api_init', array( $this, 'register_store_subtitle_route' ) );
		add_action( 'rest_api_init', array( $this, 'register_error_jobs_route' ) );
	}

	/**
	 * Crea nuova rotta REST.
	 */
	public function register_store_subtitle_route() {
		register_rest_route(
			'ear2words/v1',
			'/store-subtitle',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'auth_and_get_subtitle' ),
			)
		);
	}

	/**
	 * Autenticazione JWT.
	 *
	 * @param array $request valori della richiesta.
	 */
	public function auth_and_get_subtitle( $request ) {
		$headers        = $request->get_headers();
		$jwt            = $headers['jwt'][0];
		$params         = $request->get_param( 'data' );
		$db_license_key = get_option( 'ear2words_license_key' );
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
	 * Ottiene il file dei sottotitoli e lo salva, inoltre aggiunge dei post meta al video.
	 *
	 * @param array $params parametri del file.
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
		update_option( 'ear2words_seconds_done', $params['duration'] );
		update_option( 'ear2words_jobs_done', $params['jobs'] );

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

		update_post_meta( $id_attachment, 'ear2words_subtitle', $id_file_vtt );
		update_post_meta( $id_attachment, 'ear2words_status', 'draft' );
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
	 * Genera post trascrizione.
	 *
	 * @param string $transcript testo della trascrizione.
	 * @param string $id_attachment id del video.
	 */
	public function add_post_trascript( $transcript, $id_attachment ) {
		$related_attachment = get_post( $id_attachment );
		$trascript_post     = array(
			'post_title'   => $related_attachment->post_title,
			'post_content' => $transcript,
			'post_status'  => 'publish',
			'post_type'    => 'transcript',
			'meta_input'   => array(
				'ear2words_transcript' => $id_attachment,
			),
		);
		wp_insert_post( $trascript_post );
	}

	/**
	 * Crea un nuovo endpoint per ricevere i jobs andati in errori.
	 */
	public function register_error_jobs_route() {
		register_rest_route(
			'ear2words/v1',
			'/error-jobs',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_jobs_failed' ),
			)
		);
	}
	/**
	 * Recupera i job falliti.
	 *
	 * @param array $request valori della richiesta.
	 */
	public function get_jobs_failed( $request ) {
		$params   = $request->get_param( 'data' );
		$job_id   = $params['jobId'];
		$args     = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'meta_key'       => 'ear2words_job_uuid',
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
		update_post_meta( $id_attachment, 'ear2words_status', 'error' );
		$message = array(
			'data' => array(
				'status' => '200',
				'title'  => 'Success',
			),
		);

		return $message;
	}
}
