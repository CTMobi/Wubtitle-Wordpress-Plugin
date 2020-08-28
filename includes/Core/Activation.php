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
	}

	/**
	 * When the plugin is activated calls the endpoint to receive the license key.
	 *
	 * @return void
	 */
	public function wubtitle_activation_license_key() {
		$site_url      = get_site_url();
		$wubtitle_data = get_plugin_data( WUBTITLE_FILE_URL );
		$body          = array(
			'data' => array(
				'domainUrl'     => $site_url,
				'siteLang'      => explode( '_', get_locale(), 2 )[0],
				'wpVersion'     => $GLOBALS['wp_version'],
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
