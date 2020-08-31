<?php
use Wubtitle\Api\ApiStoreSubtitle;
/**
 * Class TestEndpointCallback
 *
 * @package Wubtitle
 */

 /**
	* Test callback endpoint.
	*/
class TestEndpointCallback extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function SetUp(){
		parent::setUp();
		$this->instance = new ApiStoreSubtitle;
		//aggiungo un'attachment per il video
		$attachment_data = array(
			'guid'           => 'http://wordpress01.local/wp-content/uploads/2020/04/video.mp4',
			'post_mime_type' => 'video/mp4',
			'post_title'     => 'video',
			'post_content'   => '',
			);
		$attachment_id = $this->factory()->attachment->create($attachment_data);
		//aggiungo uno job uuid all'attachment.
		update_post_meta($attachment_id,'wubtitle_job_uuid','provauuid');
		// lo metto in stato pending.
		update_post_meta($attachment_id,'wubtitle_status','pending');
		$this->attachment_id = $attachment_id;
	}
	/**
	 * Test callback dell'endpoint che riceve i job falliti.
	 */
	 public function test_get_jobs_failed(){
		$jobId = "provauuid";
		$request_data    = array(
			'data' => array(
				'jobId' => $jobId
			),
		);
		$request = new WP_REST_Request;
		$request->set_default_params( $request_data );
		$response = $this->instance->get_jobs_failed($request);
		$result_status = get_post_meta( $this->attachment_id, 'wubtitle_status', true );
		$expected_response = array(
			'data' => array(
				'status' => '200',
				'title'  => 'Success',
			),
		);
		$expected_status = 'error';
		//verifico che la chiamata è andata a buon fine.
		$this->assertEqualSets($expected_response, $response);
		//verifico che lo stato dell'attachment è passato da pending a error.
		$this->assertEquals($expected_status, $result_status);
	 }
	/**
	 * Test endpoint callback store subtitle, subtitle not found
	 */
	public function test_negative_get_subtitle(){
		$jobId = "provauuid";
		$request_data    = array(
			'data' => array(
				'url'          => 'https://test?file_name',
				'transcript'   => 'https://test',
				'attachmentId' => 1,
				'duration'     => 90,
				'jobs'         => 2,
			),
		);
		$request = new WP_REST_Request;
		$request->set_default_params( $request_data );
		$response = $this->instance->get_subtitle($request);
		$response_data = $response->data;
		$expected_response = array(
			'errors' => array(
				'status' => '404',
				'title'  => 'Invalid URL',
				'source' => 'URL not found',
			),
		);
		//verifico che la chiamata è andata a buon fine.
		$this->assertEqualSets($expected_response, $response_data);
	 }

}
