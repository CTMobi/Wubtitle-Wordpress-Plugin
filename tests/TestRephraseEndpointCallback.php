<?php
/**
 * Test rephrase store endpoints.
 *
 * @author     Damiano Di Pietro
 * @since      1.2.5
 * @package    Wubtitle\Api
 */

use Wubtitle\Api\ApiStoreRephrase;
use WP_REST_Request;
/**
 * Class TestRephraseEndpointCallback
 *
 * @package Wubtitle
 */

/**
 * Test callback endpoint.
 */
class TestRephraseEndpointCallback extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->instance = new ApiStoreRephrase();

		// Create a video attachment.
		$attachment_data     = array(
			'guid'           => 'http://wordpress01.local/wp-content/uploads/2020/04/video.mp4',
			'post_mime_type' => 'video/mp4',
			'post_title'     => 'video',
			'post_content'   => '',
		);
		$this->attachment_id = $this->factory()->attachment->create( $attachment_data );

		// Add testing data.
		$this->factory->post->create(
			array(
				'post_title'  => 'Title Internal',
				'post_status' => 'publish',
				'post_type'   => 'rephrase',
				'meta_input'  => array(
					'wubtitle_rephrase_id'     => $this->attachment_id,
					'wubtitle_rephrase_status' => 'pending',
				),
			)
		);
		$this->factory->post->create(
			array(
				'post_title'  => 'Title External',
				'post_status' => 'publish',
				'post_type'   => 'rephrase',
				'meta_input'  => array(
					'wubtitle_rephrase_id'     => '5s32d1q1c3av16s',
					'wubtitle_rephrase_status' => 'pending',
				),
			)
		);
	}


	/**
	 * Test endpoint callback for storing rephrase with valid datas (internal video).
	 */
	public function test_valid_data_internal() {
		$request_data = array(
			'data' => array(
				'url'          => 'https://raw.githubusercontent.com/WordPress/WordPress/refs/heads/master/license.txt',
				'attachmentId' => $this->attachment_id,
			),
		);
		$request      = new WP_REST_Request();
		$request->set_default_params( $request_data );
		$response          = $this->instance->get_rephrase( $request );
		$response_data     = $response->get_data();
		$expected_response = array(
			'message' => array(
				'status' => '200',
				'title'  => 'Success',
				'source' => 'File received',
			),
		);
		// Check callback response.
		$this->assertEqualSets( $expected_response, $response_data );
	}

	/**
	 * Test endpoint callback for storing rephrase with valid datas (external video).
	 */
	public function test_valid_data_external() {
		$request_data = array(
			'data' => array(
				'url'     => 'https://raw.githubusercontent.com/WordPress/WordPress/refs/heads/master/license.txt',
				'videoId' => '5s32d1q1c3av16s',
			),
		);
		$request      = new WP_REST_Request();
		$request->set_default_params( $request_data );
		$response          = $this->instance->get_rephrase( $request );
		$response_data     = $response->get_data();
		$expected_response = array(
			'message' => array(
				'status' => '200',
				'title'  => 'Success',
				'source' => 'File received',
			),
		);
		// Check callback response.
		$this->assertEqualSets( $expected_response, $response_data );
	}

	/**
	 * Test endpoint callback for storing rephrase with invalid URL.
	 */
	public function test_invalid_url() {
		$request_data = array(
			'data' => array(
				'url'          => 'https://test?file_name',
				'attachmentId' => '5226',
			),
		);
		$request      = new WP_REST_Request();
		$request->set_default_params( $request_data );
		$response          = $this->instance->get_rephrase( $request );
		$response_data     = $response->get_data();
		$expected_response = array(
			'errors' => array(
				'status' => '404',
				'title'  => 'Invalid URL content',
				'source' => 'Invalid URL content',
			),
		);
		// Check callback response.
		$this->assertEqualSets( $expected_response, $response_data );
	}

	/**
	 * Test endpoint callback for storing rephrase with invalid structure.
	 */
	public function test_invalid_data_structure() {
		$request_data = array(
			'data' => array(
				'url' => 'https://raw.githubusercontent.com/WordPress/WordPress/refs/heads/master/license.txt',
				'ID'  => '5226',
			),
		);
		$request      = new WP_REST_Request();
		$request->set_default_params( $request_data );
		$response          = $this->instance->get_rephrase( $request );
		$response_data     = $response->get_data();
		$expected_response = array(
			'errors' => array(
				'status' => '404',
				'title'  => 'Invalid IDs',
				'source' => 'Invalid IDs',
			),
		);
		// Check callback response.
		$this->assertEqualSets( $expected_response, $response_data );
	}

	/**
	 * Test endpoint callback for storing rephrase with an invalid attachmentId.
	 */
	public function test_invalid_attachment_id() {
		$request_data = array(
			'data' => array(
				'url'          => 'https://raw.githubusercontent.com/WordPress/WordPress/refs/heads/master/license.txt',
				'attachmentId' => '1425368754568985',
			),
		);
		$request      = new WP_REST_Request();
		$request->set_default_params( $request_data );
		$response          = $this->instance->get_rephrase( $request );
		$response_data     = $response->get_data();
		$expected_response = array(
			'errors' => array(
				'status' => '404',
				'title'  => 'Invalid IDs',
				'source' => 'Invalid IDs',
			),
		);
		// Check callback response.
		$this->assertEqualSets( $expected_response, $response_data );
	}
}
