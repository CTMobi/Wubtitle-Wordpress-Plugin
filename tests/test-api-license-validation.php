<?php
use Wubtitle\Api\ApiLicenseValidation;
/**
 * Class TestApiLivenseValidation
 *
 * @package Wubtitle
 */

 /**
	* Test callback endpoint.
	*/
class TestApiLivenseValidation extends WP_UnitTestCase {
	/**
	 * Setup function.
	 */
	public function SetUp(){
		parent::setUp();
		$this->instance = new ApiLicenseValidation;
	}
	/**
	 * Test endpoint callback reset user
	 */
	 public function test_get_init_data(){
        $plans = array(
            array(
                'name'             => 'name_1',
                'id'               => 'id_1',
                'totalJobs'        => 'jobs_1',
                'totalSeconds'     => 'seconds_1',
                'dotlist'          => 'list_1',
                'icon'             => 'icon_1',
                'rank'             => 0,
                'supportedFormats' => ['all'],
            ),
            array(
                'name'             => 'name_2',
                'id'               => 'id_2',
                'totalJobs'        => 'jobs_2',
                'totalSeconds'     => 'seconds_2',
                'dotlist'          => 'list_2',
                'icon'             => 'icon_2',
                'rank'             => 1,
                'supportedFormats' => ['all'],
            ),
        );
		$body = array(
            'data' => array(
                'isFree'     => false,
                'licenseKey' => 'license_test',
                'plans'      => $plans,
            ),
        );
        update_option( 'wubtitle_token', 'token' );
        update_option( 'wubtitle_token_time', time() );
		$request = new WP_REST_Request;
		$request->set_body( wp_json_encode( $body ) );
		$response = $this->instance->get_init_data($request);
        $result_token      = get_option('wubtitle_token');
        $result_token_time = get_option('wubtitle_token_time');
		$expected_response = array(
			'data' => array(
				'status' => '200',
				'title'  => 'Success',
			),
		);
		$expected_status = 'error';
		//Check callback response
		$this->assertEqualSets($expected_response, $response);
		//Check that the token anche token time have been deleted
        $this->assertFalse($result_token);
        $this->assertFalse($result_token_time);
     }
     /**
      * Test get job list
      */
      public function test_get_job_list() {
        $attachments_data = array(
            array(
                'guid'           => '/test',
                'post_mime_type' => 'video_1',
                'post_title'     => 'test',
                'post_content'   => '',
            ),
            array(
                'guid'           => '/test',
                'post_mime_type' => 'video_2',
                'post_title'     => 'test',
                'post_content'   => '',
            ),
        );
        $expected_job_list = array(
            'data' => array(
                'job_list' => array(
                    'job_test_2',
                    'job_uuid_test',
                ), 
            ),
        );
        $attachment_id = self::factory()->attachment->create($attachments_data[0]);
        update_post_meta( $attachment_id, 'wubtitle_job_uuid', $expected_job_list['data']['job_list'][0]);
        update_post_meta( $attachment_id, 'wubtitle_status', 'pending');
        $attachment_id = self::factory()->attachment->create($attachments_data[1]);
        update_post_meta( $attachment_id, 'wubtitle_job_uuid', $expected_job_list['data']['job_list'][1]);
        update_post_meta( $attachment_id, 'wubtitle_status', 'pending');
        $response_job_list = $this->instance->get_job_list();
        $job_list          = $response_job_list['data']['job_list'];
        sort( $job_list );
        $this->assertEqualSets( $expected_job_list['data']['job_list'], $job_list );
      }
}