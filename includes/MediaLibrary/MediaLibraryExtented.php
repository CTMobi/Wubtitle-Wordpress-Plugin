<?php
/**
 * This file extends media library.
 *
 * @author     Alessio Catania
 * @since      0.1.0
 * @package    Wubtitle\Gutenberg
 */

namespace Wubtitle\MediaLibrary;

use Wubtitle\Loader;
use Wubtitle\Helpers;

/**
 * This class che extends the media library
 */
class MediaLibraryExtented {

	/**
	 * Actions setup.
	 */
	public function run() {
		$helpers = new Helpers();
		if ( ! $helpers->is_gutenberg_active() ) {
			add_action( 'attachment_fields_to_edit', array( $this, 'add_generate_subtitle_form' ), 99, 2 );
		}
		add_action( 'attachment_fields_to_edit', array( $this, 'add_generate_subtitle_form_into_media_library' ), 99, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'wubtitle_medialibrary_style' ) );
		add_filter( 'attachment_fields_to_save', array( $this, 'video_attachment_fields_to_save' ), null, 2 );
		add_filter( 'wp_video_shortcode_override', array( $this, 'wubtitle_video_shortcode' ), 10, 4 );
	}

	/**
	 *  Enqueue settings style.
	 */
	public function wubtitle_medialibrary_style() {
		wp_enqueue_style( 'wubtitle_medialibrary_style', WUBTITLE_URL . '/src/css/mediaStyle.css', null, true );
	}


	/**
	 *  Aggiunge il form di wubtitle nella scheda "add media".
	 *  Add wubtitle form into "add media" tab.
	 *
	 * @param array $form_fields modal window fields.
	 * @param array $post attachment.
	 */
	public function add_generate_subtitle_form( $form_fields, $post ) {
		global $pagenow;
		$all_status    = array(
			'pending' => __( 'Generating', 'wubtitle' ),
			'draft'   => __( 'Draft', 'wubtitle' ),
			'enabled' => __( 'Published', 'wubtitle' ),
			'error'   => __( 'Error', 'wubtitle' ),
			''        => __( 'None', 'wubtitle' ),
		);
		$allowed_pages = array(
			'admin-ajax.php',
			'async-upload.php',
		);
		if ( ! wp_attachment_is( 'video', $post ) || ! in_array( $pagenow, $allowed_pages, true ) ) {
			return $form_fields;
		}
		if ( get_option( 'wubtitle_free' ) && 'video/mp4' !== $post->post_mime_type ) {
			$form_fields['e2w_status'] = array(
				'label' => 'Wubtitle',
				'input' => 'html',
				'html'  => '<label for="attachments-' . $post->ID . '-e2w_status">' . __( 'Unsupported video format for free plan', 'wubtitle' ) . '</label>',
				'value' => $post->ID,
			);
			return $form_fields;
		}

		// Add subtitle state.
		$status                    = $post->wubtitle_status;
		$form_fields['e2w_status'] = array(
			'label' => __( 'Subtitle', 'wubtitle' ),
			'input' => 'html',
			'html'  => '<label for="attachments-' . $post->ID . '-e2w_status">' . $all_status[ $status ] . '</label>',
			'value' => $post->ID,
		);

		// Add language select and subtitle button.
		if ( '' === $status || 'error' === $status ) {
			$form_fields['e2w_form'] = array(
				'label' => __( 'Language', 'wubtitle' ),
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
				<?php esc_html_e( 'GENERATE SUBTITLES', 'wubtitle' ); ?>
			</label>
			<?php
			$form_fields['e2w_form']['html'] .= ob_get_clean();
			return $form_fields;
		}

		// Replace state with a select to enable/disable subtitles.
		if ( 'draft' === $status || 'enabled' === $status ) {
			$form_fields['e2w_status'] = array(
				'label' => __( 'Subtitle', 'wubtitle' ),
				'input' => 'html',
				'html'  => '',
				'value' => $post->ID,
			);
			$lang                      = explode( '_', get_locale(), 2 )[0];
			ob_start();
			?>
			<select name="attachments[<?php echo esc_html( $post->ID ); ?>][select-status]" id="Profile Image Select">
				<option <?php echo selected( $status, 'enabled', false ); ?> value="enabled"> <?php esc_html_e( 'Published', 'wubtitle' ); ?></option>
				<option <?php echo selected( $status, 'draft', false ); ?> value="draft"> <?php esc_html_e( 'Draft', 'wubtitle' ); ?></option>
			</select>
			<?php
			$form_fields['e2w_status']['html'] .= ob_get_clean();
		}

		// Add a label for video language.
		$form_fields['e2w_lang'] = array(
			'label' => __( 'Language', 'wubtitle' ),
			'input' => 'html',
			'html'  => '<label for="attachments-' . $post->ID . '-e2w_lang">' . $this->get_video_language( $post->ID ) . '</label>',
			'value' => $post->ID,
		);
		return $form_fields;
	}

	/**
	 * Check free plan languages.
	 *
	 * @param string $lang_code language code.
	 */
	private function is_pro_only( $lang_code ) {
		$free_lang = array( 'it', 'en' );
		return get_option( 'wubtitle_free', true ) && ! in_array( $lang_code, $free_lang, true );
	}

	/**
	 * Language select options.
	 *
	 * @param string $lang language code.
	 */
	private function language_options( $lang ) {
		$languages = array(
			'it' => __( 'Italian', 'wubtitle' ),
			'en' => __( 'English', 'wubtitle' ),
			'es' => __( 'Spanish', 'wubtitle' ),
			'de' => __( 'German', 'wubtitle' ),
			'zh' => __( 'Chinese', 'wubtitle' ),
			'fr' => __( 'French', 'wubtitle' ),
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
	 * Add wubtitle form into "add media" tab.
	 *
	 * @param array $form_fields modal window fields.
	 * @param array $post attachment.
	 */
	public function add_generate_subtitle_form_into_media_library( $form_fields, $post ) {
		global $pagenow;
		$all_status = array(
			'pending' => __( 'Generating', 'wubtitle' ),
			'draft'   => __( 'Draft', 'wubtitle' ),
			'enabled' => __( 'Published', 'wubtitle' ),
			'none'    => __( 'None', 'wubtitle' ),
			'error'   => __( 'Error', 'wubtitle' ),
		);
		if ( ! wp_attachment_is( 'video', $post ) || 'post.php' !== $pagenow ) {
			return $form_fields;
		}
		if ( get_option( 'wubtitle_free' ) && 'video/mp4' !== $post->post_mime_type ) {
			$form_fields['e2w_status'] = array(
				'label' => 'Wubtitle',
				'input' => 'html',
				'html'  => '<label for="attachments-' . $post->ID . '-e2w_status">' . __( 'Unsupported video format for free plan', 'wubtitle' ) . '</label>',
				'value' => $post->ID,
			);
			return $form_fields;
		}
		$status = empty( $post->wubtitle_status ) ? 'none' : $post->wubtitle_status;

		// Add a select to enable/disable subtitles.
		if ( 'draft' === $status || 'enabled' === $status ) {
			$form_fields = $this->create_toolbar_and_select( $status, $post->ID );
			return $form_fields;
		}

		// Add header.
		$form_fields['e2w_header']['tr'] = '<strong> ' . __( 'Subtitles', 'wubtitle' ) . ' </strong>';

		// Add subtitle state.
		$form_fields['e2w_status'] = array(
			'label' => __( 'Status', 'wubtitle' ),
			'input' => 'html',
			'html'  => '<label for="attachments-' . $post->ID . '-e2w_status">' . $all_status[ $status ] . '</label>',
			'value' => $post->ID,
		);
		$status_none               = array(
			'none',
			'error',
		);

		// Add language select and subtitle button.
		if ( in_array( $status, $status_none, true ) ) {
			$form_fields['e2w_form'] = $this->create_select_and_button( $post->ID );
			return $form_fields;
		}

		// Add video language label.
		$form_fields['e2w_lang'] = array(
			'label' => __( 'Language', 'wubtitle' ),
			'input' => 'html',
			'html'  => '<label for="attachments-' . $post->ID . '-e2w_lang">' . $this->get_video_language( $post->ID ) . '</label>',
			'value' => $post->ID,
		);

		// Add a paragraph.
		$form_fields['e2w_lang']['helps'] = __( 'Wait while subtitles are created. Subtitles will be available as soon as possible', 'wubtitle' );
		return $form_fields;
	}

	/**
	 * Toolbar creation, add subtitles languages label and publishing select.
	 *
	 * @param string $status subtitles state.
	 * @param int    $id_video video id.
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
				<option <?php echo selected( $status, 'enabled', false ); ?> value="enabled"> <?php esc_html_e( 'Published', 'wubtitle' ); ?></option>
				<option <?php echo selected( $status, 'draft', false ); ?> value="draft"> <?php esc_html_e( 'Draft', 'wubtitle' ); ?></option>
			</select>
		</div>
		<!-- <textarea style="width:100%" class="wp-editor-area" cols="40" rows="5"></textarea> -->
		<?php
			$form_fields['e2w_status']['tr'] = ob_get_clean();
			return $form_fields;
	}
	/**
	 * Create language select and subtitle generation button.
	 *
	 * @param int $id_video video id.
	 */
	private function create_select_and_button( $id_video ) {
		$form_fields = array(
			'label' => __( 'Language', 'wubtitle' ),
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
			<?php esc_html_e( 'GENERATE SUBTITLES', 'wubtitle' ); ?>
			</button>
			<?php
			$form_fields['html'] .= ob_get_clean();
			return $form_fields;
	}

	/**
	 * Request subtitle generation to endpoint, then save uuid and state.
	 *
	 * @param array $post attachment data.
	 * @param array $attachment custom input data.
	 */
	public function video_attachment_fields_to_save( $post, $attachment ) {
		if ( isset( $attachment['select-status'] ) ) {
			update_post_meta( $post['ID'], 'wubtitle_status', $attachment['select-status'] );
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
			$license_key = get_option( 'wubtitle_license_key' );
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
	 * Add subtitles overriding video shortcode.
	 *
	 * @param string $html shortcode html.
	 * @param array  $attr shortcode attributes.
	 */
	public function wubtitle_video_shortcode( $html, $attr ) {
		remove_filter( 'wp_video_shortcode_override', array( $this, 'wubtitle_video_shortcode' ), 10 );
		$source   = array_key_exists( 'mp4', $attr ) ? $attr['mp4'] : $attr['src'];
		$id_video = attachment_url_to_postid( $source );
		$subtitle = get_post_meta( $id_video, 'wubtitle_subtitle', true );
		$lang     = $this->get_video_language( $id_video );
		$status   = get_post_meta( $id_video, 'wubtitle_status', true );
		if ( '' !== $subtitle && 'enabled' === $status ) {
			$content = '<track srclang="it" label="' . $lang . '" kind="subtitles" src="' . wp_get_attachment_url( $subtitle ) . '" default>';
			$html    = wp_video_shortcode( $attr, $content );
		}
		add_filter( 'wp_video_shortcode_override', array( $this, 'wubtitle_video_shortcode' ), 10, 4 );
		return $html;
	}
	/**
	 * Get and translate video language.
	 *
	 * @param int $id_video video id.
	 */
	public function get_video_language( $id_video ) {
		$lang     = get_post_meta( $id_video, 'wubtitle_lang_video', true );
		$all_lang = array(
			'it' => __( 'Italian', 'wubtitle' ),
			'en' => __( 'English', 'wubtitle' ),
			'es' => __( 'Spanish', 'wubtitle' ),
			'de' => __( 'German ', 'wubtitle' ),
			'zh' => __( 'Chinese', 'wubtitle' ),
			'fr' => __( 'French', 'wubtitle' ),
		);
		return array_key_exists( $lang, $all_lang ) ? $all_lang[ $lang ] : 'Undefined';
	}
}
