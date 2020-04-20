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
	}

	/**
	 * Include il template che viene caricato nella finestra popup per l'acquisto della licenza al click del bottone "acquista".
	 */
	public function load_payment_template() {
		include 'Templates/payment_template.php';
		wp_die();
	}

}
