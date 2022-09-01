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
class TestApiGetTranscriptNotValid extends WP_Ajax_UnitTestCase {
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
        $this->assertEquals( $expected_response , $response->data );
    }
}
