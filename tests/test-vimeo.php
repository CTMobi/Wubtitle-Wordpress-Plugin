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
     /**
      * Test Vimeo get transcript 
      */
      public function test_get_transcript() {
          $content  = 'text content of transcription';
          $id_video = 'test_id';

          $vimeo_source     = $this->getMockBuilder( Vimeo::class )->setMethods( array( 'send_job_to_backend' ) )->getMock();
          $body             = array(
              'data' => array(
                  'text' => $content,
              ),
          );
          $response_backend = array(
              'response'      => array(
                  'code' => 201,
              ),
              'body'          => wp_json_encode( $body ),
          );

          $vimeo_source->expects( $this->once() )->method( 'send_job_to_backend' )->will( $this->returnValue( $response_backend ) );
          $response = $vimeo_source->get_transcript( $id_video, 'test title', 'default_post_type' );
          $args     = array(
            'post_type'      => 'transcript',
            'posts_per_page' => 1,
            'meta_key'       => '_video_id',
            'meta_value'     => $id_video,
          );
          
          $posts = get_posts( $args );
          $this->assertTrue( $response['success'] );
          $this->assertEquals( $content, $posts[0]->post_content );
          $this->assertEquals( $response['data'], $posts[0]->ID );
      }
}