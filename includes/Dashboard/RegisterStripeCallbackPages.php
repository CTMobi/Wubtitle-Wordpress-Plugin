<?php
/**
 * This file implements Settings.
 *
 * @author     Alessio Catania
 * @since      0.1.0
 * @package    Ear2Words\Dashboard
 */

namespace Ear2Words\Dashboard;

/**
 * This class describes Settings.
 */
class RegisterStripeCallbackPages {
	/**
	 * Init class actions.
	 */
	public function run() {
		add_action( 'admin_menu', array( $this, 'register_success_page' ) );
	}
	/**
	 * Registra le pagine per le callback di stripe.
	 */
	public function register_success_page() {
		$custom_hook = add_submenu_page( null, 'page_payment_success', 'page_payment_success', 'manage_options', 'stripe-success-payment', function(){} );
		add_action( "load-$custom_hook", array( $this, 'render_success_payment_page' ) );
		$custom_hook = add_submenu_page( null, 'page_update_success', 'page_update_success', 'manage_options', 'stripe-success-update', function(){} );
		add_action( "load-$custom_hook", array( $this, 'render_success_update_page' ) );
		$custom_hook = add_submenu_page( null, 'page_payment_cancel', 'page_payment_cancel', 'manage_options', 'stripe-cancel-payment', function(){} );
		add_action( "load-$custom_hook", array( $this, 'render_cancel_payment_page' ) );
		$custom_hook = add_submenu_page( null, 'page_update_cancel', 'page_update_cancel', 'manage_options', 'stripe-cancel-update', function(){} );
		add_action( "load-$custom_hook", array( $this, 'render_cancel_update_page' ) );
	}
	/**
	 * Render della pagina di success del pagamento.
	 */
	public function render_success_payment_page() {
		include 'Templates/success_payment_template.php';
		wp_die();
	}
	/**
	 * Render della pagina di success dell'aggiornamento dei dati di pagamento.
	 */
	public function render_success_update_page() {
		include 'Templates/success_update_template.php';
		wp_die();
	}
	/**
	 * Render della pagina di cancel del pagamento.
	 */
	public function render_cancel_payment_page() {
		include 'Templates/cancel_payment_template.php';
		wp_die();
	}
	/**
	 * Render della pagina di cancel dell'aggiornamento dei dati di pagamento.
	 */
	public function render_cancel_update_page() {
		include 'Templates/cancel_update_template.php';
		wp_die();
	}

}
