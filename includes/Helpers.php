<?php
/**
 * This file handles a class of helper methods.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Ear2Words
 */

namespace Ear2Words;

/**
 * This class handles some helper methods used throughout the plugin.
 */
class Helpers {

	/**
	 * Verifica se gutenberg è attivo.
	 */
	public function is_gutenberg_active() {
		// Gutenberg plugin is installed and activated.
		$gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

		// Block editor since 5.0.
		$block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}

		if ( $this->is_classic_editor_active() ) {
			$editor_option       = get_option( 'classic-editor-replace' );
			$block_editor_active = array( 'no-replace', 'block' );

			return in_array( $editor_option, $block_editor_active, true );
		}

		return true;
	}
	/**
	 * Verifica se il classic editor è attivo.
	 */
	public function is_classic_editor_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( 'classic-editor/classic-editor.php' );
	}

}
