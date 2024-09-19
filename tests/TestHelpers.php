<?php
use Wubtitle\Helpers;
/**
 * Class TestHelpers
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
    public function setUp(): void {
        parent::setUp();
        $this->instance = new Helpers;
    }

    /**
     * Teardown function.
     */
    public function tearDown(): void {
        parent::tearDown();
        delete_option('wubtitle_license_key');
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
    public function test_authorizer_error_no_license() {
        $request = new WP_REST_Request;
        $request->set_header( 'jwt' , 'test_invalid_jwt' );

        $response = $this->instance->authorizer( $request );

        $this->assertFalse( $response );
    }

    /**
     * Test check check has error. 
     */
    public function test_authorizer_error_wrong_license() {
        update_option('wubtitle_license_key', 'key_a');

        $request = new WP_REST_Request;
        $request->set_header('jwt', 'key_b');

        $response = $this->instance->authorizer($request);

        $this->assertFalse($response);
    }

    /**
     * Test check check passes. 
     */
    public function test_authorizer_success() {
        $jwt = Firebase\JWT\JWT::encode([], 'valid_key', 'HS256');

        update_option('wubtitle_license_key', 'valid_key');

        $request = new WP_REST_Request;
        $request->set_header('jwt', $jwt);

        $response = $this->instance->authorizer($request);
        
        $this->assertTrue($response);
    }

}