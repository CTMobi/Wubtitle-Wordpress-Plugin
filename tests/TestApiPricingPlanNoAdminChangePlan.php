<?php
use Wubtitle\Api\ApiPricingPlan;
/**
 * Class TestApiPricingPlanNoAdminChangePlan
 *
 * @package Wubtitle
 */

 /**
  * Classe che effettua dei test su ApiPricingPlan.
  */
class TestApiPricingPlanNoAdminChangePlan extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function setUp(): void {
     parent::setUp();
     update_option('siteurl','http://wordpress01.local');
     $this->instance = new ApiPricingPlan();
   }
   /**
    * tearDown function.
    */
    public function tearDown(): void {
      parent::tearDown();
    }

      /**
       * Test no administrator change plan
       */
      public function test_no_administrator_change_plan(){
        update_option( 'wubtitle_wanted_plan_rank', 2 );
        $all_plans = array(
          array(
            'stripe_code' => 'free'
          ),
          array(
            'stripe_code' => 'standard'
          ),
          array(
            'stripe_code' => 'elite'
          ),
        );
        update_option( 'wubtitle_all_plans', $all_plans );
        update_option( 'wubtitle_plan_rank', 1 );
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        try {
            $this->_handleAjax( 'change_plan' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $expected = 'An error occurred. Please try again in a few minutes.';
        $this->assertFalse( $response->success);
        $this->assertEquals($expected, $response->data);
      }
}
