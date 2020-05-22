<?php
/**
 * Questo file implementa le funzioni relative a stripe.
 *
 * @author     Alessio Catania
 * @since      1.0.0
 * @package    Ear2Words\Api
 */

namespace Ear2Words\Api;

/**
 * Questa classe implementa le funzioni relative a stripe
 */
class ApiPricingPlan {
	/**
	 * Init delle action
	 */
	public function run() {
		add_action( 'wp_ajax_submit_plan', array( $this, 'send_plan' ) );

		add_action( 'wp_ajax_reset_license', array( $this, 'reset_license' ) );
		add_action( 'wp_ajax_reactivate_plan', array( $this, 'reactivate_plan' ) );
		add_action( 'wp_ajax_update_payment_method', array( $this, 'update_payment_method' ) );
		add_action( 'wp_ajax_change_plan', array( $this, 'change_plan' ) );
	}
	/**
	 * Esegue la chiamata all'endpoint aws per confermare il cambio del piano.
	 */
	public function change_plan() {
		$wanted_plan = get_option( 'ear2words_wanted_plan' );
		if ( ! isset( $_POST['_ajax_nonce'] ) || empty( $wanted_plan ) ) {
			wp_send_json_error( __( 'An error occurred. Please try again in a few minutes.', 'ear2words' ) );
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$body        = array(
			'data' => array(
				'planId' => $wanted_plan,
			),
			'type' => 'plan',
		);
		$license_key = get_option( 'ear2words_license_key' );
		if ( empty( $license_key ) ) {
			wp_send_json_error( __( 'Unable to create subtitles. The product license key is missing.', 'ear2words' ) );
		}
		$response      = wp_remote_post(
			ENDPOINT . 'stripe/customer/update',
			array(
				'method'  => 'POST',
				'headers' => array(
					'licenseKey'   => $license_key,
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$code_response = $this->is_successful_response( $response ) ? wp_remote_retrieve_response_code( $response ) : '500';
		$message       = array(
			'400' => __( 'An error occurred. Please try again in a few minutes', 'ear2words' ),
			'401' => __( 'An error occurred. Please try again in a few minutes', 'ear2words' ),
			'403' => __( 'Access denied', 'ear2words' ),
			'500' => __( 'Could not contact the server', 'ear2words' ),
			''    => __( 'Could not contact the server', 'ear2words' ),
		);
		if ( 200 !== $code_response ) {
			wp_send_json_error( $message[ $code_response ] );
		}
		delete_option( 'ear2words_amount_preview' );
		delete_option( 'ear2words_wanted_plan' );
		wp_send_json_success();
	}
	/**
	 * Chiama l'endpoint per fare la riattivazione del piano.
	 */
	public function reactivate_plan() {
		if ( ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'An error occurred. Please try again in a few minutes.', 'ear2words' ) );
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$license_key = get_option( 'ear2words_license_key' );
		if ( empty( $license_key ) ) {
			wp_send_json_error( __( 'Unable to create subtitles. The product license key is missing.', 'ear2words' ) );
		}
		update_option( 'ear2words_is_reactivating', true );
		$response      = wp_remote_post(
			ENDPOINT . 'stripe/customer/reactivate',
			array(
				'method'  => 'POST',
				'headers' => array(
					'licenseKey' => $license_key,
				),
			)
		);
		$code_response = $this->is_successful_response( $response ) ? wp_remote_retrieve_response_code( $response ) : '500';
		$message       = array(
			'400' => __( 'An error occurred. Please try again in a few minutes', 'ear2words' ),
			'403' => __( 'Access denied', 'ear2words' ),
			'500' => __( 'Could not contact the server', 'ear2words' ),
		);
		if ( 200 !== $code_response ) {
			wp_send_json_error( $message[ $code_response ] );
		}
		wp_send_json_success();
	}
	/**
	 *  Creo il body della richiesta.
	 *
	 * @param string $pricing_plan pricing plan.
	 * @param string $site_url url del sito.
	 */
	public function set_body_request( $pricing_plan, $site_url ) {
		if ( ! is_string( $pricing_plan ) || ! filter_var( $site_url, FILTER_VALIDATE_URL ) ) {
			return false;
		}
		return array(
			'data' => array(
				'planId'    => $pricing_plan,
				'domainUrl' => $site_url,
				'siteLang'  => explode( '_', get_locale(), 2 )[0],
			),
		);
	}
	/**
	 * Riceve i dati da javascript e li invia all'endpoint.
	 */
	public function send_plan() {
		$site_url = get_site_url();
		if ( ! isset( $_POST['_ajax_nonce'] ) || ! isset( $_POST['pricing_plan'] ) || ! isset( $site_url ) ) {
			wp_send_json_error( __( 'An error occurred. Please try again in a few minutes.', 'ear2words' ) );
		}
		$pricing_plan = sanitize_text_field( wp_unslash( $_POST['pricing_plan'] ) );
		$site_url     = sanitize_text_field( wp_unslash( $site_url ) );
		$nonce        = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$body         = $this->set_body_request( $pricing_plan, $site_url );
		$url_endpoint = ENDPOINT . 'stripe/session/create';
		$license_key  = get_option( 'ear2words_license_key' );
		if ( empty( $license_key ) ) {
			wp_send_json_error( __( 'Unable to create subtitles. The product license key is missing.', 'ear2words' ) );
		}
		// se non è free contatto l'endpoint per aggiorna il piano.
		if ( ! get_option( 'ear2words_free' ) ) {
			$url_endpoint = ENDPOINT . 'stripe/customer/update/preview';
			unset( $body['data']['siteLang'] );
			unset( $body['data']['domainUrl'] );
		}
		$response      = wp_remote_post(
			$url_endpoint,
			array(
				'method'  => 'POST',
				'headers' => array(
					'licenseKey'   => $license_key,
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$code_response = $this->is_successful_response( $response ) ? wp_remote_retrieve_response_code( $response ) : '500';
		$message       = array(
			'400' => __( 'An error occurred. Please try again in a few minutes', 'ear2words' ),
			'401' => __( 'An error occurred. Please try again in a few minutes', 'ear2words' ),
			'403' => __( 'Access denied', 'ear2words' ),
			'500' => __( 'Could not contact the server', 'ear2words' ),
			''    => __( 'Could not contact the server', 'ear2words' ),
		);
		// 200 se è un downgrade o un upgrade
		if ( 200 === $code_response ) {
			$response_body  = json_decode( wp_remote_retrieve_body( $response ) );
			$amount_preview = $response_body->data->amountPreview;
			update_option( 'ear2words_amount_preview', $amount_preview );
			update_option( 'ear2words_wanted_plan', $pricing_plan );
			wp_send_json_success( 'change_plan' );
		}
		// 201 se è il primo pagamento
		if ( 201 !== $code_response ) {
			wp_send_json_error( $message[ $code_response ] );
		}
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$session_id    = $response_body->data->sessionId;
		wp_send_json_success( $session_id );
	}
	/**
	 * Riceve i dati da javascript e li invia all'endpoint per effettuare l'aggiornamento dei dati di pagamento.
	 */
	public function update_payment_method() {
		if ( ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'An error occurred. Please try again in a few minutes.', 'ear2words' ) );
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$license_key = get_option( 'ear2words_license_key' );
		if ( empty( $license_key ) ) {
			wp_send_json_error( __( 'Unable to create subtitles. The product license key is missing.', 'ear2words' ) );
		}
		$body          = array(
			'type' => 'payment',
		);
		$response      = wp_remote_post(
			ENDPOINT . 'stripe/customer/update',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
					'licenseKey'   => $license_key,
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$code_response = $this->is_successful_response( $response ) ? wp_remote_retrieve_response_code( $response ) : '500';
		$message       = array(
			'400' => __( 'An error occurred. Please try again in a few minutes', 'ear2words' ),
			'401' => __( 'An error occurred. Please try again in a few minutes', 'ear2words' ),
			'403' => __( 'Access denied', 'ear2words' ),
			'500' => __( 'Could not contact the server', 'ear2words' ),
			''    => __( 'Could not contact the server', 'ear2words' ),
		);
		if ( 201 !== $code_response ) {
			wp_send_json_error( $message[ $code_response ] );
		}
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$session_id    = $response_body->data->sessionId;
		wp_send_json_success( $session_id );
	}
	/**
	 * Riceve i dati da javascript e li invia all'endpoint per resettare la licenza.
	 */
	public function reset_license() {
		$site_url = get_site_url();
		if ( ! isset( $_POST['_ajax_nonce'] ) ) {
			wp_send_json_error( __( 'An error occurred. Please try again in a few minutes.', 'ear2words' ) );
		}
		$nonce    = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
		$site_url = sanitize_text_field( wp_unslash( $site_url ) );
		check_ajax_referer( 'itr_ajax_nonce', $nonce );
		$body          = array(
			'data' => array(
				'domainUrl' => $site_url,
			),
		);
		$response      = wp_remote_post(
			ENDPOINT . 'key/fetch',
			array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'    => wp_json_encode( $body ),
			)
		);
		$code_response = $this->is_successful_response( $response ) ? wp_remote_retrieve_response_code( $response ) : '500';
		$message       = array(
			'400' => __( 'An error occurred. Please try again in a few minutes', 'ear2words' ),
			'403' => __( 'Access denied', 'ear2words' ),
			'500' => __( 'Could not contact the server', 'ear2words' ),
		);
		if ( 200 !== $code_response ) {
			wp_send_json_error( $message[ $code_response ] );
		}
		wp_send_json_success();
	}
	/**
	 * Verifico che la chiamata non sia andata in errore.
	 *
	 * @param array | WP_ERROR $response risposta chiamata.
	 */
	private function is_successful_response( $response ) {
		if ( ! is_wp_error( $response ) ) {
			return true;
		}
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions
			error_log( print_r( $response->get_error_message(), true ) );
			// phpcs:enable
		}
		return false;
	}
}
