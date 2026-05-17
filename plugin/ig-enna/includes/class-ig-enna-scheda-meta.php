<?php
/**
 * Metabox e meta per il CPT ig_scheda.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Scheda_Meta {

	const NONCE_KEY  = 'ig_enna_scheda_nonce';
	const NONCE_NAME = 'ig_enna_scheda_nonce_field';

	/** Stati workflow custom (oltre allo status WP). */
	public static function workflow_states() {
		return [
			'draft'   => __( 'Bozza',          'ig-enna' ),
			'review'  => __( 'Da verificare',  'ig-enna' ),
			'valid'   => __( 'Validata',       'ig-enna' ),
			'pub'     => __( 'Pubblicata',     'ig-enna' ),
			'update'  => __( 'Da aggiornare',  'ig-enna' ),
			'exp'     => __( 'Scaduta',        'ig-enna' ),
			'archive' => __( 'Archiviata',     'ig-enna' ),
		];
	}

	/** Classi di fonte (allineate al design system). */
	public static function source_classes() {
		return [
			'ufficiale'  => __( 'Ufficiale',  'ig-enna' ),
			'partner'    => __( 'Partner',    'ig-enna' ),
			'verificata' => __( 'Verificata', 'ig-enna' ),
		];
	}

	/** Meta keys gestite. */
	public static function meta_keys() {
		return [
			'_ig_enna_codice',
			'_ig_enna_short',
			'_ig_enna_tipo',
			'_ig_enna_deadline',
			'_ig_enna_deadline_label',
			'_ig_enna_contributo',
			'_ig_enna_durata',
			'_ig_enna_source',
			'_ig_enna_source_url',
			'_ig_enna_source_class',
			'_ig_enna_workflow_state',
		];
	}

	public static function init() {
		add_action( 'add_meta_boxes_ig_scheda', [ __CLASS__, 'register_metaboxes' ] );
		add_action( 'save_post_ig_scheda',      [ __CLASS__, 'save' ], 10, 2 );
		add_action( 'init',                     [ __CLASS__, 'register_meta' ] );
	}

	public static function register_meta() {
		foreach ( self::meta_keys() as $key ) {
			register_post_meta( 'ig_scheda', $key, [
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			] );
		}
	}

	public static function register_metaboxes() {
		add_meta_box(
			'ig_enna_scheda_details',
			__( 'Dettagli scheda', 'ig-enna' ),
			[ __CLASS__, 'render_details' ],
			'ig_scheda',
			'normal',
			'high'
		);
		add_meta_box(
			'ig_enna_scheda_workflow',
			__( 'Stato e fonte', 'ig-enna' ),
			[ __CLASS__, 'render_workflow' ],
			'ig_scheda',
			'side',
			'default'
		);
	}

	public static function render_details( $post ) {
		wp_nonce_field( self::NONCE_KEY, self::NONCE_NAME );

		$codice          = get_post_meta( $post->ID, '_ig_enna_codice', true );
		$short           = get_post_meta( $post->ID, '_ig_enna_short', true );
		$tipo            = get_post_meta( $post->ID, '_ig_enna_tipo', true );
		$deadline        = get_post_meta( $post->ID, '_ig_enna_deadline', true );
		$deadline_label  = get_post_meta( $post->ID, '_ig_enna_deadline_label', true );
		$contributo      = get_post_meta( $post->ID, '_ig_enna_contributo', true );
		$durata          = get_post_meta( $post->ID, '_ig_enna_durata', true );
		?>
		<div class="ig-enna-meta-grid">
			<p>
				<label for="ig_enna_codice"><strong><?php esc_html_e( 'Codice scheda', 'ig-enna' ); ?></strong></label>
				<input type="text" id="ig_enna_codice" name="ig_enna_codice"
					value="<?php echo esc_attr( $codice ); ?>"
					placeholder="es. ERA-2026-117"
					class="widefat ig-enna-input" />
				<span class="description"><?php esc_html_e( 'Identificativo univoco visualizzato nelle liste pubbliche e admin.', 'ig-enna' ); ?></span>
			</p>

			<p>
				<label for="ig_enna_short"><strong><?php esc_html_e( 'Sintesi breve', 'ig-enna' ); ?></strong></label>
				<textarea id="ig_enna_short" name="ig_enna_short" rows="2" class="widefat ig-enna-input"
					maxlength="280" placeholder="<?php esc_attr_e( 'Una o due frasi per le anteprime (max 280 caratteri).', 'ig-enna' ); ?>"><?php echo esc_textarea( $short ); ?></textarea>
			</p>

			<p>
				<label for="ig_enna_tipo"><strong><?php esc_html_e( 'Tipo', 'ig-enna' ); ?></strong></label>
				<input type="text" id="ig_enna_tipo" name="ig_enna_tipo"
					value="<?php echo esc_attr( $tipo ); ?>"
					placeholder="<?php esc_attr_e( 'es. Tirocinio, Concorso, Bando impresa, Bonus…', 'ig-enna' ); ?>"
					class="widefat ig-enna-input" />
			</p>

			<div class="ig-enna-meta-row">
				<p>
					<label for="ig_enna_deadline"><strong><?php esc_html_e( 'Scadenza (data)', 'ig-enna' ); ?></strong></label>
					<input type="date" id="ig_enna_deadline" name="ig_enna_deadline"
						value="<?php echo esc_attr( $deadline ); ?>" class="ig-enna-input" />
					<span class="description"><?php esc_html_e( 'Lascia vuoto per "Sempre aperta".', 'ig-enna' ); ?></span>
				</p>
				<p>
					<label for="ig_enna_deadline_label"><strong><?php esc_html_e( 'Etichetta scadenza', 'ig-enna' ); ?></strong></label>
					<input type="text" id="ig_enna_deadline_label" name="ig_enna_deadline_label"
						value="<?php echo esc_attr( $deadline_label ); ?>"
						placeholder="<?php esc_attr_e( 'es. 17 marzo 2026 · 12:00', 'ig-enna' ); ?>"
						class="widefat ig-enna-input" />
				</p>
			</div>

			<div class="ig-enna-meta-row">
				<p>
					<label for="ig_enna_contributo"><strong><?php esc_html_e( 'Contributo', 'ig-enna' ); ?></strong></label>
					<input type="text" id="ig_enna_contributo" name="ig_enna_contributo"
						value="<?php echo esc_attr( $contributo ); ?>"
						placeholder="<?php esc_attr_e( 'es. 550–700€ al mese', 'ig-enna' ); ?>"
						class="widefat ig-enna-input" />
				</p>
				<p>
					<label for="ig_enna_durata"><strong><?php esc_html_e( 'Durata', 'ig-enna' ); ?></strong></label>
					<input type="text" id="ig_enna_durata" name="ig_enna_durata"
						value="<?php echo esc_attr( $durata ); ?>"
						placeholder="<?php esc_attr_e( 'es. 2–12 mesi', 'ig-enna' ); ?>"
						class="widefat ig-enna-input" />
				</p>
			</div>
		</div>
		<?php
	}

	public static function render_workflow( $post ) {
		$source       = get_post_meta( $post->ID, '_ig_enna_source', true );
		$source_url   = get_post_meta( $post->ID, '_ig_enna_source_url', true );
		$source_class = get_post_meta( $post->ID, '_ig_enna_source_class', true );
		$state        = get_post_meta( $post->ID, '_ig_enna_workflow_state', true );
		if ( empty( $state ) ) {
			$state = 'draft';
		}
		?>
		<p>
			<label for="ig_enna_workflow_state"><strong><?php esc_html_e( 'Stato workflow', 'ig-enna' ); ?></strong></label>
			<select id="ig_enna_workflow_state" name="ig_enna_workflow_state" class="widefat">
				<?php foreach ( self::workflow_states() as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $state, $k ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<span class="description"><?php esc_html_e( 'Stato editoriale interno (badge nella lista).', 'ig-enna' ); ?></span>
		</p>

		<p>
			<label for="ig_enna_source"><strong><?php esc_html_e( 'Fonte', 'ig-enna' ); ?></strong></label>
			<input type="text" id="ig_enna_source" name="ig_enna_source"
				value="<?php echo esc_attr( $source ); ?>"
				placeholder="<?php esc_attr_e( 'es. Invitalia, INDIRE, Comune di Enna', 'ig-enna' ); ?>"
				class="widefat" />
		</p>

		<p>
			<label for="ig_enna_source_url"><strong><?php esc_html_e( 'URL fonte', 'ig-enna' ); ?></strong></label>
			<input type="url" id="ig_enna_source_url" name="ig_enna_source_url"
				value="<?php echo esc_attr( $source_url ); ?>"
				placeholder="https://…" class="widefat" />
		</p>

		<p>
			<label for="ig_enna_source_class"><strong><?php esc_html_e( 'Classe fonte', 'ig-enna' ); ?></strong></label>
			<select id="ig_enna_source_class" name="ig_enna_source_class" class="widefat">
				<option value=""><?php esc_html_e( '— Non specificata —', 'ig-enna' ); ?></option>
				<?php foreach ( self::source_classes() as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $source_class, $k ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_KEY ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = [
			'_ig_enna_codice'         => 'sanitize_text_field',
			'_ig_enna_short'          => 'sanitize_textarea_field',
			'_ig_enna_tipo'           => 'sanitize_text_field',
			'_ig_enna_deadline'       => [ __CLASS__, 'sanitize_date' ],
			'_ig_enna_deadline_label' => 'sanitize_text_field',
			'_ig_enna_contributo'     => 'sanitize_text_field',
			'_ig_enna_durata'         => 'sanitize_text_field',
			'_ig_enna_source'         => 'sanitize_text_field',
			'_ig_enna_source_url'     => 'esc_url_raw',
			'_ig_enna_source_class'   => [ __CLASS__, 'sanitize_source_class' ],
			'_ig_enna_workflow_state' => [ __CLASS__, 'sanitize_state' ],
		];

		foreach ( $fields as $meta_key => $sanitizer ) {
			$form_key = ltrim( $meta_key, '_' );
			if ( ! array_key_exists( $form_key, $_POST ) ) {
				continue;
			}
			$raw   = wp_unslash( $_POST[ $form_key ] );
			$value = call_user_func( $sanitizer, $raw );
			if ( $value === '' || $value === null ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	public static function sanitize_date( $value ) {
		$value = sanitize_text_field( $value );
		if ( $value === '' ) {
			return '';
		}
		// Accetta solo YYYY-MM-DD.
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return $value;
		}
		return '';
	}

	public static function sanitize_state( $value ) {
		$value = sanitize_key( $value );
		return array_key_exists( $value, self::workflow_states() ) ? $value : 'draft';
	}

	public static function sanitize_source_class( $value ) {
		$value = sanitize_key( $value );
		return array_key_exists( $value, self::source_classes() ) ? $value : '';
	}

	/**
	 * Calcola urgenza (urgent/soon/ok) dato un meta deadline YYYY-MM-DD.
	 *
	 * @param string $deadline
	 * @return string ''|'urgent'|'soon'|'ok'|'exp'
	 */
	public static function compute_urgency( $deadline ) {
		if ( empty( $deadline ) ) {
			return 'ok';
		}
		$ts_dead = strtotime( $deadline . ' 23:59:59' );
		if ( ! $ts_dead ) {
			return '';
		}
		$days = (int) floor( ( $ts_dead - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
		if ( $days < 0 )  { return 'exp'; }
		if ( $days <= 7 ) { return 'urgent'; }
		if ( $days <= 21 ){ return 'soon'; }
		return 'ok';
	}
}
