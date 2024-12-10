<?php
/**
 * In this file are created new endpoints for the rephrases
 *
 * @author     Damiano Di Pietro
 * @since      1.2.5
 * @package    Wubtitle\Api
 */

namespace Wubtitle\Api;

use WP_REST_Response;
use Wubtitle\Helpers;

/**
 * This class manages file storage.
 */
class ApiStoreRephrase {
	/**
	 * Instance of class helpers.
	 *
	 * @var mixed
	 */
	private $helpers;

	/**
	 * Init class action.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'rest_api_init', array( $this, 'register_rephrase_routes' ) );
		$this->helpers = new Helpers();
	}

	/**
	 * Creates new REST routes.
	 *
	 * @return void
	 */
	public function register_rephrase_routes() {
		// Endpoint to store the rephrase.
		register_rest_route(
			'wubtitle/v1',
			'/store-rephrase',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'get_rephrase' ),
				'permission_callback' => function ( $request ) {
					return $this->helpers->authorizer( $request );
				},
			)
		);
	}

	/**
	 * Gets the rephrase text and link it to a video_id or attachment_id.
	 *
	 * @param \WP_REST_Request $request request values.
	 * @return WP_REST_Response
	 */
	public function get_rephrase( $request ) {
		$params       = $request->get_param( 'data' );
		$rephrase_url = $params['url'] ?? '';

		if ( empty( $rephrase_url ) ) {
			$error = array(
				'errors' => array(
					'status' => '404',
					'title'  => 'Invalid URL',
					'source' => 'Invalid URL',
				),
			);

			$response = new WP_REST_Response( $error );

			$response->set_status( 404 );

			return $response;
		}

		$rephrase_response = wp_remote_get( $rephrase_url );
		$rephrase          = wp_remote_retrieve_body( $rephrase_response );

		// Internal Video.
		if ( isset( $params['attachmentId'] ) && ! empty( $params['attachmentId'] ) && ! empty( get_post( $params['attachmentId'] ) ) ) {
			return $this->save_post_rephrase( $rephrase, $params['attachmentId'] );
		}
		// External Video.
		if ( isset( $params['videoId'] ) && ! empty( $params['videoId'] ) ) {
			return $this->save_post_rephrase( $rephrase, $params['videoId'] );
		}

		$error = array(
			'errors' => array(
				'status' => '404',
				'title'  => 'Invalid IDs',
				'source' => 'Invalid IDs',
			),
		);

		$response = new WP_REST_Response( $error );

		$response->set_status( 404 );

		return $response;
	}

	/**
	 * Save rephrase post.
	 *
	 * @param string $rephrase rephrase text.
	 * @param string $id_video video_id or id_attachment.
	 * @return WP_REST_Response
	 */
	public function save_post_rephrase( $rephrase, $id_video ) {

		$args = array(
			'post_type'              => 'rephrase',
			'posts_per_page'         => 1,
			'meta_key'               => 'wubtitle_rephrase_id',
			'meta_value'             => $id_video,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		);
		// @phpstan-ignore-next-line
		$rephrase_post = get_posts( $args );

		if ( empty( $rephrase_post ) ) {
			$error = array(
				'errors' => array(
					'status' => '404',
					'title'  => 'ID not found',
					'source' => 'ID not found',
				),
			);

			$response = new WP_REST_Response( $error );

			$response->set_status( 404 );

			return $response;
		}

		$rephrase_post_id = $this->update_rephrase_post( $rephrase_post[0], $rephrase );

		if ( is_wp_error( $rephrase_post_id ) ) {
			$error = array(
				'errors' => array(
					'status' => '404',
					'title'  => 'Rephrase not saved',
					'source' => 'Rephrase not saved',
				),
			);

			$response = new WP_REST_Response( $error );

			$response->set_status( 404 );

			return $response;
		}

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
	 * Update rephrase post.
	 *
	 * @param \WP_Post $rephrase_post post ID.
	 * @param string   $rephrase rephrase text.
	 * @return int|\WP_Error
	 */
	public function update_rephrase_post( $rephrase_post, $rephrase ) {

		$postarr = array(
			'ID'           => $rephrase_post->ID,
			'post_content' => $rephrase,
			'meta_input'   => array(
				'wubtitle_rephrase_status' => 'draft',
			),
		);

		return wp_update_post( $postarr );
	}
}
