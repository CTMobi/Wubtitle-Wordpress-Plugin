<?php
/**
 * This file implements Video Block.
 *
 * @author     Alessio Catania
 * @since      0.1.0
 * @package    Ear2Words\Gutenberg
 */

namespace Ear2Words\Gutenberg;

use Ear2Words\Loader;

/**
 * This class describes The Gutenberg video block.
 */
class VideoBlock {
	/**
	 * Init class actions
	 */
	public function run() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'add_subtitle_button_enqueue' ) );
		add_action( 'init', array( $this, 'video_block_dynamic' ) );
	}
	/**
	 * Enqueue degli script.
	 */
	public function add_subtitle_button_enqueue() {
		wp_enqueue_script( 'add_subtitle_button-script', plugins_url( '../../build/index.js', __FILE__ ), array( 'wp-compose', 'wp-data', 'wp-element', 'wp-hooks', 'wp-api-fetch', 'wp-components', 'wp-block-editor', 'wp-edit-post', 'wp-i18n' ), 'add_subtitle_button', false );
		wp_set_script_translations( 'add_subtitle_button-script', 'ear2words', EAR2WORDS_DIR . 'languages' );
		wp_localize_script(
			'add_subtitle_button-script',
			'ear2words_button_object',
			array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'ajaxnonce' => wp_create_nonce( 'itr_ajax_nonce' ),
				'lang'      => explode( '_', get_locale(), 2 )[0],
				'isFree'    => get_option( 'ear2words_free' ),
			)
		);
	}
	/**
	 * Registro il block type facendo override al blocco core/video.
	 */
	public function video_block_dynamic() {
		register_block_type(
			'core/video',
			array(
				'render_callback' => array( $this, 'video_dynamic_block_render_callback' ),
			)
		);
	}
	/**
	 * Callback che definisce il blocco dinamico.
	 *
	 * @param array  $attributes attributi del video (id).
	 * @param string $content html generato da wordress per il blocco video standard.
	 */
	public function video_dynamic_block_render_callback( $attributes, $content ) {
		wp_enqueue_style( 'ear2words_test', EAR2WORDS_URL . '/src/css/subtitles.css', null, true );
		if ( empty( $attributes['id'] ) ) {
			return $content;
		}
		$subtitle     = get_post_meta( $attributes['id'], 'ear2words_subtitle', true );
		$subtitle_src = wp_get_attachment_url( $subtitle );
		$video_src    = wp_get_attachment_url( $attributes['id'] );
		$lang         = Loader::get( 'extented_media_library' )->get_video_language( $attributes['id'] );
		$status       = get_post_meta( $attributes['id'], 'ear2words_status', true );
		if ( '' === $subtitle || 'enabled' !== $status ) {
			return $content;
		}
		ob_start();
		?>
		<figure class="wp-block-video">
			<video controls src= "<?php echo esc_html( $video_src ); ?>">
			<track label="<?php echo esc_attr( $lang ); ?>" kind="subtitles" src=" <?php echo esc_html( $subtitle_src ); ?>" default>
			</video>
		</figure>
		<?php
		return ob_get_clean();
	}
}
