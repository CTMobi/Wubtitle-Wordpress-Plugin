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
class TestInvoiceHelper extends WP_Ajax_UnitTestCase {
    /**
     * Setup function.
     */
    public function SetUp(){
        parent::setUp();
        $this->instance = new InvoiceHelper;
    }

    /**
     * Test no license check vat code.
     */
    public function test_no_license_check_vat_code(){
        $this->_setRole( 'administrator' );
        $_POST['_ajax_nonce'] = wp_create_nonce( 'itr_ajax_nonce' );
        $_POST['price_plan']  = 25;
        $_POST['vat_code']    = '11111111111';
        $_POST['country']     = 'IT';
        $expected_response    = 'Error. The product license key is missing.';
        try {
            $this->_handleAjax( 'check_vat_code' );
        } catch ( WPAjaxDieContinueException $e ) {}

        //Check excpetion
        $this->assertTrue( isset( $e ) );
        $response = json_decode( $this->_last_response );
        $this->assertFalse( $response->success );
        $this->assertEquals( $expected_response, $response->data );
    }

    /**
     * Test no license check fiscal code.
     */
    public function test_no_license_check_fiscal_code(){
        $this->_setRole( 'administrator' );
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

    /**
     * Test build invoice array. 
     */
    public function test_build_invoice_array() {
        $invoice_object = new stdClass();

        $invoice_object->invoice_firstname = 'Alessio';
        $invoice_object->invoice_lastname  = 'Catania';
        $invoice_object->invoice_email     = 'test@test.com';
        $invoice_object->telephone         = '12304567890';
        $invoice_object->prefix            = '+25';
        $invoice_object->address           = 'test';
        $invoice_object->city              = 'Catania';
        $invoice_object->country           = 'KK';
        $invoice_object->company_name      = 'ciao';

        $response = $this->instance->build_invoice_array( $invoice_object );

        $invoice_object->prefix = '25';
        $expected_response      = (array) $invoice_object;
        $this->assertEqualSets( $expected_response, $response );
    }

    /**
     * Test italian invoice array. 
     */
    public function test_italian_invoice() {
        $invoice_object = new stdClass();

        $invoice_object->cap              = '88888';
        $invoice_object->province         = 'Catania';
        $invoice_object->fiscal_code      = '0000000000000000';
        $invoice_object->destination_code = '';
        $invoice_details                  = array();

        $response = $this->instance->italian_invoice( $invoice_details, $invoice_object );

        $expected_response      = array(
            'PostCode'   => $invoice_object->cap,
            'Province'   => $invoice_object->province,
            'FiscalCode' => $invoice_object->fiscal_code,
        );
        $this->assertEqualSets( $expected_response, $response );
    }

    /**
     * Test no nonce check coupon.
     */
    public function test_no_nonce_check_coupon(){
        $this->_setRole( 'administrator' );
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