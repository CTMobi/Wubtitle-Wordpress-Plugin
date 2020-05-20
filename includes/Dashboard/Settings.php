<?php
/**
 * This file implements Settings.
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Ear2Words\Dashboard
 */

namespace Ear2Words\Dashboard;

use Ear2Words\Loader;

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
		add_action( 'admin_notices', array( $this, 'check_notice_stripe' ) );
	}

	/**
	 * Crea un nuova voce nel menu della dashbord
	 */
	public function create_settings_menu() {
		// TODO: Cambiare $icon_url e $position (attualmente subito dopo "Impostazioni") quando verranno date indicazioni UX.
		add_menu_page( __( 'Wubtitle Settings', 'ear2words' ), __( 'Wubtitle', 'ear2words' ), 'manage_options', 'ear2words_settings', array( $this, 'render_settings_page' ), 'dashicons-format-status', 81 );
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
		Loader::get( 'cron' )->get_remote_data();
		$plans        = array(
			'plan_0'              => __( 'Free Plan', 'ear2words' ),
			'plan_HBBbNjLjVk3w4w' => __( 'Standard Plan', 'ear2words' ),
			'plan_HBBS5I9usXvwQR' => __( 'Elite Plan', 'ear2words' ),
		);
		$plan_saved   = get_option( 'ear2words_plan' );
		$current_plan = array_key_exists( $plan_saved, $plans ) ? $plans[ $plan_saved ] : '';
		$seconds_max  = get_option( 'ear2words_total_seconds' );
		$jobs_max     = get_option( 'ear2words_total_jobs' );
		$seconds      = get_option( 'ear2words_seconds_done' );
		if ( ! $seconds ) {
			$seconds = 0; }
		$jobs                      = empty( get_option( 'ear2words_jobs_done' ) ) ? 0 : get_option( 'ear2words_jobs_done' );
		$ear2words_expiration_date = get_option( 'ear2words_expiration_date' );
		$friendly_expiration_date  = date_i18n( get_option( 'date_format' ), $ear2words_expiration_date );
		$ear2words_is_canceling    = get_option( 'ear2words_is_canceling' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
			<div class="form-header">
				<img class="logo" src="<?php echo esc_url( EAR2WORDS_URL . 'src/img/logo.svg' ); ?>">
				<?php
				settings_errors();
				submit_button();
				?>
			</div>
			<div class="postbox">
				<h2 class="hndle ui-sortable-handle e2w-title" ><span><?php esc_html_e( 'Licensing', 'ear2words' ); ?></span></h2>
				<div class="inside">
					<div class="plan-state">
						<?php echo esc_html( $current_plan ); ?>
					</div>
					<div class="plan-renewal">
						<?php
						$this->render_plan_renewal( $plan_saved, $ear2words_is_canceling, $friendly_expiration_date );
						?>
					</div>
					<p style="font-weight:400">
					<?php
					esc_html_e( 'Generated video subtitles: ', 'ear2words' );
					echo esc_html( $jobs . '/' . $jobs_max );
					?>
					</p>
					<p style="font-weight:400">
					<?php
					esc_html_e( 'Video time spent: ', 'ear2words' );
					echo esc_html( date_i18n( 'H:i:s', $seconds ) . '/' . date_i18n( 'H:i:s', $seconds_max ) );
					esc_html_e( ' hours', 'ear2words' );
					?>
					</p>
						<?php
						settings_fields( 'ear2words_settings' );
						do_settings_sections( 'ear2words-settings' );
						?>
					<div class="plan-update">
						<?php
						$this->render_plan_update( $ear2words_is_canceling );
						?>
					</div>
				</div>
			</div>
		</div>
		</form>
		<?php
	}
	/**
	 * Controlla se ci sono i parametri in get e da una notice all'utente.
	 */
	public function check_notice_stripe() {
		$message = false;
		// phpcs:disable
		if ( empty( $_GET['notices-code'] ) || isset( $_GET['settings-updated'] ) ) {
			return;
		}
		switch ( $_GET['notices-code'] ) {
			case 'payment':
				$message = __( 'Payment successful', 'ear2words' );
				break;
			case 'update':
				$message = __( 'Payment information updated', 'ear2words' );
				break;
			case 'reset':
				$message = __( 'License key sent, check your email!', 'ear2words' );
				break;
			case 'delete':
				$message = __( 'Unsubscription successful', 'ear2words' );
				break;
			case 'reactivate':
				$message = __( 'Reactivation of the plan successful', 'ear2words' );
				break;
		}
		if ( ! $message ) {
			return;
		}
		?>
		 <div class="notice notice-success is-dismissible">
			 <p> <?php echo esc_html( $message ); ?></p>
		 </div>
		<?php
		// phpcs:enable
	}


	/**
	 * Render name plan in render settengs method
	 *
	 * @param string  $plan stripe plan code.
	 * @param boolean $cancelling state of user plan.
	 * @param string  $date renewal date.
	 */
	private function render_plan_renewal( $plan, $cancelling, $date ) {
		if ( 'plan_0' !== $plan && ! $cancelling ) {
			echo esc_html( __( 'Automatic renewal: ', 'ear2words' ) . $date );
		} elseif ( 'plan_0' !== $plan && $cancelling ) {
			echo esc_html( __( 'You requested the subscription cancellation. Your plan will be valid until  ', 'ear2words' ) . $date );
		}
	}

	/**
	 * Render name plan in render settengs method
	 *
	 * @param boolean $cancelling state of user plan.
	 */
	private function render_plan_update( $cancelling ) {
		if ( ! $cancelling && ! get_option( 'ear2words_free' ) ) {
			?>
			<a href="#" id="cancel-license-button" style="text-decoration: underline; color:red; margin-right:10px;" >
				<?php esc_html_e( 'Unsubscribe', 'ear2words' ); ?>
			</a>
			<a href="#" id="update-plan-button" style="text-decoration: underline" >
				<?php esc_html_e( 'Update email or payment detail', 'ear2words' ); ?>
			</a>
			<a href="#" id="modify-plan" style="text-decoration: underline; margin-left: 10px;" >
				<?php esc_html_e( 'Modify plan', 'ear2words' ); ?>
			</a>
			<?php
		} elseif ( $cancelling ) {
			?>
			<a href="#" id="reactivate-plan-button" style="text-decoration: underline;" >
				<?php esc_html_e( 'Reactivate plan', 'ear2words' ); ?>
			</a>
			<?php
		}
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
		if ( 'plan_HBBS5I9usXvwQR' !== get_option( 'ear2words_plan' ) ) {
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
		}
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
		$option = '';
		if ( ! get_option( 'ear2words_free' ) ) {
			$option = get_option( $args['name'], '' );
		}
		?>
		<input class="regular-text" type="<?php echo esc_attr( $args['type'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" value="<?php echo esc_attr( $option ); ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>">
		<?php
		if ( ! get_option( 'ear2words_free' ) && empty( get_option( 'ear2words_license_key' ) ) ) :
			?>
			<a href="#" id="reset-license" style="text-decoration: underline" >
				<?php esc_html_e( 'Reset license key', 'ear2words' ); ?>
			</a>
			<?php
		endif;
		?>
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
		<p style="display:inline; margin-left:4px;"> <?php esc_html_e( 'now!', 'ear2words' ); ?> </p>
		<?php
	}

	/**
	 * Includo gli script.
	 *
	 * @param string $hook valore presente nell'hook admin_enqueue_scripts.
	 */
	public function e2w_settings_scripts( $hook ) {
		if ( 'toplevel_page_ear2words_settings' === $hook ) {
			wp_enqueue_script( 'wp-util' );
			wp_enqueue_script( 'settings_scripts', EAR2WORDS_URL . '/src/payment/settings_script.js', array( 'wp-util' ), EAR2WORDS_VER, true );
			wp_localize_script(
				'settings_scripts',
				'settings_object',
				array(
					'ajax_url'  => admin_url( 'admin-ajax.php' ),
					'ajaxnonce' => wp_create_nonce( 'itr_ajax_nonce' ),
				)
			);
		}
	}
}
