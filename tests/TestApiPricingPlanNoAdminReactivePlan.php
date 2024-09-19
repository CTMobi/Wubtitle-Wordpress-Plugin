<?php
use Wubtitle\Api\ApiPricingPlan;
/**
 * Class TestApiPricingPlanNoAdminReactivePlan
 *
 * @package Wubtitle
 */

 /**
  * Classe che effettua dei test su ApiPricingPlan.
  */
class TestApiPricingPlanNoAdminReactivePlan extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function setUp(): void{
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
       * Test no administrator reactivate plan
       */
      public function test_no_administrator_reactivate_plan(){
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        try {
            $this->_handleAjax( 'reactivate_plan' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $expected = 'An error occurred. Please try again in a few minutes.';
        $this->assertFalse( $response->success);
        $this->assertEquals($expected, $response->data);
      }
}
