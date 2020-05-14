<?php
/**
 * This file describes handle operation on subtitle.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Ear2Words\Core
 */

namespace Ear2Words\Core;

/**
 * This class handle subtitles.
 */
interface VideoSource {
	/**
	 * Interface method.
	 *
	 * @param string $id_video id del del video.
	 * @param string $from post type dal quale viene fatta la richiesta.
	 */
	public function get_subtitle( $id_video, $from );
}
