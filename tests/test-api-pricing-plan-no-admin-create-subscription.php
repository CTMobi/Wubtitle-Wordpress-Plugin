<?php
use Wubtitle\Api\ApiPricingPlan;
/**
 * Class TestAPIPricingPlan
 *
 * @package Wubtitle
 */

 /**
  * Classe che effettua dei test su ApiPricingPlan.
  */
class TestApiPricingPlanNoAdminCReateSubscription extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function SetUp(): void{
     parent::setUp();
     update_option('siteurl','http://wordpress01.local');
     $this->instance = new ApiPricingPlan();
   }
   /**
    * tearDown function.
    */
    public function tearDown(): void{
      parent::tearDown();
    }

      /**
       * Test no administrator create subscription
       */
      public function test_no_administrator_create_subscription(){
        $_POST['_ajax_nonce']    = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['invoiceObject']  = (object) array();
        $_POST['email']          = 'alessio@test.com';
        $_POST['actionCheckout'] = 'actiontest';
        try {
            $this->_handleAjax( 'create_subscription' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $expected = 'An error occurred. Please try again in a few minutes.';
        $this->assertFalse( $response->success);
        $this->assertEquals($expected, $response->data);
      }
}
