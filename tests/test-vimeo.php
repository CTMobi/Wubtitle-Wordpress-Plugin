<?php
/**
 * Test YouTube
 *
 * @package Wubtitle
*/

use \Wubtitle\Core\Sources\Vimeo;

/**
* Test ricezione trascrizione.
*/
class TestVimeoTranscript extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function SetUp(){
		parent::setUp();
		$this->instance = new Vimeo();
	}
	/**
	 * Test Vimeo get transcript without license
	 */
	 public function test_no_licese_get_video_info(){
        $url       = 'https://test/id_video';
        $url_parts = wp_parse_url( $url );

        $expected_response = array(
            'success' => false,
            'message' => 'License key is missing',
        );
        
        $response = $this->instance->get_video_info($url_parts );
        $this->assertEqualSets( $expected_response, $response );
	 }

}