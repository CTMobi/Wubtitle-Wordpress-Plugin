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
		$attachment_id       = $this->factory()->attachment->create( $attachment_data );
		$this->attachment_id = $attachment_id;

		// Create a custom table.
		global $wpdb;
		$table_name      = $wpdb->prefix . 'wubtitle_rephrase_info';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            id_video varchar(50) NOT NULL,
            status varchar(15) NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY id_video (id_video)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Add testing data.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table_name,
			array(
				'title'    => 'Test Title 2',
				'id_video' => 'sd8w2c6v4q75',
				'status'   => 'pending',
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	public function tearDown(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wubtitle_rephrase_info';
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', array( $table_name ) ) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChangetearDown
		parent::tearDown();
	}

	/**
	 * Test endpoint callback for storing rephrase with valid datas.
	 */
	public function test_valid_data() {
		$request_data = array(
			'data' => array(
				'url'     => 'https://raw.githubusercontent.com/WordPress/WordPress/refs/heads/master/license.txt',
				'videoId' => 'sd8w2c6v4q75',
			),
		);
		$request      = new WP_REST_Request();
		$request->set_default_params( $request_data );
		$response          = $this->instance->get_rephrase( $request );
		$response_data = $response->get_data();
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
		$response_data = $response->get_data();
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
		$response_data = $response->get_data();
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
		$response_data = $response->get_data();
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
     * Test the endpoint callback for storing a rephrase with a valid attachmentId that is not present in the info table.
	 */
	public function test_valid_attachment_info_not_found() {
		$request_data = array(
			'data' => array(
				'url'          => 'https://raw.githubusercontent.com/WordPress/WordPress/refs/heads/master/license.txt',
				'attachmentId' => $this->attachment_id,
			),
		);
		$request      = new WP_REST_Request();
		$request->set_default_params( $request_data );
		$response          = $this->instance->get_rephrase( $request );
		$response_data = $response->get_data();
		$expected_response = array(
			'errors' => array(
				'status' => '404',
				'title'  => 'ID not found',
				'source' => 'ID not found',
			),
		);
		// Check callback response.
		$this->assertEqualSets( $expected_response, $response_data );
	}
}
