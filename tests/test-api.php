<?php
use Wubtitle\Api\ApiRequest;
/**
 * Class TestAPI
 *
 * @package Wubtitle
 */

 /**
  * Sample test.
  */
class TestApiRequest extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function SetUp(): void {
     parent::setUp();
     update_option('siteurl','http://wordpress01.local');
     $this->instance = new Wubtitle\Api\ApiRequest();
   }
   /**
    * tearDown function.
    */
    public function tearDown(): void {
      parent::tearDown();
    }
    /**
     * Effettua la chiamata senza avere una license key
     */
     public function test_nolicense_send_request(){
       $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
       $_POST['id_attachment'] = 1;
       $_POST['src_attachment'] = '#';
       $_POST['id_post'] = 1;
       try {
           $this->_handleAjax( 'submitVideo' );
       } catch ( WPAjaxDieContinueException $e ) {}
       // Verifica che è stata lanciata l'eccezione
       $this->assertTrue( isset( $e ) );
       $response = json_decode( $this->_last_response );
       $this->assertFalse( $response->success);
     }
     /**
      * Effettua la chiamata validando tutti i campi
      */
      public function test_validate_field(){
        $array['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $array['src_attachment'] = '#';
        $array['id_attachment'] = 1;
        $array['lang'] = 'en';
        $result = $this->instance->sanitize_input($array);
        $this->assertArrayHasKey('id_attachment',$result);
        $this->assertArrayHasKey('src_attachment',$result);
      }
      /**
       * Verifica che il body è stato creato correttamente
       */
       public function test_body_request(){
         $src = 'http://test.com';
         $attachment_data = array(
            'guid'           => '/test',
            'post_mime_type' => 'video',
            'post_title'     => 'test',
            'post_content'   => '',
            'post_status'    => 'inherit'
          );
          $attachment_metadata = array(
            'filesize' => 123456,
            'length'   => 15,
          );
         $attachment_id = self::factory()->attachment->create($attachment_data,['/test'],1);
         wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
         $data = array(
           'id_attachment' => $attachment_id,
           'src_attachment' => $src,
           'lang' => 'en-US'
         );
         $result = $this->instance->set_body_request($data);
         $expected_body = array(
           'source' => 'INTERNAL',
    			 'data' => array(
    				 'attachmentId' => $attachment_id,
    				 'url'          => $src,
    				 'size'         => 123456,
    				 'duration'     => 15,
             'lang'         => 'en-US'
    			 ),
    		 );
         $this->assertEqualSets($expected_body,$result);
       }
       /**
        * Effettua la chiamata con un url non valida
        */
        public function test_fail_body_request(){
          $src = 'invalidurl';
          $attachment_data = array(
             'guid'           => '/test',
             'post_mime_type' => 'video',
             'post_title'     => 'test',
             'post_content'   => '',
             'post_status'    => 'inherit'
           );
           $attachment_metadata = array(
             'filesize' => 123456,
             'length'   => 15,
           );
          $attachment_id = self::factory()->attachment->create($attachment_data,['/test'],1);
          wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
          $data = array(
            'id_attachment' => $attachment_id,
            'src_attachment' => $src,
            'lang' => 'it'
          );
          $result = $this->instance->set_body_request($data);
          $this->assertFalse($result);
        }
        /**
         * Test update post meta 
         */
        public function test_update_post_meta() {
          $id_attachment   = 1;
          $expected_lang   = 'IT';
          $expected_uuid   = '1234';
          $expected_status = 'pending';
          $this->instance->update_uuid_status_and_lang( $id_attachment, $expected_lang, $expected_uuid );
          
          $result_lang   = get_post_meta( $id_attachment, 'wubtitle_lang_video', true );
          $result_uuid   = get_post_meta( $id_attachment, 'wubtitle_job_uuid', true );
          $result_status = get_post_meta( $id_attachment, 'wubtitle_status', true );

          $this->assertEquals( $expected_lang, $result_lang );
          $this->assertEquals( $expected_uuid, $result_uuid );
          $this->assertEquals( $expected_status, $result_status );
        }

        /**
         * Test get error message
         */
        public function test_get_error_message() {
          $reason = array(
            'reason' => 'NO_AVAILABLE_JOBS'
          );
          $body = array(
            'errors' => array(
              'title'  => wp_json_encode($reason),
              'status' => 429
            )
          );
          $response = array(
            'body' => wp_json_encode($body)
          );
          $expected_response = 'Error, no more video left for your subscription plan';
          $result_response   = $this->instance->get_error_message($response);
          $this->assertEquals( $expected_response, $result_response );
        }
        /**
         * get error message for minutes error
         */
        public function test_get_minutes_error_message() {
          $time_left    = 5;
          $jobs_left    = 2;
          $object_error = array(
            'reason'        => 'NO_AVAILABLE_MINUTES',
            'videoTimeLeft' => $time_left,
            'jobsLeft'      => $jobs_left,
          );
          $body = array(
            'errors' => array(
              'title'  => wp_json_encode($object_error),
              'status' => 429
            )
          );
          $response = array(
            'body' => wp_json_encode($body)
          );
          $expected_response = 'Error, video length is longer than minutes available for your subscription plan (minutes left ' . date_i18n( 'i:s', $time_left ) . ', video left ' . $jobs_left . ')';
          $result_response   = $this->instance->get_error_message($response);
          $this->assertEquals( $expected_response, $result_response );
        }
}
