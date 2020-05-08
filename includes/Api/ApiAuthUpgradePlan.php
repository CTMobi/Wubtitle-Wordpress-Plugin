<?php
/**
 * Questo file crea un nuovo endpoint per l'autorizzazione al cambio di piano.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Ear2Words\Api
 */

namespace Ear2Words\Api;

use WP_REST_Response;
use \Firebase\JWT\JWT;

/**
 * Questa classe gestisce l'autorizzazione al cambio di piano.
 */
class ApiAuthUpgradePlan {
	/**
	 * Init class action.
	 */
	public function run() {
		add_action( 'rest_api_init', array( $this, 'register_auth_plan_route' ) );
		add_action( 'rest_api_init', array( $this, 'register_reactivate_plan_route' ) );
	}

	/**
	 * Crea nuova rotta REST.
	 */
	public function register_auth_plan_route() {
		register_rest_route(
			'ear2words/v1',
			'/auth-plan',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'auth_and_get_plan' ),
			)
		);
	}
	/**
	 * Crea un endpoint rest per la riattivazione del piano.
	 */
	public function register_reactivate_plan_route() {
		register_rest_route(
			'ear2words/v1',
			'/reactivate-plan',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'reactivate_plan' ),
			)
		);
	}

	/**
	 * Autenticazione JWT.
	 *
	 * @param array $request valori della richiesta.
	 */
	public function reactivate_plan( $request ) {
		$headers        = $request->get_headers();
		$jwt            = $headers['jwt'][0];
		$db_license_key = get_option( 'ear2words_license_key' );
		try {
			JWT::decode( $jwt, $db_license_key, array( 'HS256' ) );
		} catch ( \Exception $e ) {
			$error = array(
				'errors' => array(
					'status' => '403',
					'title'  => 'Authentication Failed',
					'source' => $e->getMessage(),
				),
			);

			$response = new WP_REST_Response( $error );

			$response->set_status( 403 );

			return $response;
		}
		$is_reactivating = (bool) get_option( 'ear2words_is_reactivating' );
		update_option( 'ear2words_is_reactivating', false );
		$message = array(
			'data' => array(
				'is_reactivating' => $is_reactivating,
			),
		);

		$response = new WP_REST_Response( $message );

		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Autenticazione JWT.
	 *
	 * @param array $request valori della richiesta.
	 */
	public function auth_and_get_plan( $request ) {
		$headers        = $request->get_headers();
		$jwt            = $headers['jwt'][0];
		$db_license_key = get_option( 'ear2words_license_key' );
		try {
			JWT::decode( $jwt, $db_license_key, array( 'HS256' ) );
		} catch ( \Exception $e ) {
			$error = array(
				'errors' => array(
					'status' => '403',
					'title'  => 'Authentication Failed',
					'source' => $e->getMessage(),
				),
			);

			$response = new WP_REST_Response( $error );

			$response->set_status( 403 );

			return $response;
		}
		return $this->return_plan();
	}

	/**
	 * Ottiene e restitutisce al backend il piano desiderato.
	 */
	public function return_plan() {
		$plan_to_upgrade = get_option( 'ear2words_wanted_plan' );

		$data = array(
			'data' => array(
				'plan_code' => $plan_to_upgrade,
			),
		);
		return $data;
	}
}
