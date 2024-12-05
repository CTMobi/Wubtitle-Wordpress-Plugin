<?php
/**
 * Test rephrase store endpoints.
 *
 * @author     Damiano Di Pietro
 * @since      1.2.5
 * @package    Wubtitle\Api
 */

use Wubtitle\Core\Rephrase;

/**
 * Class TestRephraseEndpointCallback
 *
 * @package Wubtitle
 */

/**
 * Test callback endpoint.
 */
class TestRephrase extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->instance = new Rephrase();

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

		// Manually adding test data.
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
	 * Test reading rephrase information from the DB.
	 */
	public function test_read_data() {
		$id_video       = 'sd8w2c6v4q75';
		$db_data        = $this->instance->get_rephrase_info( $id_video );
		$expected_title = 'Test Title 2';
		// Check callback response.
		$this->assertEquals( $expected_title, $db_data->title );
	}

	/**
	 * Test adding rephrase information to the DB.
	 */
	public function test_insert_data() {
		$data = array(
			'title'    => 'Test Insert',
			'id_video' => '22',
			'status'   => 'pending',
		);
		$this->instance->insert_rephrase_info( $data );
		$db_data        = $this->instance->get_rephrase_info( '22' );
		$expected_title = 'Test Insert';
		// Check callback response.
		$this->assertEquals( $expected_title, $db_data->title );
	}

	/**
	 * Test updating rephrase information in the DB.
	 */
	public function test_update_data() {
		$id_video = 'sd8w2c6v4q75';
		$data     = array(
			'status' => 'draft',
		);
		$this->instance->update_rephrase_info( $id_video, $data );
		$db_data        = $this->instance->get_rephrase_info( $id_video );
		$expected_status = 'draft';
		// Check callback response.
		$this->assertEquals( $expected_status, $db_data->status );
	}

	/**
	 * Test deleting rephrase information in the DB.
	 */
	public function test_delete_data() {
		$data = array(
			'title'    => 'Test Delete',
			'id_video' => '22',
			'status'   => 'pending',
		);
		$this->instance->insert_rephrase_info( $data );
		$this->instance->delete_rephrase_info( '22' );
		$db_data = $this->instance->get_rephrase_info( '22' );
		$this->assertNull( $db_data );
	}
}
