<?php
use Wubtitle\MediaLibrary\TrascriptionsExtends;
/**
 * Class TestTrascriptionsExtends
 *
 * @package Wubtitle
 */

/**
 * Test TrascriptionsExtends.
 */
class TestTrascriptionsExtends extends WP_UnitTestCase {

    private $instance;
    
    /**
     * Setup function.
     */
    public function setUp(): void {
        parent::setUp();
        $this->instance = new TrascriptionsExtends;
    }

    /**
     * Test add enqueue script.
     */
    public function test_enqueue_script(){
        $response = $this->instance->include_transcription_modal_script();
        $this->assertTrue( wp_script_is('transcription_modal_script') );
     }

    /**
     * Test admin notices.
     */
    public function test_admin_notice(){
        set_current_screen( 'post' );
        $this->instance->wubtitle_admin_notice();
        $this->expectOutputString( '<div id="wubtitle-notice" class="notice notice-error" style="display:none"></div>' );
     }

}