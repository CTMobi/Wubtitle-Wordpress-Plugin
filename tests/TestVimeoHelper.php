<?php
use Wubtitle\Utils\VimeoHelper;
/**
 * Class TestVimeoHelper
 *
 * @package Wubtitle
 */

/**
 * Test VimeoHelper.
 */
class TestVimeoHelper extends WP_UnitTestCase {
    /**
     * Setup function.
     */
    public function setUp(): void {
        parent::setUp();
        $this->instance = new VimeoHelper;
    }

    /**
     * Test get languages. 
     */
    public function test_get_languages() {

        $response = $this->instance->get_languages( );

        $expected_response = 'Thai';
        $this->assertEquals( $expected_response, $response['th'] );
    }


}