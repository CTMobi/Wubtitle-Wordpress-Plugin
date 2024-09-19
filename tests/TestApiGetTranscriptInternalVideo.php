<?php
use Wubtitle\Api\ApiGetTranscript;
/**
 * Class TestApiGetTranscriptInternalVideo
 *
 * @package Wubtitle
 */

 /**
  * Sample test.
  */
class TestApiGetTranscriptInternalVideo extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function setUp(): void {
      parent::setUp();
   }
   /**
    * tearDown function.
    */
    public function tearDown(): void {
      parent::tearDown();
    }

    /**
    * test get transcript internal video
    */
    public function test_get_transcript_internal_video(){
        $id_video     = 2;
        $title        = 'Video Title';
        $post_content = 'Test content';
        $trascript_post = array(
            'post_title'   => $title,
            'post_content' => $post_content,
            'post_type'    => 'transcript',
            'post_status'  => 'publish',
        );
        $expected_response = (object) array(
            'post_title'   => $title,
            'post_content' => $post_content,
        );
        $id_transcript  = wp_insert_post( $trascript_post );
        update_post_meta( $id_transcript, 'wubtitle_transcript', $id_video );
        $_POST['id']          = $id_video;
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['from']        = 'classic_editor';
        try {
            $this->_handleAjax( 'get_transcript_internal_video' );
        } catch ( WPAjaxDieContinueException $e ) {}
        // Check exception
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertTrue( $response->success );
        $this->assertEquals( $expected_response , $response->data );
    }
}
