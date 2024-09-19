<?php
use Wubtitle\Api\ApiRequest;
/**
 * Class TestApiRequestNegative
 *
 * @package Wubtitle
 */

 /**
  * Sample test.
  */
class TestApiRequestNegative extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function setUp(): void {
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
}
