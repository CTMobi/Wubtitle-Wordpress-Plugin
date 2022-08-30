<?php
use Wubtitle\Utils\InvoiceHelper;
/**
 * Class InvoiceHelper
 *
 * @package Wubtitle
 */

/**
 * Test InvoiceHelper.
 */
class TestInvoiceHelperNoNonce extends WP_Ajax_UnitTestCase {
    /**
     * Setup function.
     */
    public function SetUp(): void {
        parent::setUp();
        $this->instance = new InvoiceHelper;
    }
    /**
     * Test no nonce check coupon.
     */
    public function test_no_nonce_check_coupon(){
        wp_set_current_user( 1 );
        $_POST['coupon']  = 'Test10';
        $_POST['planId']  = '1';

        try {
            $this->_handleAjax( 'check_coupon' );
        } catch ( WPAjaxDieContinueException $e ) {}

        //Check excpetion
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertFalse( $response->success );
    }

}