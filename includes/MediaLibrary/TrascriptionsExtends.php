<?php
/**
 * This file extends media library.
 *
 * @author     Alessio Catania
 * @since      1.0.0
 * @package    Ear2Words\Gutenberg
 */

namespace Ear2Words\MediaLibrary;

use Ear2Words\Loader;

/**
 * Classe che estende la media library aggiungendo la sezione per le trascrizioni
 */
class TrascriptionsExtends {
	/**
	 * Setup delle action.
	 */
	public function run() {
		add_action( 'media_buttons', array( $this, 'add_transcriptions_media_button' ), 15 );
		add_action( 'wp_enqueue_media', array( $this, 'include_transcription_modal_script' ) );
	}
	/**
	 * Include il file javascript.
	 */
	public function include_transcription_modal_script() {
		wp_enqueue_script( 'transcription_modal_script', EAR2WORDS_URL . '/src/editor/transcriptionModalScript.js', null, 'transcription_script', true );
		wp_set_script_translations( 'transcription_modal_script', 'ear2words', EAR2WORDS_DIR . 'languages' );
		wp_localize_script(
			'transcription_modal_script',
			'wubtitle_object_modal',
			array(
				'ajaxnonce' => wp_create_nonce( 'itr_ajax_nonce' ),
			)
		);
	}
	/**
	 * Aggiunge il bottone custom.
	 */
	public function add_transcriptions_media_button() {
		echo '<a href="#" id="insert-my-media" class="button">' . esc_html( __( 'Add transcription', 'ear2words' ) ) . '</a>';
	}
}
