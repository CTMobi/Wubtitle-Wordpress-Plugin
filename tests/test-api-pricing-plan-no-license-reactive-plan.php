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
class TestApiPricingPlanNoLicenseReactivePlan extends WP_Ajax_UnitTestCase {
  /**
   * Setup function.
   */
   public function SetUp(): void {
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
       * Test no license reactivate plan
       */
      public function test_no_license_reactivate_plan(){
        wp_set_current_user( 1 );
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        try {
            $this->_handleAjax( 'reactivate_plan' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $expected = 'Unable to create subtitles. The product license key is missing.';
        $this->assertFalse( $response->success);
        $this->assertEquals($expected, $response->data);
      }
}
