<?php
/**
 * Class TestDashboardThankYouPage
 * 
 * @package Wubtitle
 */
use Wubtitle\Utils\InvoiceHelper;

/**
 * Test Dashboard views
 */
class TestDashboardThankYouPage extends WP_Ajax_UnitTestCase {
    /**
     * Setup function
     */
    public function setUp(): void {
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
    public function tearDown(): void {
        parent::tearDown();
      }
    /**
     * Test thank you page.
     */
    public function test_thankyou_page() {
        wp_set_current_user( 1 );
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
}