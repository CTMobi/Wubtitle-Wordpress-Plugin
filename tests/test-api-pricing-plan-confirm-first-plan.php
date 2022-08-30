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
class TestApiPricingPlanConfirmFirstSub extends WP_Ajax_UnitTestCase {
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
       * Test confirm new subscription without plan id
       */
      public function test_no_planid_confirm_first_subscription(){
        $_POST['_ajax_nonce']   = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['setupIntent']   = wp_json_encode ( array( 'setupIntent' => 'test_setup') );
        $_POST['email']         = 'alessio@test.com';
        $_POST['name']          = 'alessio';
        // set action = create for first subscription
        $_POST['actionCheckout'] = 'create';
        try {
            $this->_handleAjax( 'confirm_subscription' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $expected = 'An error occurred. Please try again in a few minutes.';
        $this->assertFalse( $response->success);
        $this->assertEquals($expected, $response->data);
      }
}
