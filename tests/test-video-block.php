<?php
use Wubtitle\Gutenberg\VideoBlock;
/**
 * Class TranscriptionBlock
 *
 * @package Wubtitle
 */

/**
 * Test transcription block.
 */
class TestVideoBlock extends WP_UnitTestCase {
    /**
     * Setup function.
     */
    public function SetUp(){
        parent::setUp();
        $this->instance = new VideoBlock;
    }
    /**
     * Test add enqueue script.
     */
     public function test_enqueue_script(){
        $response = $this->instance->add_subtitle_button_enqueue();
        $this->assertTrue( wp_script_is('add_subtitle_button-script') );
     }
     /**
     * Test render callback.
     */
    public function test_render_callback(){
        $block   = array(
            'blockName' => 'core/video',
            'attrs'     => array(),
        );
        $content  = 'none';
        $response = $this->instance->video_dynamic_block_render_callback( $content, $block );
        $this->assertEquals( $content, $response );
     }

}