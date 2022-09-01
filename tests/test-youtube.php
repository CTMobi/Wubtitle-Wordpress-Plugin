<?php
/**
 * Test YouTube
 *
 * @package Wubtitle
*/

use \Wubtitle\Core\Sources\YouTube;

/**
* Test ricezione trascrizione.
*/
class TestYoutubeTranscript extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function SetUp(): void {
		parent::setUp();
		$this->instance = new YouTube();
	}

	 /**
      * Test invalid Youtube url get transcript 
      */
      public function test_invalid_url_get_transcript() {
		$content  = 'text content of transcription';
		$id_video = 'test_id';

		$youtube_source   = $this->getMockBuilder( YouTube::class )->setMethods( array( 'send_job_to_backend' ) )->getMock();
		$response_backend = array(
			'response'      => array(
				'code' => 201,
			),
		);
		$expected_response = 'Transcript not avaiable for this video.';
		$youtube_source->expects( $this->once() )->method( 'send_job_to_backend' )->will( $this->returnValue( $response_backend ) );
		$response = $youtube_source->get_transcript( $id_video, 'test title', 'default_post_type', 'invalid_url' );

		$this->assertFalse( $response['success'] );
		$this->assertEquals( $expected_response, $response['message'] );
	}

}
