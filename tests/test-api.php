<?php
use Ear2Words\Api\ApiRequest;
/**
 * Class TestAPI
 *
 * @package Ear2Words
 */

 /**
  * Sample test.
  */
class TestApiRequest extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function SetUp(){
     parent::setUp();
     update_option('siteurl','http://wordpress01.local');
     $this->instance = new Ear2Words\Api\ApiRequest();
   }
   /**
    * tearDown function.
    */
    public function tearDown(){
      parent::tearDown();
    }

   /**
    * Effettua la chiamata senza nonce
    */
    public function test_negative_send_request(){
      try {
          $this->_handleAjax( 'submitVideo' );
      } catch ( WPAjaxDieContinueException $e ) {}

      // Verifica che è stata lanciata l'eccezione
      $this->assertTrue( isset( $e ) );
      $response = json_decode( $this->_last_response );
      $this->assertFalse( $response->success);
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
         $src = 'http://test';
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
         $attachment_id = self::factory()->attachment->create($attachment_data,'/test',1);
         wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
         $data = array(
           'id_attachment' => $attachment_id,
           'src_attachment' => $src,
           'lang' => 'en'
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
          $attachment_id = self::factory()->attachment->create($attachment_data,'/test',1);
          wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
          $data = array(
            'id_attachment' => $attachment_id,
            'src_attachment' => $src,
            'lang' => 'it'
          );
          $result = $this->instance->set_body_request($data);
          $this->assertFalse($result);
        }
}
