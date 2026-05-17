<?php
/**
 * Metabox per il CPT ig_partner.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Partner_Meta {

	const NONCE_KEY  = 'ig_enna_partner_nonce';
	const NONCE_NAME = 'ig_enna_partner_nonce_field';

	public static function types() {
		return [
			'universita'    => __( 'Università / ITS',        'ig-enna' ),
			'ente_pubblico' => __( 'Ente pubblico',           'ig-enna' ),
			'camera_comm'   => __( 'Camera di Commercio',     'ig-enna' ),
			'azienda'       => __( 'Azienda',                 'ig-enna' ),
			'ong'           => __( 'ONG / Terzo settore',     'ig-enna' ),
			'fondazione'    => __( 'Fondazione / ITS Academy','ig-enna' ),
			'altro'         => __( 'Altro',                   'ig-enna' ),
		];
	}

	public static function meta_keys() {
		return [
			'_ig_enna_partner_type',
			'_ig_enna_partner_area',
			'_ig_enna_partner_website',
			'_ig_enna_partner_email',
			'_ig_enna_partner_phone',
			'_ig_enna_partner_address',
		];
	}

	public static function init() {
		add_action( 'add_meta_boxes_ig_partner', [ __CLASS__, 'register_metaboxes' ] );
		add_action( 'save_post_ig_partner',      [ __CLASS__, 'save' ], 10, 2 );
		add_action( 'init',                      [ __CLASS__, 'register_meta' ] );

		add_filter( 'manage_ig_partner_posts_columns',       [ __CLASS__, 'columns' ] );
		add_action( 'manage_ig_partner_posts_custom_column', [ __CLASS__, 'column_value' ], 10, 2 );
	}

	public static function register_meta() {
		foreach ( self::meta_keys() as $key ) {
			register_post_meta( 'ig_partner', $key, [
				'show_in_rest' => false,
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
			'ig_enna_partner_details',
			__( 'Dettagli partner', 'ig-enna' ),
			[ __CLASS__, 'render' ],
			'ig_partner',
			'normal',
			'high'
		);
	}

	public static function render( $post ) {
		wp_nonce_field( self::NONCE_KEY, self::NONCE_NAME );
		$type    = get_post_meta( $post->ID, '_ig_enna_partner_type', true );
		$area    = get_post_meta( $post->ID, '_ig_enna_partner_area', true );
		$website = get_post_meta( $post->ID, '_ig_enna_partner_website', true );
		$email   = get_post_meta( $post->ID, '_ig_enna_partner_email', true );
		$phone   = get_post_meta( $post->ID, '_ig_enna_partner_phone', true );
		$address = get_post_meta( $post->ID, '_ig_enna_partner_address', true );
		?>
		<div class="ig-enna-meta-grid">
			<div class="ig-enna-meta-row">
				<p>
					<label><strong><?php esc_html_e( 'Tipo ente', 'ig-enna' ); ?></strong></label>
					<select name="ig_enna_partner_type" class="widefat">
						<option value=""><?php esc_html_e( '— Seleziona —', 'ig-enna' ); ?></option>
						<?php foreach ( self::types() as $k => $label ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $type, $k ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label><strong><?php esc_html_e( 'Area di competenza', 'ig-enna' ); ?></strong></label>
					<input class="widefat ig-enna-input" type="text" name="ig_enna_partner_area" value="<?php echo esc_attr( $area ); ?>" placeholder="<?php esc_attr_e( 'es. Formazione tecnica, Impresa, ...', 'ig-enna' ); ?>" />
				</p>
			</div>

			<div class="ig-enna-meta-row">
				<p>
					<label><strong><?php esc_html_e( 'Sito web', 'ig-enna' ); ?></strong></label>
					<input class="widefat" type="url" name="ig_enna_partner_website" value="<?php echo esc_attr( $website ); ?>" placeholder="https://…" />
				</p>
				<p>
					<label><strong><?php esc_html_e( 'Email', 'ig-enna' ); ?></strong></label>
					<input class="widefat" type="email" name="ig_enna_partner_email" value="<?php echo esc_attr( $email ); ?>" />
				</p>
			</div>

			<div class="ig-enna-meta-row">
				<p>
					<label><strong><?php esc_html_e( 'Telefono', 'ig-enna' ); ?></strong></label>
					<input class="widefat" type="tel" name="ig_enna_partner_phone" value="<?php echo esc_attr( $phone ); ?>" />
				</p>
				<p>
					<label><strong><?php esc_html_e( 'Indirizzo', 'ig-enna' ); ?></strong></label>
					<input class="widefat ig-enna-input" type="text" name="ig_enna_partner_address" value="<?php echo esc_attr( $address ); ?>" />
				</p>
			</div>
		</div>
		<?php
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) { return; }
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_KEY ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$fields = [
			'_ig_enna_partner_type'    => [ __CLASS__, 'sanitize_type' ],
			'_ig_enna_partner_area'    => 'sanitize_text_field',
			'_ig_enna_partner_website' => 'esc_url_raw',
			'_ig_enna_partner_email'   => 'sanitize_email',
			'_ig_enna_partner_phone'   => 'sanitize_text_field',
			'_ig_enna_partner_address' => 'sanitize_text_field',
		];
		foreach ( $fields as $meta => $san ) {
			$form = ltrim( $meta, '_' );
			if ( ! isset( $_POST[ $form ] ) ) { continue; }
			$v = call_user_func( $san, wp_unslash( $_POST[ $form ] ) );
			if ( $v === '' || $v === null ) {
				delete_post_meta( $post_id, $meta );
			} else {
				update_post_meta( $post_id, $meta, $v );
			}
		}
	}

	public static function sanitize_type( $v ) {
		$v = sanitize_key( $v );
		return array_key_exists( $v, self::types() ) ? $v : '';
	}

	public static function columns( $cols ) {
		$new = [];
		$new['cb']         = $cols['cb'] ?? '';
		$new['title']      = __( 'Nome', 'ig-enna' );
		$new['ig_p_type']  = __( 'Tipo', 'ig-enna' );
		$new['ig_p_area']  = __( 'Area', 'ig-enna' );
		$new['ig_p_web']   = __( 'Contatti', 'ig-enna' );
		$new['date']       = $cols['date'] ?? __( 'Data', 'ig-enna' );
		return $new;
	}

	public static function column_value( $col, $post_id ) {
		switch ( $col ) {
			case 'ig_p_type':
				$t = get_post_meta( $post_id, '_ig_enna_partner_type', true );
				$labels = self::types();
				echo $t && isset( $labels[ $t ] ) ? '<span class="ig-enna-badge ig-enna-badge--ptype-' . esc_attr( $t ) . '">' . esc_html( $labels[ $t ] ) . '</span>' : '—';
				break;
			case 'ig_p_area':
				echo esc_html( get_post_meta( $post_id, '_ig_enna_partner_area', true ) ?: '—' );
				break;
			case 'ig_p_web':
				$w = get_post_meta( $post_id, '_ig_enna_partner_website', true );
				$e = get_post_meta( $post_id, '_ig_enna_partner_email', true );
				$out = [];
				if ( $w ) { $out[] = '<a href="' . esc_url( $w ) . '" target="_blank" rel="noopener">' . esc_html( wp_parse_url( $w, PHP_URL_HOST ) ?: $w ) . '</a>'; }
				if ( $e ) { $out[] = '<a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a>'; }
				echo $out ? implode( '<br>', $out ) : '—';
				break;
		}
	}
}
