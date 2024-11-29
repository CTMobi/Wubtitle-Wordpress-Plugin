<?php
/**
 * This file describes the rephrase custom post type.
 *
 * @author     Damiano Di Pietro
 * @since      1.2.5
 * @package    Wubtitle\Core\CustomPostTypes
 */

namespace Wubtitle\Core\CustomPostTypes;

use Wubtitle\Core\Sources\YouTube;

/**
 * This class handle the rephrase custom post type methods.
 */
class Rephrase {
	/**
	 * Init class actions.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'init', array( $this, 'register_rephrase_cpt' ) );
	}

	/**
	 * Registers a new post type.
	 *
	 * @return void
	 */
	public function register_rephrase_cpt() {
		$labels = array(
			'name'                     => __( 'Rephrases', 'wubtitle' ),
			'singular_name'            => __( 'Rephrase', 'wubtitle' ),
			'menu_name'                => __( 'Rephrases', 'wubtitle' ),
			'all_items'                => __( 'All rephrases', 'wubtitle' ),
			'add_new'                  => __( 'Add new', 'wubtitle' ),
			'add_new_item'             => __( 'Add new rephrase', 'wubtitle' ),
			'edit_item'                => __( 'Edit rephrase', 'wubtitle' ),
			'new_item'                 => __( 'New rephrase', 'wubtitle' ),
			'view_item'                => __( 'View rephrase', 'wubtitle' ),
			'view_items'               => __( 'View rephrases', 'wubtitle' ),
			'search_items'             => __( 'Search rephrases', 'wubtitle' ),
			'not_found'                => __( 'No Rephrases found', 'wubtitle' ),
			'not_found_in_trash'       => __( 'No Rephrases found in trash', 'wubtitle' ),
			'parent'                   => __( 'Parent rephrase:', 'wubtitle' ),
			'archives'                 => __( 'Rephrase archives', 'wubtitle' ),
			'insert_into_item'         => __( 'Insert into Rephrase', 'wubtitle' ),
			'uploaded_to_this_item'    => __( 'Upload to this Rephrase', 'wubtitle' ),
			'filter_items_list'        => __( 'Filter Rephrases list', 'wubtitle' ),
			'items_list_navigation'    => __( 'Rephrases list navigation', 'wubtitle' ),
			'items_list'               => __( 'Rephrases list', 'wubtitle' ),
			'attributes'               => __( 'Rephrases attributes', 'wubtitle' ),
			'name_admin_bar'           => __( 'Rephrase', 'wubtitle' ),
			'item_published'           => __( 'Rephrase published', 'wubtitle' ),
			'item_published_privately' => __( 'Rephrase published privately.', 'wubtitle' ),
			'item_reverted_to_draft'   => __( 'Rephrase reverted to draft.', 'wubtitle' ),
			'item_scheduled'           => __( 'Rephrase scheduled', 'wubtitle' ),
			'item_updated'             => __( 'Rephrase updated.', 'wubtitle' ),
			'parent_item_colon'        => __( 'Parent rephrase:', 'wubtitle' ),
		);

		$args = array(
			'label'        => __( 'Rephrases', 'wubtitle' ),
			'labels'       => $labels,
			'description'  => __( 'Video Rephrases', 'wubtitle' ),
			'show_in_rest' => true,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'supports'     => array( 'title', 'editor', 'revisions' ),
		);

		if ( WP_DEBUG ) {
			$args['show_ui']       = true;
			$args['menu_position'] = 84;
			$args['menu_icon']     = 'dashicons-format-chat';
		}

		register_post_type( 'rephrase', $args );
	}
}
