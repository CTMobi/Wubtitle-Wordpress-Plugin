<?php
use Wubtitle\MediaLibrary\MediaLibraryExtented;
/**
 * Class MediaLibraryExtented
 *
 * @package Wubtitle
 */

/**
 * Test media library extented.
 */
class TestMediaLibraryExtented extends WP_UnitTestCase {
    /**
     * Setup function.
     */
    public function SetUp(){
        parent::setUp();
        $this->instance = new MediaLibraryExtented;
    }

    /**
     * Test add enqueue script.
     */
    public function test_enqueue_script(){
        $this->instance->wubtitle_medialibrary_style();
        $this->assertTrue( wp_style_is( 'wubtitle_medialibrary_style' ) );
     }

    /**
     * Test generate subtitle form.
     */
    public function test_generate_subtitle_form(){
        $form_fields     = array();
        $status          = 'pending';
        $attachment_data = array(
            'guid'            => '/test',
            'post_mime_type'  => 'video/mp4',
            'post_title'      => 'test',
            'post_content'    => '',
        );
        $attachment_id   = self::factory()->attachment->create( $attachment_data );
        update_post_meta( $attachment_id, '_wp_attached_file', '/test/path/test.mp4' );
        update_post_meta( $attachment_id, 'wubtitle_status', 'pending' );
        update_post_meta( $attachment_id, 'wubtitle_lang_video', 'en' );
        update_option( 'wubtitle_free', false );

        $expected_html_data   = '<label for="attachments-' . $attachment_id . '-e2w_status">Generating</label>';
        $expected_data_status = array(
            'label' => 'Subtitle',
            'input' => 'html',
            'html'  => $expected_html_data,
            'value' => $attachment_id,
        );

        $expected_html_data = '<label for="attachments-' . $attachment_id . '-e2w_lang">English</label>';
        $expected_data_lang = array(
            'label' => 'Language',
            'input' => 'html',
            'html'  => $expected_html_data,
            'value' => $attachment_id,
        );

        $post = get_post( $attachment_id );
        $GLOBALS['pagenow'] = 'admin-ajax.php';
        $response = $this->instance->add_generate_subtitle_form( $form_fields, $post );
        $this->assertEqualSets( $expected_data_status , $response['e2w_status']);
        $this->assertEqualSets( $expected_data_lang , $response['e2w_lang']);
     }

    /**
     * Test generate subtitle form into medialibrary.
     */
    public function test_generate_subtitle_form_into_media_library(){
        $form_fields     = array();
        $status          = 'pending';
        $attachment_data = array(
            'guid'            => '/test',
            'post_mime_type'  => 'video/mp4',
            'post_title'      => 'test',
            'post_content'    => '',
        );
        $attachment_id   = self::factory()->attachment->create( $attachment_data );
        update_post_meta( $attachment_id, '_wp_attached_file', '/test/path/test.mp4' );
        update_post_meta( $attachment_id, 'wubtitle_status', 'pending' );
        update_post_meta( $attachment_id, 'wubtitle_lang_video', 'en' );
        update_option( 'wubtitle_free', false );

        $expected_html_data   = '<label for="attachments-' . $attachment_id . '-e2w_status">Generating</label>';
        $expected_data_status = array(
            'label' => 'Status',
            'input' => 'html',
            'html'  => $expected_html_data,
            'value' => $attachment_id,
        );

        $expected_html_data = '<label for="attachments-' . $attachment_id . '-e2w_lang">English</label>';
        $expected_data_lang = array(
            'label' => 'Language',
            'input' => 'html',
            'html'  => $expected_html_data,
            'value' => $attachment_id,
            'helps' => 'Wait while subtitles are created. Subtitles will be available as soon as possible',
        );

        $post = get_post( $attachment_id );
        $GLOBALS['pagenow'] = 'post.php';
        $response = $this->instance->add_generate_subtitle_form_into_media_library( $form_fields, $post );
        $this->assertEqualSets( $expected_data_status , $response['e2w_status']);
        $this->assertEqualSets( $expected_data_lang , $response['e2w_lang']);
     }

}