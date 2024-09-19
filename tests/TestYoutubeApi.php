<?php
/**
 * Test YouTube
 *
 * @package Wubtitle
*/


/**
* Stress test api yt.
*/
class TestYoutubeApi extends WP_UnitTestCase {
	 /**
		* Test stress api youtube
		*/
		public function test_api_yt(){
			$id_video = "WSKi8HfcxEk";
			for ($i=0; $i < 1000; $i++) {
				$get_info_url = "https://www.youtube.com/get_video_info?video_id=$id_video";

				$response = wp_remote_get(
					$get_info_url,
					array(
						'headers' => array( 'Accept-Language' => get_locale() ),
					)
				);
				$file     = wp_remote_retrieve_body( $response );
				$file_info = array();
				parse_str( $file, $file_info );
				$title_video = json_decode( $file_info['player_response'] )->videoDetails->title;
				$expected_title = "The Rise of the Machines – Why Automation is Different this Time";
			 	$this->assertEquals( $expected_title, $title_video );
			}

		}

}
