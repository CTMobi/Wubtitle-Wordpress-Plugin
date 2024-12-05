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
use Wubtitle\Core\Rephrase;

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
		$rephrase_helper = new Rephrase();
		$rephrase_info   = $rephrase_helper->get_rephrase_info( $id_video );

		if ( empty( $rephrase_info ) || ! isset( $rephrase_info->title ) ) {
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

		$args = array(
			'post_type'              => 'rephrase',
			'posts_per_page'         => 1,
			'meta_key'               => 'wubtitle_rephrase',
			'meta_value'             => $id_video,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'fields'                 => 'ids',
		);
		// @phpstan-ignore-next-line
		$posts_exist = get_posts( $args );

		$trascript_post_id = empty( $posts_exist ) ?
			$this->create_rephrase_post( $rephrase_info->title, $rephrase, $id_video ) :
			$this->update_rephrase_post( $posts_exist[0], $rephrase );

		if ( is_wp_error( $trascript_post_id ) ) {
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

		$rephrase_helper->update_rephrase_info( $id_video, array( 'status' => 'draft' ) );

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
	 * Create new rephrase post.
	 *
	 * @param string $title video title.
	 * @param string $rephrase rephrase text.
	 * @param string $id_video video_id or id_attachment.
	 * @return int|\WP_Error
	 */
	public function create_rephrase_post( $title, $rephrase, $id_video ) {

		$trascript_post_elements = array(
			'post_title'   => $title,
			'post_content' => $rephrase,
			'post_status'  => 'publish',
			'post_type'    => 'rephrase',
			'meta_input'   => array(
				'wubtitle_rephrase' => $id_video,
			),
		);

		return wp_insert_post( $trascript_post_elements );
	}

	/**
	 * Update rephrase post.
	 *
	 * @param int    $post_id post ID.
	 * @param string $rephrase rephrase text.
	 * @return int|\WP_Error
	 */
	public function update_rephrase_post( $post_id, $rephrase ) {

		$postarr = array(
			'ID'           => $post_id,
			'post_content' => $rephrase,
		);

		return wp_update_post( $postarr );
	}
}
