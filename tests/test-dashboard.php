<?php
/**
 * Class TestDashboard
 * 
 * @package Wubtitle
 */
use Wubtitle\Utils\InvoiceHelper;

/**
 * Test Dashboard views
 */
class TestDashboard extends WP_Ajax_UnitTestCase {
    /**
     * Setup function
     */
    public function SetUp() {
        parent::setUp();
        $price_object = new stdClass();
        $this->price_info_object     = array();
        $price_object->price         = 5;
        $price_object->taxAmount     = 1;
        $price_object->taxPercentage = 10;
        $this->price_info_object[0]  = $price_object;
        $price_object->price         = 4;
        $price_object->taxAmount     = 2;
        $price_object->taxPercentage = 20;
        $this->price_info_object[1]  = $price_object;

        $this->all_plans = array(
            array(
                'totalSeconds'     => 2000,
                'totalJobs'        => 4,
                'name'             => 'free',
                'icon'             => 'smile',
                'stripe_code'      => 'freecode',
                'features'         => array(
                    'feature 1',
                    'feature 2',
                ),
                'dot_list'         => array(
                    '1',
                    '2',
                    '3',
                ),
                'supportedFormats' => ['all'],
                'dotlistV4'        => '',
            ),
            array(
                'totalSeconds'     => 5466,
                'totalJobs'        => 16,
                'name'             => 'pro',
                'icon'             => 'fire',
                'stripe_code'      => 'procode',
                'features'         => array(
                    'feature 2',
                    'feature 3',
                ),
                'dot_list'         => array(
                    '1',
                    '2',
                    '3',
                ),
                'supportedFormats' => ['all'],
                'dotlistV4'        => '',
            ),
        );
        update_option( 'wubtitle_all_plans', $this->all_plans );
        update_option( 'wubtitle_plan', 'plan_test' );
        update_option( 'wubtitle_plan_rank', 1 );
        update_option( 'wubtitle_is_first_month', false );
    }
    /**
    * tearDown function.
    */
    public function tearDown(){
        parent::tearDown();
      }
    /**
     * Test Cancel Page.
     */
    public function test_cancel_page() {
        $this->_setRole( 'administrator' );
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['priceinfo']   = wp_json_encode( $this->price_info_object );
        try {
            $this->_handleAjax( 'cancel_template' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertTrue( $response->success );
    }
    /**
     * Test payment page.
     */
    public function test_payment_page() {
        $this->_setRole( 'administrator' );
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['priceinfo']   = wp_json_encode( $this->price_info_object );
        try {
            $this->_handleAjax( 'payment_template' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertTrue( $response->success );
    }
    /**
     * Test thank you page.
     */
    public function test_thankyou_page() {
        $this->_setRole( 'administrator' );
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['mode']        = 'upgrade';
        try {
            $this->_handleAjax( 'thankyou_page' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertTrue( $response->success );
    }
    /**
     * Test custom form page.
     */
    public function test_custom_form_page() {
        $this->_setRole( 'administrator' );
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['priceinfo']   = wp_json_encode( $this->price_info_object );
        $_POST['planRank']    = 1;
        try {
            $this->_handleAjax( 'custom_form_template' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertTrue( $response->success );
    }
    /**
     * Test change plan page.
     */
    public function test_change_plan_page() {
        $this->_setRole( 'administrator' );
        $_POST['_ajax_nonce']    = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['priceinfo']      = wp_json_encode( $this->price_info_object );
        $_POST['wantedPlanRank'] = 1;
        try {
            $this->_handleAjax( 'change_plan_template' );
        } catch ( WPAjaxDieContinueException $e ) {}
  
        // Check that the exception was thrown
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertTrue( $response->success );
    }
}