<?php
use Wubtitle\Utils\InvoiceHelper;
/**
 * Class TestInvoiceHelperNoLicense
 *
 * @package Wubtitle
 */

/**
 * Test InvoiceHelper.
 */
class TestInvoiceHelperNoLicense extends WP_Ajax_UnitTestCase {
    /**
     * Setup function.
     */
    public function setUp(): void {
        parent::setUp();
        $this->instance = new InvoiceHelper;
    }

    /**
     * Test no license check fiscal code.
     */
    public function test_no_license_check_fiscal_code(){
        wp_set_current_user( 1 );
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['fiscalCode']  = '11111111111';
        $expected_response    = 'Error. The product license key is missing.';
        try {
            $this->_handleAjax( 'check_fiscal_code' );
        } catch ( WPAjaxDieContinueException $e ) {}

        //Check excpetion
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertFalse( $response->success );
        $this->assertEquals( $expected_response, $response->data );
    }

}