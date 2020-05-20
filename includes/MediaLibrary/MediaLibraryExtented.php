<?php
/**
 * This file extends media library.
 *
 * @author     Alessio Catania
 * @since      0.1.0
 * @package    Ear2Words\Gutenberg
 */

namespace Ear2Words\MediaLibrary;

use Ear2Words\Loader;
use Ear2Words\Helpers;

/**
 * Classe che estende la media library
 */
class MediaLibraryExtented {
	/**
	 * Setup delle action.
	 */
	public function run() {
		$helpers = new Helpers();
		if ( ! $helpers->is_gutenberg_active() ) {
			add_action( 'attachment_fields_to_edit', array( $this, 'add_generate_subtitle_form' ), 99, 2 );
		}
		add_action( 'attachment_fields_to_edit', array( $this, 'add_generate_subtitle_form_into_media_library' ), 99, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'ear2words_medialibrary_style' ) );
		add_filter( 'attachment_fields_to_save', array( $this, 'video_attachment_fields_to_save' ), null, 2 );
		add_filter( 'wp_video_shortcode_override', array( $this, 'ear2words_video_shortcode' ), 10, 4 );
	}

	/**
	 *  Faccio l'enqueue dello style per i settings.
	 */
	public function ear2words_medialibrary_style() {
		wp_enqueue_style( 'ear2words_medialibrary_style', EAR2WORDS_URL . '/src/css/mediaStyle.css', null, true );
	}


	/**
	 *  Aggiunge il form di ear2words nella scheda "add media".
	 *
	 * @param array $form_fields campi finestra modale.
	 * @param array $post attachment.
	 */
	public function add_generate_subtitle_form( $form_fields, $post ) {
		global $pagenow;
		$all_status    = array(
			'pending' => __( 'Generating', 'ear2words' ),
			'draft'   => __( 'Draft', 'ear2words' ),
			'enabled' => __( 'Published', 'ear2words' ),
			'error'   => __( 'Error', 'ear2words' ),
			''        => __( 'None', 'ear2words' ),
		);
		$allowed_pages = array(
			'admin-ajax.php',
			'async-upload.php',
		);
		if ( ! wp_attachment_is( 'video', $post ) || ! in_array( $pagenow, $allowed_pages, true ) ) {
			return $form_fields;
		}
		if ( get_option( 'ear2words_free' ) && 'video/mp4' !== $post->post_mime_type ) {
			$form_fields['e2w_status'] = array(
				'label' => 'Wubtitle',
				'input' => 'html',
				'html'  => '<label for="attachments-' . $post->ID . '-e2w_status">' . __( 'Unsupported video format for free plan', 'ear2words' ) . '</label>',
				'value' => $post->ID,
			);
			return $form_fields;
		}
		// Aggiunge lo stato del sottotitolo.
		$status                    = $post->ear2words_status;
		$form_fields['e2w_status'] = array(
			'label' => __( 'Subtitle', 'ear2words' ),
			'input' => 'html',
			'html'  => '<label for="attachments-' . $post->ID . '-e2w_status">' . $all_status[ $status ] . '</label>',
			'value' => $post->ID,
		);

		// Aggiunge la select della lingua e il bottone per generare i sottotitoli se il video non è ancora stato processato da e2w.
		if ( '' === $status || 'error' === $status ) {
			$form_fields['e2w_form'] = array(
				'label' => __( 'Language', 'ear2words' ),
				'input' => 'html',
				'html'  => '',
				'value' => $post->ID,
			);
			$lang                    = explode( '_', get_locale(), 2 )[0];
			ob_start();
			?>
			<select name="attachments[<?php echo esc_html( $post->ID ); ?>][select-lang]" id="Profile Image Select">
				<?php $this->language_options( $lang ); ?>
			</select>
			<label onclick="this.setAttribute('disabled','true')" class="button-primary" style="margin-top:16px;" for="attachments-<?php echo esc_html( $post->ID ); ?>-e2w_form">
				<input type="checkbox" style="display:none" id="attachments-<?php echo esc_html( $post->ID ); ?>-e2w_form" name="attachments[<?php echo esc_html( $post->ID ); ?>][e2w_form]" value="<?php echo esc_html( $post->ID ); ?>" />
				<?php esc_html_e( 'GENERATE SUBTITLES', 'ear2words' ); ?>
			</label>
			<?php
			$form_fields['e2w_form']['html'] .= ob_get_clean();
			return $form_fields;
		}
		// Sostituisce lo stato con una select per pubblicare o disabilitare i sottotitoli se lo stato è uno tra enabled e draft.
		if ( 'draft' === $status || 'enabled' === $status ) {
			$form_fields['e2w_status'] = array(
				'label' => __( 'Subtitle', 'ear2words' ),
				'input' => 'html',
				'html'  => '',
				'value' => $post->ID,
			);
			$lang                      = explode( '_', get_locale(), 2 )[0];
			ob_start();
			?>
			<select name="attachments[<?php echo esc_html( $post->ID ); ?>][select-status]" id="Profile Image Select">
				<option <?php echo selected( $status, 'enabled', false ); ?> value="enabled"> <?php esc_html_e( 'Published', 'ear2words' ); ?></option>
				<option <?php echo selected( $status, 'draft', false ); ?> value="draft"> <?php esc_html_e( 'Draft', 'ear2words' ); ?></option>
			</select>
			<?php
			$form_fields['e2w_status']['html'] .= ob_get_clean();
		}
		// Aggiunge una label per la lingua del video.
		$form_fields['e2w_lang'] = array(
			'label' => __( 'Language', 'ear2words' ),
			'input' => 'html',
			'html'  => '<label for="attachments-' . $post->ID . '-e2w_lang">' . $this->get_video_language( $post->ID ) . '</label>',
			'value' => $post->ID,
		);
		return $form_fields;
	}

	/**
	 *  Check sulla lingua disponibile nel piano free.
	 *
	 * @param string $lang_code language code.
	 */
	private function is_pro_only( $lang_code ) {
		$free_lang = array( 'it', 'en' );
		return get_option( 'ear2words_free', true ) && ! in_array( $lang_code, $free_lang, true );
	}

	/**
	 *  Ritorna le options delle lingue selezionabili nelle select "genera sottotitoli".
	 *
	 * @param string $lang language code.
	 */
	private function language_options( $lang ) {
		$languages = array(
			'it' => __( 'Italian', 'ear2words' ),
			'en' => __( 'English', 'ear2words' ),
			'es' => __( 'Spanish', 'ear2words' ),
			'de' => __( 'German', 'ear2words' ),
			'zh' => __( 'Chinese', 'ear2words' ),
			'fr' => __( 'French', 'ear2words' ),
		);
		foreach ( $languages as $key => $language ) {
			echo sprintf(
				'<option %s value="%s" %s>%s</option>',
				selected( $lang, $key, false ),
				esc_html( $key ),
				esc_html( $this->is_pro_only( $key ) ? 'disabled' : '' ),
				esc_html( $this->is_pro_only( $key ) ? $language . ' (Pro Only)' : $language )
			);
		}
	}

	/**
	 *  Aggiunge il form di ear2words nella scheda "add media".
	 *
	 * @param array $form_fields campi finestra modale.
	 * @param array $post attachment.
	 */
	public function add_generate_subtitle_form_into_media_library( $form_fields, $post ) {
		global $pagenow;
		$all_status = array(
			'pending' => __( 'Generating', 'ear2words' ),
			'draft'   => __( 'Draft', 'ear2words' ),
			'enabled' => __( 'Published', 'ear2words' ),
			'none'    => __( 'None', 'ear2words' ),
			'error'   => __( 'Error', 'ear2words' ),
		);
		if ( ! wp_attachment_is( 'video', $post ) || 'post.php' !== $pagenow ) {
			return $form_fields;
		}
		if ( get_option( 'ear2words_free' ) && 'video/mp4' !== $post->post_mime_type ) {
			$form_fields['e2w_status'] = array(
				'label' => 'Wubtitle',
				'input' => 'html',
				'html'  => '<label for="attachments-' . $post->ID . '-e2w_status">' . __( 'Unsupported video format for free plan', 'ear2words' ) . '</label>',
				'value' => $post->ID,
			);
			return $form_fields;
		}
		$status = empty( $post->ear2words_status ) ? 'none' : $post->ear2words_status;

		// Aggiunge una select per pubblicare o disabilitare i sottotitoli se lo stato è uno tra enabled e draft.
		if ( 'draft' === $status || 'enabled' === $status ) {
			$form_fields = $this->create_toolbar_and_select( $status, $post->ID );
			return $form_fields;
		}
		// Aggiunge l'header.
		$form_fields['e2w_header']['tr'] = '<strong> ' . __( 'Subtitles', 'ear2words' ) . ' </strong>';

		// Aggiunge lo stato del sottotitolo.
		$form_fields['e2w_status'] = array(
			'label' => __( 'Status', 'ear2words' ),
			'input' => 'html',
			'html'  => '<label for="attachments-' . $post->ID . '-e2w_status">' . $all_status[ $status ] . '</label>',
			'value' => $post->ID,
		);
		$status_none               = array(
			'none',
			'error',
		);
		// Aggiunge la select della lingua e il bottone per generare i sottotitoli se il video non è ancora stato processato da e2w.
		if ( in_array( $status, $status_none, true ) ) {
			$form_fields['e2w_form'] = $this->create_select_and_button( $post->ID );
			return $form_fields;
		}

		// Aggiunge una label per la lingua del video.
		$form_fields['e2w_lang'] = array(
			'label' => __( 'Language', 'ear2words' ),
			'input' => 'html',
			'html'  => '<label for="attachments-' . $post->ID . '-e2w_lang">' . $this->get_video_language( $post->ID ) . '</label>',
			'value' => $post->ID,
		);
		// Aggiunge paragrafo.
		$form_fields['e2w_lang']['helps'] = __( 'Wait while subtitles are created. Subtitles will be available as soon as possible', 'ear2words' );
		return $form_fields;
	}

	/**
	 * Crea la toolbar e inserisce una label per la lingua del sottotitolo e una select per scegliere se pubblicare o meno i stottotitoli.
	 *
	 * @param string $status stato dei stottotitoli.
	 * @param int    $id_video id del video.
	 */
	private function create_toolbar_and_select( $status, $id_video ) {
		$form_fields = array();
		ob_start();
		?>
		<div class="quicktags-toolbar">
			<label for="attachments-' . $post->ID . '-e2w_lang">
				<strong>Subtitles: </strong><?php echo esc_html( $this->get_video_language( $id_video ) ); ?>
			</label>
			<select class="e2w-select-status" name="attachments[<?php echo esc_html( $id_video ); ?>][select-status]" id="Profile Image Select">
				<option <?php echo selected( $status, 'enabled', false ); ?> value="enabled"> <?php esc_html_e( 'Published', 'ear2words' ); ?></option>
				<option <?php echo selected( $status, 'draft', false ); ?> value="draft"> <?php esc_html_e( 'Draft', 'ear2words' ); ?></option>
			</select>
		</div>
		<!-- <textarea style="width:100%" class="wp-editor-area" cols="40" rows="5"></textarea> -->
		<?php
			$form_fields['e2w_status']['tr'] = ob_get_clean();
			return $form_fields;
	}
	/**
	 * Crea la select della lingua e il bottone per generare i sottotitoli.
	 *
	 * @param int $id_video id del video.
	 */
	private function create_select_and_button( $id_video ) {
		$form_fields = array(
			'label' => __( 'Language', 'ear2words' ),
			'input' => 'html',
			'html'  => '',
			'value' => $id_video,
		);
		$lang        = explode( '_', get_locale(), 2 )[0];
		ob_start();
		?>
			<select style="width:100%" name="attachments[<?php echo esc_html( $id_video ); ?>][select-lang]" id="Profile Image Select">
				<?php $this->language_options( $lang ); ?>
			</select>
			<button type="submit" class="button-primary" style="margin-top:16px;" id="attachments-<?php echo esc_html( $id_video ); ?>-e2w_form" name="attachments[<?php echo esc_html( $id_video ); ?>][e2w_form]" value="invio">
			<?php esc_html_e( 'GENERATE SUBTITLES', 'ear2words' ); ?>
			</button>
			<?php
			$form_fields['html'] .= ob_get_clean();
			return $form_fields;
	}

	/**
	 * Esegue la chiamata all'endpoint per generare i sottotitoli, se la chiamata va a buon fine salva uuid e stato.
	 *
	 * @param array $post contiene i dati dell'attachment.
	 * @param array $attachment contiene i dati degli input custom.
	 */
	public function video_attachment_fields_to_save( $post, $attachment ) {
		if ( isset( $attachment['select-status'] ) ) {
			update_post_meta( $post['ID'], 'ear2words_status', $attachment['select-status'] );
		}
		if ( isset( $attachment['e2w_form'] ) && '' !== $attachment['e2w_form'] ) {
			$data['lang']           = $attachment['select-lang'];
			$data['id_attachment']  = $post['ID'];
			$data['src_attachment'] = wp_get_attachment_url( $post['ID'] );
			$data                   = Loader::get( 'request' )->sanitize_input( $data );
			if ( ! $data ) {
				// TODO restituire il messaggio di errore.
				return;
			}
			$body = Loader::get( 'request' )->set_body_request( $data );
			if ( ! $body ) {
				// TODO restituire il messaggio di errore.
				return;
			}
			$license_key = get_option( 'ear2words_license_key' );
			if ( empty( $license_key ) ) {
				// TODO restituire il messaggio di errore.
				return;
			}
			$response = Loader::get( 'request' )->send_job_to_backend( $body, $license_key );
			if ( 201 === wp_remote_retrieve_response_code( $response ) ) {
				$response_body = json_decode( wp_remote_retrieve_body( $response ) );
				Loader::get( 'request' )->update_uuid_status_and_lang( $post['ID'], $data['lang'], $response_body->data->jobId );
			}
		}
		return $post;
	}
	/**
	 * Sovrascrive lo shortcode video aggiungendo i sottotitoli come file_get_content
	 *
	 * @param string $html html generato dallo shortcode.
	 * @param array  $attr attributi dello shortcode.
	 */
	public function ear2words_video_shortcode( $html, $attr ) {
		remove_filter( 'wp_video_shortcode_override', array( $this, 'ear2words_video_shortcode' ), 10 );
		$source   = array_key_exists( 'mp4', $attr ) ? $attr['mp4'] : $attr['src'];
		$id_video = attachment_url_to_postid( $source );
		$subtitle = get_post_meta( $id_video, 'ear2words_subtitle', true );
		$lang     = $this->get_video_language( $id_video );
		$status   = get_post_meta( $id_video, 'ear2words_status', true );
		if ( '' !== $subtitle && 'enabled' === $status ) {
			$content = '<track srclang="it" label="' . $lang . '" kind="subtitles" src="' . wp_get_attachment_url( $subtitle ) . '" default>';
			$html    = wp_video_shortcode( $attr, $content );
		}
		add_filter( 'wp_video_shortcode_override', array( $this, 'ear2words_video_shortcode' ), 10, 4 );
		return $html;
	}
	/**
	 * Ritorna la lingua prendendola dal post meta del video. Inoltre la traduce.
	 *
	 * @param int $id_video id del video.
	 */
	public function get_video_language( $id_video ) {
		$lang     = get_post_meta( $id_video, 'ear2words_lang_video', true );
		$all_lang = array(
			'it' => __( 'Italian', 'ear2words' ),
			'en' => __( 'English', 'ear2words' ),
			'es' => __( 'Spanish', 'ear2words' ),
			'de' => __( 'German ', 'ear2words' ),
			'zh' => __( 'Chinese', 'ear2words' ),
			'fr' => __( 'French', 'ear2words' ),
		);
		return array_key_exists( $lang, $all_lang ) ? $all_lang[ $lang ] : 'Undefined';
	}
}
