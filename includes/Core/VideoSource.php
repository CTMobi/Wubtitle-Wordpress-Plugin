<?php
/**
 * This file describes handle operation on subtitle.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Wubtitle\Core
 */

namespace Wubtitle\Core;

/**
 * This class handle subtitles.
 */
interface VideoSource {

	/**
	 * Interface method send job to backend.
	 *
	 * @param string $id_video video id.
	 * @return array<string>|\WP_Error
	 */
	public function send_job_to_backend( $id_video );

	/**
	 * Interface method for calling and retrieving transcripts.
	 *
	 * @param string $id_video embed video id.
	 * @param string $url_subtitle url video embed subtitle.
	 * @param string $video_title video title.
	 * @param string $from where the request comes from.
	 *
	 * @return array<mixed>
	 */
	public function get_transcript( $id_video, $url_subtitle, $video_title, $from );

	/**
	 * Interface method for retrieving transcripts from url.
	 *
	 * @param string $url_subtitle url sottotitoli youtube.
	 * @param string $id_video id video.
	 * @param string $title_video titolo video.
	 * @return bool|string|int
	 */
	public function get_subtitle_to_url( $url_subtitle, $id_video, $title_video );

	/**
	 * Interface method for get video info
	 *
	 * @param array<string> $url_parts parts of url.
	 *
	 * @return array<mixed>|false
	 */
	public function get_video_info( $url_parts );
}
