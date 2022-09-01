<?php
/**
 * Class TestFilter
 *
 * @package Wubtitle
 */

 /**
  * Test filter.
  */
  class TestFilter extends WP_Ajax_UnitTestCase {
    /**
     * Setup function.
     */
     public function SetUp(): void{
       parent::setUp();
       //Setto come current screen una schermata admin
       set_current_screen('upload');
       //aggiungo un'attachment per i sottotitoli
       $attachment_data = array(
          'guid'           => 'http://wordpress01.local/wp-content/uploads/2020/04/sottotitoli.vtt',
          'post_mime_type' => 'text/vtt',
          'post_title'     => 'sottotitoli',
          'post_content'   => '',
          'meta_input'     => array(
            'is_subtitle'    => 'true',
          )
        );
        //error_log(print_r($attachment_data,true));
        $subtitle_id = $this->factory()->attachment->create($attachment_data,['/sottotitoli.vtt'],1);
        $this->expected = $subtitle_id;
        //aggiungo un'attachment per il video
        $attachment_data = array(
           'guid'           => 'http://wordpress01.local/wp-content/uploads/2020/04/video.mp4',
           'post_mime_type' => 'video/mp4',
           'post_title'     => 'video',
           'post_content'   => '',
           'meta_input'     => array(
             'wubtitle_subtitle' => $subtitle_id
           )
         );
         $attachment_id = $this->factory()->attachment->create($attachment_data,['/video.mp4'],2);
     }
     /**
      * tearDown function.
      */
      public function tearDown(): void{
        parent::tearDown();
      }
      /**
       * Verifica che vengono correttamente filtrati i sottotitoli.
       */
       public function test_filter_attachment(){
          $query = new WP_Query(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'video/mp4',
            'post_status'    => 'any'
          ));
          $media = $query->posts;
          $subtitle_id = $this->expected;
          $result_id = get_post_meta($media[0]->ID,'wubtitle_subtitle',true);
          $this->assertEquals(1,count($media));
          $this->assertEquals('video',$media[0]->post_title);
          $this->assertEquals($subtitle_id,$result_id);
          $this->assertInstanceOf('WP_Post',get_post($subtitle_id));
       }
  }
