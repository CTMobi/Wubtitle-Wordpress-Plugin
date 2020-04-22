<?php
/**
 * This file implements.
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Ear2Words\Api
 */

namespace Ear2Words\Api;

/**
 * This class describes.
 */
class ApiCancelSubscription {
	/**
	 * Init class actions
	 */
	public function run() {
		add_action( 'wp_ajax_cancel_subscription', array( $this, 'remote_request' ) );
	}

	/**
	 * Chiamata ad endpoint remoto per richiesta cancellazione.
	 */
	public function remote_request() {
		if ( ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'No Nonce', 'ear2words' ) );
		} elseif ( ! isset( $_POST['action'] ) ) {
			wp_send_json_error( __( 'No Cancel', 'ear2words' ) );
		} elseif ( ! check_ajax_referer( 'itr_ajax_nonce', $nonce ) ) {
			wp_send_json_error( __( 'Invalid', 'ear2words' ) );
		}

		$license_key = get_option( 'ear2words_license_key' );
		$nonce       = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );

		$response = wp_remote_post(
			'http://ca3bed8a.ngrok.io/stripe/customer/unsubscribe',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
					'licenseKey'   => $license_key,
				),
			)
		);

		$code_response = wp_remote_retrieve_response_code( $response );

		$message = array(
			'200' => __( 'Cancellato correttamente', 'ear2words' ),
			'400' => __( 'Bad Request. Please try again in a few minutes', 'ear2words' ),
			'401' => __( 'Unauthorized', 'ear2words' ),
			'403' => __( 'Forbidden', 'ear2words' ),
			'404' => __( 'Non trovato', 'ear2words' ),
			'500' => __( 'Int ser error', 'ear2words' ),
			'502' => __( 'bad gate', 'ear2words' ),
		);
		wp_send_json_success( $message[ $code_response ] );
	}


}
