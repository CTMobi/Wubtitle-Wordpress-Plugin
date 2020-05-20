<?php
use Ear2Words\Api\ApiPricingPlan;
/**
 * Class TestAPIPricingPlan
 *
 * @package Ear2Words
 */

 /**
  * Classe che effettua dei test su ApiPricingPlan.
  */
class TestApiPricingPlan extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function SetUp(){
     parent::setUp();
     update_option('siteurl','http://wordpress01.local');
     $this->instance = new ApiPricingPlan();
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
          $this->_handleAjax( 'submit_plan' );
      } catch ( WPAjaxDieContinueException $e ) {}

      // Verifica che è stata lanciata l'eccezione
      $this->assertTrue( isset( $e ) );
      $response = json_decode( $this->_last_response );
      $this->assertFalse( $response->success);
    }
    /**
     * Effuettua la chiamata senza nonce
     */
     public function test_no_license_send_request(){
       $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
       $_POST['pricing_plan'] = 'test';
       try {
           $this->_handleAjax( 'submit_plan' );
       } catch ( WPAjaxDieContinueException $e ) {}

       // Verifica che è stata lanciata l'eccezione
       $this->assertTrue( isset( $e ) );
       $response = json_decode( $this->_last_response );
       //verifica che c'è stato un'errore
       $this->assertFalse( $response->success);
     }
    /**
     * Effettua la chiamata senza nonce alla funzione update_payment
     */
     public function test_negative_update_payment(){
       try {
           $this->_handleAjax( 'update_payment_method' );
       } catch ( WPAjaxDieContinueException $e ) {}

       // Verifica che è stata lanciata l'eccezione
       $this->assertTrue( isset( $e ) );
       $response = json_decode( $this->_last_response );
       $this->assertFalse( $response->success);
     }
      /**
       * Verifica che il body è stato creato correttamente
       */
       public function test_body_request(){
         $pricing_plan = 'premium';
         $site_url = get_site_url();
         $result = $this->instance->set_body_request( $pricing_plan, $site_url );
         $lang_expected = explode( '_', get_locale(), 2 )[0];
         $expected_body = array(
    			 'data' => array(
    				 'planId'    => 'premium',
    				 'domainUrl' => 'http://wordpress01.local',
             'siteLang' => $lang_expected,
    			 ),
    		 );
         $this->assertEqualSets($expected_body,$result);
       }
}
