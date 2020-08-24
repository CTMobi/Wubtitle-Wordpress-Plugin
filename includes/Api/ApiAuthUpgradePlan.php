<?php
/**
 * In this file is created a new endpoint for plan change authorization.
 *
 * @author     Nicola Palermo
 * @since      1.0.0
 * @package    Wubtitle\Api
 */

namespace Wubtitle\Api;

use WP_REST_Response;
use \Firebase\JWT\JWT;

/**
 * This class manages authorization to change plans.
 */
class ApiAuthUpgradePlan {
	/**
	 * Init class action.
	 *
	 * @return void
	 */
	public function run() {
		add_action( 'rest_api_init', array( $this, 'register_auth_plan_route' ) );
		add_action( 'rest_api_init', array( $this, 'register_reactivate_plan_route' ) );
	}

	/**
	 * Creates new REST route
	 *
	 * @return void
	 */
	public function register_auth_plan_route() {
		register_rest_route(
			'wubtitle/v1',
			'/auth-plan',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'auth_and_get_plan' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}
	/**
	 * Creates a rest endpoint for the reactivation plan.
	 *
	 * @return void
	 */
	public function register_reactivate_plan_route() {
		register_rest_route(
			'wubtitle/v1',
			'/reactivate-plan',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'reactivate_plan' ),
				'permission_callback' => function() {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * JWT authentication.
	 *
	 * @param \WP_REST_Request $request request values.
	 * @return WP_REST_Response|array<string,array<string,bool>>
	 */
	public function reactivate_plan( $request ) {
		$headers        = $request->get_headers();
		$jwt            = $headers['jwt'][0];
		$db_license_key = get_option( 'wubtitle_license_key' );
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
		$is_reactivating = (bool) get_option( 'wubtitle_is_reactivating' );
		update_option( 'wubtitle_is_reactivating', false );
		$message = array(
			'data' => array(
				'is_reactivating' => $is_reactivating,
			),
		);
		return $message;
	}

	/**
	 * JWT Authentication.
	 *
	 * @param \WP_REST_Request $request values.
	 * @return WP_REST_Response|array<array<string>>
	 */
	public function auth_and_get_plan( $request ) {
		$headers        = $request->get_headers();
		$jwt            = $headers['jwt'][0];
		$db_license_key = get_option( 'wubtitle_license_key' );
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
	 * Gets and returns the chosen plan to backend
	 *
	 * @return array<array<string>>
	 */
	public function return_plan() {
		$plan_rank       = get_option( 'wubtitle_wanted_plan_rank' );
		$all_plans       = get_option( 'wubtitle_all_plans' );
		$plan_to_upgrade = $all_plans[ $plan_rank ]['stripe_code'];

		$data = array(
			'data' => array(
				'plan_code' => $plan_to_upgrade,
			),
		);
		return $data;
	}
}
