<?php
use Wubtitle\Api\ApiPricingPlan;
/**
 * Class TestApiPricingPlanNotUserFree
 *
 * @package Wubtitle
 */

 /**
  * Classe che effettua dei test su ApiPricingPlan.
  */
class TestApiPricingPlanNotUserFree extends WP_Ajax_UnitTestCase {
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
    * Test not user free
    */
    public function test_not_user_free(){
      update_option( 'wubtitle_free', false );
      try {
          $this->_handleAjax( 'check_plan_change' );
      } catch ( WPAjaxDieContinueException $e ) {}

      // Verifica che è stata lanciata l'eccezione
      $this->assertTrue( isset( $e ) );
      $response = json_decode( $this->_last_response );
      $expected = 'change_plan';
      $this->assertTrue( $response->success);
      $this->assertEquals($expected, $response->data);
    }
}
