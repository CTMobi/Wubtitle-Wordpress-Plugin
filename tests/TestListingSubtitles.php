<?php
use Wubtitle\MediaLibrary\ListingSubtitles;
/**
 * Class TestListingSubtitles
 *
 * @package Wubtitle
 */

/**
 * Test ListingSubtitle.
 */
class TestListingSubtitles extends WP_UnitTestCase {

    private $instance;
    
    /**
     * Setup function.
     */
    public function setUp(): void {
        parent::setUp();
        $this->instance = new ListingSubtitles;
    }
    /**
     * Test add status column.
     */
     public function test_status_column(){
        $cols          = array();
        $expected_data = 'Subtitle';
        $response      = $this->instance->wubtitle_status_column( $cols );
        $this->assertEquals( $expected_data, $response['wubtitle_status'] );
     }
     /**
     * Test status value.
     */
    public function test_status_value(){
        $column_name      = 'wubtitle_status';
        $attachments_data = array(
            'guid'           => '/test',
            'post_mime_type' => 'video/mp4',
            'post_title'     => 'test',
            'post_content'   => '',
        );

        $expected_output = 'Published';
        $attachment_id   = self::factory()->attachment->create( $attachments_data );
        update_post_meta( $attachment_id, 'wubtitle_status', 'enabled' );

        $response = $this->instance->wubtitle_status_value( $column_name, $attachment_id );
        $this->expectOutputString( $expected_output );
     }

    /**
     * Test add enqueue script.
     */
    public function test_enqueue_script(){
        $response = $this->instance->wubtitle_column_width();
        $this->assertTrue( wp_style_is('wubtitle_column_style') );
     }

}