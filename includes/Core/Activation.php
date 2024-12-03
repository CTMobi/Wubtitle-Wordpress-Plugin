<?php
/**
 * In this file is implemented the functions performed when the plugin is activated.
 *
 * @author     Alessio Catania
 * @since      0.1.0
 * @package    Wubtitle\Api
 */

namespace Wubtitle\Core;

/**
 * This class implements the functions performed when the plugin is activated.
 */
class Activation {
	/**
	 * Init class action.
	 *
	 * @return void
	 */
	public function run() {
		register_activation_hook( WUBTITLE_FILE_URL, array( $this, 'wubtitle_activation_license_key' ) );
		register_activation_hook( WUBTITLE_FILE_URL, array( $this, 'wubtitle_create_rephrase_info_table' ) );
		add_action( 'upgrader_process_complete', array( $this, 'post_plugin_upgrade' ), 10, 2 );
		add_action( '_core_updated_successfully', array( $this, 'wubtitle_activation_license_key' ), 10, 1 );
		add_filter( 'upgrader_post_install', array( $this, 'post_install' ), 10, 3 );
	}

	/**
	 * Execute operations after the plugin is upgraded.
	 *
	 * This function checks if the current action is an update and if the type is a plugin.
	 * If the plugin being updated is the current plugin, it calls the function to create
	 * or update the rephrase info table.
	 *
	 * @param object                                                                                                         $upgrader_object   The upgrader object.
	 * @param array{action:string,type:string,bulk:bool,plugins:array<string>,themes:array<mixed>,translations:array<mixed>} $options           An array of options for the upgrade process.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @return void
	 */
	public function post_plugin_upgrade( $upgrader_object, $options ) {
		$action                   = ! empty( $options['action'] ) ? $options['action'] : '';
		$type                     = ! empty( $options['type'] ) ? $options['type'] : '';
		$plugins                  = ! empty( $options['plugins'] ) ? $options['plugins'] : array();
		$current_plugin_path_name = plugin_basename( __FILE__ );

		if ( 'update' === $action && 'plugin' === $type && in_array( $current_plugin_path_name, $plugins, true ) ) {
			$this->wubtitle_create_rephrase_info_table();
		}
	}


	/**
	 * Create or update the table to store the rephrase statuses on plugin activation or update.
	 *
	 * @return void
	 */
	public function wubtitle_create_rephrase_info_table() {
		$wubtitle_version_option = get_option( 'wubtitle_version', '0.0.0' );

		if ( version_compare( $wubtitle_version_option, '1.2.5', '<' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			global $wpdb;

			$table_name      = "{$wpdb->prefix}wubtitle_rephrase_info";
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				id_video varchar(50) NOT NULL,
				status varchar(15) NOT NULL,
				PRIMARY KEY (id),
				UNIQUE KEY id_video (id_video)
			) $charset_collate;";

			dbDelta( $sql );

			update_option( 'wubtitle_version', WUBTITLE_VER );
		}
	}

	/**
	 * After upgrade run plugin activation.
	 *
	 * @param array<mixed> ...$args installation result data.
	 * @return void
	 */
	public function post_install( ...$args ) {
		$name_plugin = $args[1]['plugin'];
		if ( WUBTITLE_NAME . '/wubtitle.php' === $name_plugin ) {
			$this->wubtitle_activation_license_key();
		}
	}

	/**
	 * When the plugin is activated calls the endpoint to receive the license key.
	 *
	 * @param string $wp_version WordPress version.
	 *
	 * @return void
	 */
	public function wubtitle_activation_license_key( $wp_version = '' ) {
		$site_url      = get_option( 'siteurl' );
		$wubtitle_data = get_plugin_data( WUBTITLE_FILE_URL );
		$body          = array(
			'data' => array(
				'domainUrl'     => $site_url,
				'siteLang'      => explode( '_', get_locale(), 2 )[0],
				'wpVersion'     => empty( $wp_version ) ? $GLOBALS['wp_version'] : $wp_version,
				'pluginVersion' => $wubtitle_data['Version'],
			),
		);
		$response      = wp_remote_post(
			WUBTITLE_ENDPOINT . 'key/create',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$code_response = wp_remote_retrieve_response_code( $response );
		if ( 200 === $code_response ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );
			update_option( 'wubtitle_token', $response_body->data->token, false );
			update_option( 'wubtitle_token_time', time() + ( MINUTE_IN_SECONDS * 5 ), false );
		}
	}
}
