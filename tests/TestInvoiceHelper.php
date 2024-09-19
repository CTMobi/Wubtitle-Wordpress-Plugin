<?php
use Wubtitle\Utils\InvoiceHelper;
/**
 * Class TestInvoiceHelper
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
    public function setUp(): void {
        parent::setUp();
        $this->instance = new InvoiceHelper;
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

}