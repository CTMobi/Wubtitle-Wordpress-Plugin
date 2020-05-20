<?php
/**
 * Effettua il bootstrap del plugin
 *
 * @package Ear2Words
 */

namespace Ear2Words;

/**
 * This class describes the plugin loader.
 */
class Loader {
	/**
	 * L'array che contiene gli oggetti istanziati dal Loader.
	 *
	 * @var array
	 */
	private static $services = array();
	/**
	 * Istanzia le classi Principali
	 */
	public static function init() {
		load_plugin_textdomain( 'ear2words', false, EAR2WORDS_NAME . '/languages' );
		$classes = array(
			'gutenber'                => Gutenberg\VideoBlock::class,
			'settings'                => Dashboard\Settings::class,
			'request'                 => Api\ApiRequest::class,
			'license_validation'      => Api\ApiLicenseValidation::class,
			'custom_media_library'    => MediaLibrary\ListingSubtitles::class,
			'subtitle'                => Core\Subtitle::class,
			'store_subtitle'          => Api\ApiStoreSubtitle::class,
			'extented_media_library'  => MediaLibrary\MediaLibraryExtented::class,
			'send_pricing_plan'       => Api\ApiPricingPlan::class,
			'payment_template'        => Dashboard\PaymentTemplate::class,
			'activation'              => Core\Activation::class,
			'cancel_template'         => Dashboard\CancelPage::class,
			'cancel_subscription'     => Api\ApiCancelSubscription::class,
			'cron'                    => Core\Cron::class,
			'register_callback_pages' => Dashboard\RegisterStripeCallbackPages::class,
			'api_auth_plan'           => Api\ApiAuthUpgradePlan::class,
			'api_get_transcript'      => Api\ApiGetTranscript::class,
			'helpers'                 => Helpers::class,
			'extends_transcription'   => MediaLibrary\TrascriptionsExtends::class,
			'transcript_cpt'          => Core\CustomPostTypes\Transcript::class,
			'shortcode'               => Core\Shortcode::class,
			'youtube_source'          => Core\Sources\YouTube::class,
			'trascription_block'      => Gutenberg\TranscriptionBlock::class,
		);

		foreach ( $classes as $key => $class ) {
			$instance = new $class();
			self::bind( $key, $instance );
			if ( method_exists( $instance, 'run' ) ) {
				$instance->run();
			}
		}
	}
	/**
	 * Aggiunge l'istanza della classe al container.
	 *
	 * @param string $key nome instanza.
	 * @param class  $item instanza della classe.
	 */
	public static function bind( $key, $item ) {
		( self::$services )[ $key ] = $item;
	}
	/**
	 * Cerca nel container e se esiste restituisce l'istanza di una classe.
	 *
	 * @param string $key nome instanza.
	 */
	public static function get( $key ) {
		if ( ! isset( self::$services[ $key ] ) ) {
			return false;
		}

		return ( self::$services )[ $key ];
	}
}
