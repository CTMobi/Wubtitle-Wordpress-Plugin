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
       * Test no license change plan
       */
      public function test_no_license_change_plan(){
        $this->_setRole( 'administrator' );
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
        $expected = 'Unable to create subtitles. The product license key is missing.';
        $this->assertFalse( $response->success);
        $this->assertEquals($expected, $response->data);
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


      /**
       * Test no license reactivate plan
       */
      public function test_no_license_reactivate_plan(){
        $this->_setRole( 'administrator' );
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
