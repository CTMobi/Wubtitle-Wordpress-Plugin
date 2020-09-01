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
class TestApiGetTranscript extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function SetUp(){
     parent::setUp();
   }
   /**
    * tearDown function.
    */
    public function tearDown(){
      parent::tearDown();
    }

   /**
    * test get transcript yt
    */
    public function test_get_transcript_yt(){
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
        $_POST['urlSubtitle'] = 'https://www.youtube.com/api?lang=' . $lang;
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['videoTitle']  = 'Video test';
        $_POST['from']        = 'default_post_type';
        try {
            $this->_handleAjax( 'get_transcript_yt' );
        } catch ( WPAjaxDieContinueException $e ) {}
        // Check exception
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertTrue( $response->success);
        $this->assertEqualSets( $id_transcript , $response->data );
    }
    /**
    * test url not valid get video info
    */
    public function test_url_not_valid_get_video_info(){
        $_POST['url']    = 'https://www.youtube.com/watch?test=novalid';
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        try {
            $this->_handleAjax( 'get_video_info' );
        } catch ( WPAjaxDieContinueException $e ) {}
        // Check exception
        $expected_response = 'Url not a valid youtube url';
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertFalse( $response->success);
        $this->assertEqualSets( $expected_response , $response->data );
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
        $this->assertEqualSets( $expected_response , $response->data );
    }
}
