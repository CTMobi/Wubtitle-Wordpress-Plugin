<?php
use Wubtitle\Helpers;
/**
 * Class Helpers
 *
 * @package Wubtitle
 */

/**
 * Test Helpers.
 */
class TestHelpers extends WP_UnitTestCase {
    /**
     * Setup function.
     */
    public function SetUp(): void {
        parent::setUp();
        $this->instance = new Helpers;
    }

    /**
     * Test check gutenberg active. 
     */
    public function test_is_gutenberg_active() {

        $response = $this->instance->is_gutenberg_active( );

        $this->assertTrue( $response );
    }

    /**
     * Test check classic editor active. 
     */
    public function test_is_classic_editor_active() {

        $response = $this->instance->is_classic_editor_active( );

        $this->assertFalse( $response );
    }

    /**
     * Test check check has error. 
     */
    public function test_check_has_error() {
        $status     = '404';
        $verified   = false;
        $error_type = 404;

        $expected_response = '4xx';

        $response = $this->instance->check_has_error( $status, $verified, $error_type );

        $this->assertEquals( $expected_response, $response );
    }

    /**
     * Test check check has error. 
     */
    public function test_authorizer() {
        $request = new WP_REST_Request;
        $request->set_header( 'jwt' , 'test_invaliv_jwt' );

        $response = $this->instance->authorizer( $request );

        $this->assertFalse( $response );
    }


}