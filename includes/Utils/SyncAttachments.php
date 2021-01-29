<?php
/**
 * Helper to sync attachments and add wpml plugin support.
 *
 * @author     Alessio Catania
 * @since      1.2.1
 * @package    Wubtitle\Utils
 */

namespace Wubtitle\Utils;

/**
 * Class helper to sync attachments post meta.
 */
class SyncAttachments {

	/**
	 * Init action
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'updated_post_meta', array( $this, 'sync_post_meta' ), 10, 4 );
	}

	/**
	 * Recover all attachment clones and duplicate wubtitle post meta.
	 *
	 * @param string|int ...$args attachment info.
	 * @return void
	 */
	public function sync_post_meta( ...$args ) {
		$object_id  = $args[1];
		$meta_key   = $args[2];
		$meta_value = $args[3];
		global $sitepress;
		global $wpdb;

		$sync_meta_keys = array(
			'wubtitle_lang_video',
			'wubtitle_job_uuid',
			'wubtitle_status',
			'wubtitle_subtitle',
			'is_subtitle',
		);

		if ( in_array( $meta_key, $sync_meta_keys, true ) ) {
			$trid               = $sitepress->get_element_trid( $object_id, 'post_attachment' );
			$translations_query = $wpdb->prepare( "SELECT * FROM wp_icl_translations WHERE trid = %d AND element_type = 'post_attachment'", $trid );
			// phpcs:disable
			$translations       = $wpdb->get_results( $translations_query );
			// phpcs:enable

			foreach ( $translations as $translation ) {
				if ( $translation->element_id !== $object_id ) {
					update_post_meta( $translation->element_id, $meta_key, $meta_value );
				}
			}
		}
	}

}
