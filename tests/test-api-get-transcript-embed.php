<?php
use Wubtitle\Api\ApiGetTranscript;
/**
 * Class TestAPI
 *
 * @package Wubtitle
 */

 /**
  * Sample test.
  */
class TestApiGetTranscriptEmbed extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function SetUp(): void {
      parent::setUp();
   }
   /**
    * tearDown function.
    */
    public function tearDown(): void {
      parent::tearDown();
    }

   /**
    * test get transcript yt
    */
    public function test_get_transcript_embed(){
        $id_video = 'testId';
        $lang     = 'testLang';
        $trascript_post = array(
            'post_title'   => 'Video Title',
            'post_content' => 'Test content',
            'post_type'    => 'transcript',
            'post_status'  => 'publish',
        );
        $id_transcript  = wp_insert_post( $trascript_post );
        update_post_meta( $id_transcript, '_video_id', $id_video . $lang );
        $_POST['urlVideo']    = 'https://www.youtube.com/watch?v=' . $id_video;
        $_POST['subtitle'] = 'https://www.youtube.com/api?lang=' . $lang;
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['videoTitle']  = 'Video test';
        $_POST['from']        = 'default_post_type';
        try {
            $this->_handleAjax( 'get_transcript_embed' );
        } catch ( WPAjaxDieContinueException $e ) {}
        // Check exception
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertTrue( $response->success);
        $this->assertEquals( $id_transcript , $response->data );
    }
}
