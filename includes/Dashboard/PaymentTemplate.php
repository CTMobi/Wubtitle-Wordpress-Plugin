<?php
/**
 * This file describes handle Templates.
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Ear2Words\Dashboard
 */

namespace Ear2Words\Dashboard;

/**
 * This class handle Payment Templates .
 */
class PaymentTemplate {
	/**
	 * Init class actions
	 */
	public function run() {
		add_action( 'wp_ajax_payment_template', array( $this, 'load_payment_template' ) );
		add_action( 'wp_ajax_update_template', array( $this, 'load_update_template' ) );
		add_action( 'wp_ajax_change_plan_template', array( $this, 'change_plan_template' ) );
	}


	/**
	 * Include il template che viene caricato nella finestra popup per l'acquisto della licenza al click del bottone "acquista".
	 */
	public function change_plan_template() {
		$map_plans     = array(
			'plan_0'              => 0,
			'plan_HBBbNjLjVk3w4w' => 1,
			'plan_HBBS5I9usXvwQR' => 2,
		);
		$plan          = get_option( 'ear2words_plan' );
		$current_plan  = $map_plans[ $plan ];
		$plan          = get_option( 'ear2words_wanted_plan' );
		$wanted_plan   = $map_plans[ $plan ];
		$includes_file = 'Templates/downgrade_plan_template.php';
		if ( $wanted_plan > $current_plan ) {
			$includes_file = 'Templates/upgrade_plan_template.php';
		}
		if ( current_user_can( 'manage_options' ) ) {
			ob_start();
			include $includes_file;
			$html = ob_get_clean();
			wp_send_json_success( $html );
			wp_die();
		}
		$html = 'Error';
		wp_send_json_error( $html );
		wp_die();
	}
	/**
	 * Include il template che viene caricato nella finestra popup per l'acquisto della licenza al click del bottone "acquista".
	 */
	public function load_payment_template() {
		if ( current_user_can( 'manage_options' ) ) {
			ob_start();
			include 'Templates/payment_template.php';
			$html = ob_get_clean();
			wp_send_json_success( $html );
			wp_die();
		}
		$html = 'Error';
		wp_send_json_error( $html );
		wp_die();
	}
	/**
	 * Include il template che viene caricato nella finestra popup per l'aggiornamento dei dati di pagamento.
	 */
	public function load_update_template() {
		if ( current_user_can( 'manage_options' ) ) {
			ob_start();
			include 'Templates/update_template.php';
			$html = ob_get_clean();
			wp_send_json_success( $html );
			wp_die();
		}
		$html = 'Error';
		wp_send_json_error( $html );
		wp_die();
	}

}
