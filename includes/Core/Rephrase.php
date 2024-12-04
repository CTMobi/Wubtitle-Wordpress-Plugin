<?php
/**
 * This file handles operations on the wubtitle_rephrase_info table.
 *
 * @author     Damiano Di Pietro
 * @since      1.2.5
 * @package    Wubtitle\Core
 */

namespace Wubtitle\Core;

/**
 * This class handles the rephrase information.
 */
class Rephrase {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	private $table_name = 'wubtitle_rephrase_info';

	/**
	 * Cache prefix for rephrase info.
	 *
	 * @var string
	 */
	private $cache_prefix = 'rephrase_info_';

	/**
	 * Insert the rephrase info.
	 *
	 * @param array<string,string> $data     The data to insert.
	 * @return int|false
	 */
	public function insert_rephrase_info( $data ) {

		if ( ! empty( array_diff( array( 'title', 'id_video', 'status' ), array_keys( $data ) ) ) ) {
			return false;
		}

		global $wpdb;

		$rephrase_table = $wpdb->prefix . $this->table_name;

		// Every field is a string.
		$format = '%s';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->insert( $rephrase_table, $data, $format );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( empty( $inserted ) ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Handle the rephrase info deletion.
	 *
	 * @param string $id_video video_id or attachment_id.
	 * @return int|false
	 */
	public function delete_rephrase_info( $id_video ) {
		global $wpdb;

		$rephrase_table = $wpdb->prefix . $this->table_name;
		$cache_key      = $this->cache_prefix . $id_video;
		wp_cache_delete( $cache_key, WUBTITLE_NAME );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->delete(
			$rephrase_table,
			array( 'id_video' => $id_video ),
			array( '%s' )
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	}

	/**
	 * Get the rephrase info.
	 *
	 * @param string $id_video video_id or attachment_id.
	 * @return object|null
	 */
	public function get_rephrase_info( $id_video ) {
		global $wpdb;

		$rephrase_table = $wpdb->prefix . $this->table_name;
		$cache_key      = $this->cache_prefix . $id_video;

		// Try to get cached result.
		$rephrase_info = wp_cache_get( $cache_key, WUBTITLE_NAME );

		if ( false === $rephrase_info ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
			$rephrase_info = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE id_video = %s', array( $rephrase_table, $id_video ) ) );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

			// Store the result on the cache.
			wp_cache_set( $cache_key, $rephrase_info, WUBTITLE_NAME );
		}

		return $rephrase_info;
	}


	/**
	 * Update the rephrase info.
	 *
	 * @param string               $id_video video_id or attachment_id.
	 * @param array<string,string> $data     The data to update.
	 * @return int|false
	 */
	public function update_rephrase_info( $id_video, $data ) {
		global $wpdb;

		$rephrase_table = $wpdb->prefix . $this->table_name;
		$cache_key      = $this->cache_prefix . $id_video;
		wp_cache_delete( $cache_key, WUBTITLE_NAME );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->update(
			$rephrase_table,
			$data,
			array( 'id_video' => $id_video )
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.DirectQuery
	}
}
