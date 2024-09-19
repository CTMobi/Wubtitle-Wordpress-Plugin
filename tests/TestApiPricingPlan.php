<?php
use Wubtitle\Api\ApiPricingPlan;
/**
 * Class TestApiPricingPlan
 *
 * @package Wubtitle
 */

 /**
  * Classe che effettua dei test su ApiPricingPlan.
  */
class TestApiPricingPlan extends WP_Ajax_UnitTestCase {
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
     * Fail test, license not found.
     */
     public function test_no_license_send_request(){
       $all_plans = array(
         'plan_test' => array(
           'stripe_code' => 'test_code'
         )
       );
       update_option( 'wubtitle_all_plans', $all_plans );
       $result = $this->instance->send_wanted_plan_info('plan_test');

       // Verifica che è stata lanciata l'eccezione
       $expected = 'The product license key is missing.';
       //verifica che c'è stato un'errore
       $this->assertEquals($expected, $result);
     }
      /**
       * Verifica che il body è stato creato correttamente
       */
       public function test_body_request(){
         $pricing_plan = 'premium';
         $site_url = get_option( 'siteurl' );
         $result = $this->instance->set_body_request( $pricing_plan, $site_url );
         $lang_expected = explode( '_', get_locale(), 2 )[0];
         $expected_body = array(
    			 'data' => array(
    				 'planId'    => 'premium',
    				 'domainUrl' => 'http://wordpress01.local',
             'siteLang' => $lang_expected,
    			 ),
    		 );
         $this->assertEquals($expected_body,$result);
       }

        /**
         * get error message 
         */
        public function test_error_manager() {
          $body = (object) array(
            'errors' => (object) array(
              'title'  => 'WRONG_CUSTOMER',
              'status' => 400
            )
          );
          $result_response   = $this->instance->error_confirm_manager(400, $body);

          $this->assertTrue( $result_response['couponError'] );
        }
}
