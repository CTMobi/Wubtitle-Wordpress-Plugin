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
		add_filter( 'rest_transcript_query', array( $this, 'add_parameters_query' ), 10, 2 );
	}
	/**
	 * Aggiunge i parametri per il posttype transcript.
	 *
	 * @param array  $args argomenti per la query.
	 * @param object $request oggetto contente i parametri custom.
	 */
	public function add_parameters_query( $args, $request ) {
		$url_parts    = wp_parse_url( $request->get_param( 'metaValue' ) );
		$query_params = array();
		parse_str( $url_parts['query'], $query_params );
		$id_video = $query_params['v'];
		if ( $request->get_param( 'metaKey' ) ) {
				$args['meta_key']   = $request->get_param( 'metaKey' );
				$args['meta_value'] = $id_video;
		}
		return $args;
	}
	/**
	 * Registra un nuovo block type.
	 */
	public function create_transcription_block() {
		wp_register_script( 'trascription_block_script', EAR2WORDS_URL . '/build/index.js', array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-data' ), 'transcription_block', false );
		wp_register_style( 'stylesheet_transcription_block', EAR2WORDS_URL . '/src/css/transBlockStyle.css', null, 'transcript_block_style', false );

		register_block_type(
			'wubtitle/transcription',
			array(
				'editor_style' => 'stylesheet_transcription_block',
			)
		);
	}

}
