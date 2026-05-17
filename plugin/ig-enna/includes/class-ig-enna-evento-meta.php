<?php
/**
 * Metabox e meta per il CPT ig_evento.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Evento_Meta {

	const NONCE_KEY  = 'ig_enna_evento_nonce';
	const NONCE_NAME = 'ig_enna_evento_nonce_field';

	public static function modes() {
		return [
			'presenza' => __( 'In presenza', 'ig-enna' ),
			'online'   => __( 'Online',      'ig-enna' ),
			'misto'    => __( 'Misto',       'ig-enna' ),
		];
	}

	public static function statuses() {
		return [
			'open'      => __( 'Iscrizioni aperte', 'ig-enna' ),
			'full'      => __( 'Posti esauriti',    'ig-enna' ),
			'closed'    => __( 'Iscrizioni chiuse', 'ig-enna' ),
			'cancelled' => __( 'Annullato',         'ig-enna' ),
			'done'      => __( 'Concluso',          'ig-enna' ),
		];
	}

	public static function meta_keys() {
		return [
			'_ig_enna_event_date',
			'_ig_enna_event_time',
			'_ig_enna_event_mode',
			'_ig_enna_event_place',
			'_ig_enna_event_url',
			'_ig_enna_event_capacity',
			'_ig_enna_event_target_label',
			'_ig_enna_event_status',
		];
	}

	public static function init() {
		add_action( 'add_meta_boxes_ig_evento', [ __CLASS__, 'register_metaboxes' ] );
		add_action( 'save_post_ig_evento',      [ __CLASS__, 'save' ], 10, 2 );
		add_action( 'init',                     [ __CLASS__, 'register_meta' ] );
	}

	public static function register_meta() {
		foreach ( self::meta_keys() as $key ) {
			register_post_meta( 'ig_evento', $key, [
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
			'ig_enna_evento_details',
			__( 'Dettagli evento', 'ig-enna' ),
			[ __CLASS__, 'render' ],
			'ig_evento',
			'normal',
			'high'
		);
	}

	public static function render( $post ) {
		wp_nonce_field( self::NONCE_KEY, self::NONCE_NAME );

		$date     = get_post_meta( $post->ID, '_ig_enna_event_date', true );
		$time     = get_post_meta( $post->ID, '_ig_enna_event_time', true );
		$mode     = get_post_meta( $post->ID, '_ig_enna_event_mode', true );
		$place    = get_post_meta( $post->ID, '_ig_enna_event_place', true );
		$url      = get_post_meta( $post->ID, '_ig_enna_event_url', true );
		$capacity = get_post_meta( $post->ID, '_ig_enna_event_capacity', true );
		$target   = get_post_meta( $post->ID, '_ig_enna_event_target_label', true );
		$status   = get_post_meta( $post->ID, '_ig_enna_event_status', true );
		if ( empty( $status ) ) {
			$status = 'open';
		}
		?>
		<div class="ig-enna-meta-grid">
			<div class="ig-enna-meta-row">
				<p>
					<label for="ig_enna_event_date"><strong><?php esc_html_e( 'Data', 'ig-enna' ); ?></strong></label>
					<input type="date" id="ig_enna_event_date" name="ig_enna_event_date"
						value="<?php echo esc_attr( $date ); ?>" class="ig-enna-input" required />
				</p>
				<p>
					<label for="ig_enna_event_time"><strong><?php esc_html_e( 'Ora', 'ig-enna' ); ?></strong></label>
					<input type="time" id="ig_enna_event_time" name="ig_enna_event_time"
						value="<?php echo esc_attr( $time ); ?>" class="ig-enna-input" />
				</p>
			</div>

			<div class="ig-enna-meta-row">
				<p>
					<label for="ig_enna_event_mode"><strong><?php esc_html_e( 'Modalità', 'ig-enna' ); ?></strong></label>
					<select id="ig_enna_event_mode" name="ig_enna_event_mode" class="widefat">
						<?php foreach ( self::modes() as $k => $label ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $mode, $k ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label for="ig_enna_event_status"><strong><?php esc_html_e( 'Stato iscrizioni', 'ig-enna' ); ?></strong></label>
					<select id="ig_enna_event_status" name="ig_enna_event_status" class="widefat">
						<?php foreach ( self::statuses() as $k => $label ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $status, $k ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			</div>

			<p>
				<label for="ig_enna_event_place"><strong><?php esc_html_e( 'Luogo / Sala', 'ig-enna' ); ?></strong></label>
				<input type="text" id="ig_enna_event_place" name="ig_enna_event_place"
					value="<?php echo esc_attr( $place ); ?>"
					placeholder="<?php esc_attr_e( 'es. Sala Cerere · Castello di Lombardia', 'ig-enna' ); ?>"
					class="widefat ig-enna-input" />
			</p>

			<p>
				<label for="ig_enna_event_url"><strong><?php esc_html_e( 'URL (per eventi online)', 'ig-enna' ); ?></strong></label>
				<input type="url" id="ig_enna_event_url" name="ig_enna_event_url"
					value="<?php echo esc_attr( $url ); ?>"
					placeholder="https://meet.io/…" class="widefat" />
			</p>

			<div class="ig-enna-meta-row">
				<p>
					<label for="ig_enna_event_capacity"><strong><?php esc_html_e( 'Capienza', 'ig-enna' ); ?></strong></label>
					<input type="number" id="ig_enna_event_capacity" name="ig_enna_event_capacity"
						value="<?php echo esc_attr( $capacity ); ?>" min="0" class="small-text" />
				</p>
				<p>
					<label for="ig_enna_event_target_label"><strong><?php esc_html_e( 'Target (etichetta)', 'ig-enna' ); ?></strong></label>
					<input type="text" id="ig_enna_event_target_label" name="ig_enna_event_target_label"
						value="<?php echo esc_attr( $target ); ?>"
						placeholder="<?php esc_attr_e( 'es. 18–30 anni', 'ig-enna' ); ?>"
						class="widefat ig-enna-input" />
				</p>
			</div>
		</div>
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
			'_ig_enna_event_date'         => [ __CLASS__, 'sanitize_date' ],
			'_ig_enna_event_time'         => [ __CLASS__, 'sanitize_time' ],
			'_ig_enna_event_mode'         => [ __CLASS__, 'sanitize_mode' ],
			'_ig_enna_event_place'        => 'sanitize_text_field',
			'_ig_enna_event_url'          => 'esc_url_raw',
			'_ig_enna_event_capacity'     => [ __CLASS__, 'sanitize_int' ],
			'_ig_enna_event_target_label' => 'sanitize_text_field',
			'_ig_enna_event_status'       => [ __CLASS__, 'sanitize_status' ],
		];

		foreach ( $fields as $meta_key => $sanitizer ) {
			$form_key = ltrim( $meta_key, '_' );
			if ( ! array_key_exists( $form_key, $_POST ) ) {
				continue;
			}
			$value = call_user_func( $sanitizer, wp_unslash( $_POST[ $form_key ] ) );
			if ( $value === '' || $value === null ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, (string) $value );
			}
		}
	}

	public static function sanitize_date( $v ) {
		$v = sanitize_text_field( $v );
		return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $v ) ? $v : '';
	}

	public static function sanitize_time( $v ) {
		$v = sanitize_text_field( $v );
		return preg_match( '/^\d{2}:\d{2}(:\d{2})?$/', $v ) ? substr( $v, 0, 5 ) : '';
	}

	public static function sanitize_mode( $v ) {
		$v = sanitize_key( $v );
		return array_key_exists( $v, self::modes() ) ? $v : 'presenza';
	}

	public static function sanitize_status( $v ) {
		$v = sanitize_key( $v );
		return array_key_exists( $v, self::statuses() ) ? $v : 'open';
	}

	public static function sanitize_int( $v ) {
		return $v === '' ? '' : (string) max( 0, (int) $v );
	}
}
