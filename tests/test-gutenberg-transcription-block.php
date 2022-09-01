<?php
use Wubtitle\Gutenberg\TranscriptionBlock;
/**
 * Class TranscriptionBlock
 *
 * @package Wubtitle
 */

/**
 * Test transcription block.
 */
class TestTranscriptionBlock extends WP_UnitTestCase {
    /**
     * Setup function.
     */
    public function SetUp(): void {
        parent::setUp();
        $this->instance = new TranscriptionBlock;
    }
    /**
     * Test add parameters query.
     */
     public function test_add_parameters_query(){
        $request  = new WP_REST_Request;
        $id_video = 'id_test';
        $params   = array(
            'metaKey'   => 'id_video',
            'metaValue' => 'http://test?v=' . $id_video,
        );
        $args = array();
        $request->set_default_params( $params );
        $response = $this->instance->add_parameters_query( $args, $request );
        $this->assertEquals( $id_video, $response['meta_value'] );
     }

}