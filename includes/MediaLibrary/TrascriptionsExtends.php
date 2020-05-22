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
		add_filter( 'embed_oembed_html', array( $this, 'ear2words_embed_shortcode' ), 10, 4 );
		add_action( 'media_buttons', array( $this, 'add_transcriptions_media_button' ), 15 );
		add_action( 'wp_enqueue_media', array( $this, 'include_transcription_modal_script' ) );
	}
	/**
	 * Fa l'override dello shortcode.
	 *
	 * @param string $html html dello shortcode nativo.
	 * @param string $url url del video.
	 * @param array  $attr attributi dello shortcode.
	 */
	public function ear2words_embed_shortcode( $html, $url, $attr ) {
		$url_parts    = wp_parse_url( $url );
		$allowed_urls = array(
			'www.youtube.com',
			'www.youtu.be',
		);
		if ( empty( $attr['transcription'] ) || 'enable' !== $attr['transcription'] ) {
			return $html;
		}
		if ( ! in_array( $url_parts['host'], $allowed_urls, true ) ) {
			$html = '<p style="color:red">' . __( 'Url not a valid youtube url', 'ear2words' ) . '</p>';
			return $html;
		}
		$transcript_response = Loader::get( 'youtube_source' )->send_job_and_get_transcription( $url, 'default_post_type' );
		if ( ! $transcript_response['success'] ) {
			$html = '<p style="color:red">' . $transcript_response['data'] . '</p>';
			return $html;
		}
		$html .= '[transcript id="' . $transcript_response['data'] . '" ]';
		return $html;
	}
	/**
	 * Include il file javascript.
	 */
	public function include_transcription_modal_script() {
		wp_enqueue_script( 'transcription_modal_script', EAR2WORDS_URL . '/src/editor/transcriptionModalScript.js', null, 'transcription_script', true );
	}
	/**
	 * Aggiunge il bottone custom.
	 */
	public function add_transcriptions_media_button() {
		echo '<a href="#" id="insert-my-media" class="button">Add transcription</a>';
	}
}
