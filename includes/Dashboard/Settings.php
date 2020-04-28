<?php
/**
 * This file implements Settings.
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Ear2Words\Dashboard
 */

namespace Ear2Words\Dashboard;

/**
 * This class describes Settings.
 */
class Settings {
	/**
	 * Init class actions
	 */
	public function run() {
		add_action( 'admin_menu', array( $this, 'create_settings_menu' ) );
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'admin_init', array( $this, 'init_settings_field' ) );
		add_action( 'update_option_ear2words_license_key', array( $this, 'check_license' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'e2w_settings_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'ear2words_settings_style' ) );
	}

	/**
	 * Crea un nuova voce nel menu della dashbord
	 */
	public function create_settings_menu() {
		// TODO: Cambiare $icon_url e $position (attualmente subito dopo "Impostazioni") quando verranno date indicazioni UX.
		add_menu_page( __( 'Ear2words Settings', 'ear2words' ), __( 'Ear2words', 'ear2words' ), 'manage_options', 'ear2words_settings', array( $this, 'render_settings_page' ), 'dashicons-format-status', 81 );
	}
	/**
	 *  Faccio l'enqueue dello style per i settings.
	 */
	public function ear2words_settings_style() {
		wp_enqueue_style( 'ear2words_settings_style', EAR2WORDS_URL . 'src/css/settingsStyle.css', null, true );
	}
	/**
	 * Crea la pagina dei settings
	 */
	public function render_settings_page() {
			$this->stripe_callback_url();
		?>
		<div class="wrap">
			<div class="logo-placeholder">
				LOGO PLACEHOLDER
			</div>
			<form action="options.php" method="post">
			<?php
			settings_errors();
			submit_button();
			?>
			<div class="postbox">
				<h2 class="hndle ui-sortable-handle e2w-title" ><span><?php esc_html_e( 'Licensing', 'ear2words' ); ?></span></h2>
				<div class="inside">
					<div class="plan-state">
						<!-- TODO:  Rendere dinamico -->
						<?php esc_html_e( 'Free Plan', 'ear2words' ); ?>
					</div>
						<?php
						settings_fields( 'ear2words_settings' );
						do_settings_sections( 'ear2words-settings' );
						?>
					<?php
					if ( ! empty( get_option( 'ear2words_license_key' ) ) ) {
						?>
						<a id="update-plan-button" style="text-decoration: underline" >
							<?php esc_html_e( 'Update email or payment detail', 'ear2words' ); ?>
						</a>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		</form>
		<?php
		if ( ! get_option( 'ear2words_free' ) ) {
			echo '<a id="cancel-license-button" style="text-decoration: underline; color:red" >';
			esc_html_e( 'Unsubscribe', 'ear2words' );
			echo '</a>';
			echo '<a id="update-plan-button" style="text-decoration: underline; margin-left:16px" >';
			esc_html_e( 'Update email or payment detail', 'ear2words' );
			echo '</a>';
		}
	}
	/**
	 * Gestisce le callback di stripe.
	 */
	private function stripe_callback_url() {
		$notices = get_option( 'custom_notices' );
		if ( ! empty( $notices ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html( $notices ); ?></p>
			</div>
			<?php
			delete_option( 'custom_notices' );
		}
		// phpcs:disable
		if ( isset( $_GET['payment'] ) && 'true' === $_GET['payment'] ) {
			update_option( 'custom_notices', 'pagamento effettuato' );
		}

		if ( isset( $_GET['update'] ) && 'true' === $_GET['update'] ) {
			update_option( 'custom_notices', 'aggiornamento effettuato' );
		}
		// phpcs:enable
	}
	/**
	 * Aggiunge una nuova impostazione
	 */
	public function init_settings() {
		register_setting(
			'ear2words_settings',
			'ear2words_license_key',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}

	/**
	 * Check license
	 */
	public function check_license() {
		$submitted_license = get_option( 'ear2words_license_key' );

		$validation = $this->remote_request( $submitted_license );

		if ( $validation['error'] && ! $validation['verified'] ) {
			$error_messages = array(
				'EXPIRED' => __( 'Unable to update. Expired product license.', 'ear2words' ),
				'INVALID' => __( 'Unable to update. Invalid product license.', 'ear2words' ),
				'4xx'     => __( 'An error occurred while updating licence. Please try again in a few minutes.', 'ear2words' ),
				'5xx'     => __( 'Could not contact the server.', 'ear2words' ),
				'xxx'     => __( 'An error occurred.', 'ear2words' ),
			);

			add_settings_error(
				'ear2words_license_key',
				esc_attr( 'invalid_license' ),
				$error_messages[ $validation['error'] ],
				'error'
			);
			remove_action( 'update_option_ear2words_license_key', array( $this, 'check_license' ) );
			update_option( 'ear2words_license_key', null );
		} elseif ( $validation['verified'] ) {
			add_settings_error(
				'ear2words_license_key',
				esc_attr( 'invalid_license' ),
				__( 'Valid product license. Subscription plan updated.', 'ear2words' ),
				'success'
			);
		}
	}

	/**
	 * Chiamata ad endpoint remoto per check license key.
	 *
	 * @param string $license_key license key dell'input.
	 */
	private function remote_request( $license_key ) {
		$headers = array(
			'Content-Type' => 'application/json; charset=utf-8',
		);

		$body = array(
			'data' => array(
				'licenseKey' => $license_key,
			),
		);

		$response = wp_remote_post(
			ENDPOINT . 'license/check',
			array(
				'method'  => 'POST',
				'headers' => $headers,
				'body'    => wp_json_encode( $body ),
			)
		);

		$retrieved = json_decode( wp_remote_retrieve_body( $response ), true );
		$status    = wp_remote_retrieve_response_code( $response );

		$validation = array();

		$validation['verified'] = $retrieved['data']['verified'];

		// xxx indica un errore da gestire con un messaggio generico, 4xx e 5xx tutti gli errori 400 o 500.
		$validation['error'] = 'xxx';
		if ( 200 === $status && ! $validation['verified'] ) {
			$validation['error'] = $retrieved['data']['errorType'];
		} elseif ( 500 <= $status && 600 > $status ) {
			$validation['error'] = '5xx';
		} elseif ( 400 <= $status && 500 > $status ) {
			$validation['error'] = '4xx';
		}

		return $validation;
	}

	/**
	 * Aggiunge un nuovo campo all'impostazione precedentemente creata
	 */
	public function init_settings_field() {
		add_settings_section( 'ear2words-main-settings', null, null, 'ear2words-settings' );
		add_settings_field(
			'buy-license-button',
			__( 'Unlock more features!', 'ear2words' ),
			array( $this, 'upgrade_button' ),
			'ear2words-settings',
			'ear2words-main-settings',
			array(
				'name'  => __( 'Upgrade', 'ear2words' ),
				'class' => 'upgrade-button',
			)
		);
		add_settings_field(
			'ear2words-license-key',
			__( 'License Number', 'ear2words' ),
			array( $this, 'input_field' ),
			'ear2words-settings',
			'ear2words-main-settings',
			array(
				'type'        => 'text',
				'name'        => 'ear2words_license_key',
				'placeholder' => __( 'License key', 'ear2words' ),
				'class'       => 'input-license-key',
				'description' => __( 'Please enter the license key you received after successful checkout', 'ear2words' ),
			)
		);
	}

	/**
	 * Crea un componente input da richiamare nel form
	 *
	 * @param array $args Parametri dell'input.
	 */
	public function input_field( $args ) {
		$option = get_option( $args['name'], '' );
		?>
		<input class="regular-text" type="<?php echo esc_attr( $args['type'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( $option ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>">
		<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php
	}
	/**
	 * Crea il bottone per fare l'upgrade del bottone.
	 *
	 * @param array $args Parametri dell'input.
	 */
	public function upgrade_button( $args ) {
		?>
		<button id="buy-license-button" class="button-primary" >
			<?php echo esc_html( $args['name'] ); ?>
		</button>
		<p style="display:inline; margin-left:4px;"> now! </p>
		<?php
	}

	/**
	 * Includo gli script.
	 *
	 * @param string $hook valore presente nell'hook admin_enqueue_scripts.
	 */
	public function e2w_settings_scripts( $hook ) {
		$update  = 'none';
		$payment = 'none';
		// phpcs:disable
		if ( isset( $_GET['update'] ) ) {
			$update = sanitize_text_field( wp_unslash( $_GET['update'] ) );
		}
		if ( isset( $_GET['payment'] ) ) {
			$payment = sanitize_text_field( wp_unslash( $_GET['payment'] ) );
		}
		// phpcs:enable
		if ( 'toplevel_page_ear2words_settings' === $hook ) {
			wp_enqueue_script( 'wp-util' );
			wp_enqueue_script( 'settings_scripts', EAR2WORDS_URL . '/src/payment/settings_script.js', array( 'wp-util' ), EAR2WORDS_VER, true );
			wp_localize_script(
				'settings_scripts',
				'object',
				array(
					'update'  => $update,
					'payment' => $payment,
				)
			);
		}
	}
}
