<?php
/**
 * This file implements Transcription Block.
 *
 * @author     Alessio Catania
 * @since      1.0.0
 * @package    Ear2Words\Gutenberg
 */

namespace Ear2Words\Gutenberg;

/**
 * This class describes The Gutenberg Transcription Block.
 */
class TranscriptionBlock {
	/**
	 * Init class actions.
	 */
	public function run() {
		add_action( 'init', array( $this, 'create_transcription_block' ) );
	}
	/**
	 * Registra un nuovo block type.
	 */
	public function create_transcription_block() {
		wp_register_script( 'trascription_block_script', EAR2WORDS_URL . '/build/index.js', array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data' ), 'transcription_block', false );

		register_block_type(
			'wubtitle/transcription',
			array(
				'editor-script' => 'trascription_block_script',
			)
		);
	}

}
