<?php
/**
 * Class Subtitle Test
 *
 * @package Wubtitle
 */

use Wubtitle\Core\Subtitle;

/**
 * Subtitle test case.
 */
class TestSubtitle extends WP_UnitTestCase {

	/**
	 * Setup del test
	 */
	public function setUp(): void {
		parent::setUp();
		$this->instance = new Subtitle();
		$this->instance->run();
		$attachment_data = array(
			'guid'           => 'http://wordpress01.local/wp-content/uploads/2020/04/sottotitoli.vtt',
			'file'           => '/sottotitoli.vtt',
			'post_mime_type' => 'text/vtt',
			'post_title'     => 'sottotitoli',
			'post_content'   => '',
			'post_parent'    => '0',
			'post_status'    => 'inherit'
		);
		$this->subtitle_id = $this->factory()->attachment->create($attachment_data);

		$attachment_data = array(
			'guid'           => 'http://wordpress01.local/wp-content/uploads/2020/04/video.mp4',
			'post_mime_type' => 'video/mp4',
			'file'           => '/video.mp4',
			'post_title'     => 'video',
			'post_content'   => '',
			'post_parent'    => '0',
			'meta_input'     => array(
				'wubtitle_subtitle' => $this->subtitle_id
			)
		);
		$this->video_id = $this->factory()->attachment->create($attachment_data);
	}

	public function test_delete_attachment_and_subtitle() {
		$delete = wp_delete_attachment( $this->video_id);
		$this->assertInstanceOf('WP_Post',$delete);
		$subtitle_post = get_post($this->subtitle_id);
		$this->assertNull($subtitle_post);
	}

}
