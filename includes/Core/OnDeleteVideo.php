<?php
/**
 * This file implements .
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Ear2Words\Core
 */

namespace Ear2Words\Core;

/**
 * This class describes .
 */
class OnDeleteVideo {
	/**
	 * Init class actions
	 */
	public function run() {
		add_filter( 'on_delete_video', array( $this, 'delete_subtitle' ) );
	}

	/**
	 * Crea .
	 */
	public function delete_subtitle() {
	}

}
