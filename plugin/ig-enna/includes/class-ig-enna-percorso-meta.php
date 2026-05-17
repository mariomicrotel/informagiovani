<?php
/**
 * Metabox per il CPT ig_percorso (percorsi impresa).
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Percorso_Meta {

	const NONCE_KEY  = 'ig_enna_percorso_nonce';
	const NONCE_NAME = 'ig_enna_percorso_nonce_field';
	const USER_META_PATHS = 'ig_percorso_ids';

	public static function tipi() {
		return [
			'business_plan' => __( 'Business Plan',       'ig-enna' ),
			'microcredito'  => __( 'Microcredito',        'ig-enna' ),
			'resto_al_sud'  => __( 'Resto al Sud',        'ig-enna' ),
			'startup_giov'  => __( 'Start-up giovanile',  'ig-enna' ),
			'orientamento'  => __( 'Orientamento',        'ig-enna' ),
		];
	}

	public static function meta_keys() {
		return [
			'_ig_enna_percorso_tipo',
			'_ig_enna_percorso_durata',
			'_ig_enna_percorso_fasi',
			'_ig_enna_percorso_referente',
		];
	}

	public static function init() {
		add_action( 'add_meta_boxes_ig_percorso', [ __CLASS__, 'register_metaboxes' ] );
		add_action( 'save_post_ig_percorso',      [ __CLASS__, 'save' ], 10, 2 );

		add_filter( 'manage_ig_percorso_posts_columns',       [ __CLASS__, 'columns' ] );
		add_action( 'manage_ig_percorso_posts_custom_column', [ __CLASS__, 'column_value' ], 10, 2 );

		// User profile screens: assegnazione percorsi.
		add_action( 'show_user_profile', [ __CLASS__, 'user_profile_fields' ] );
		add_action( 'edit_user_profile', [ __CLASS__, 'user_profile_fields' ] );
		add_action( 'personal_options_update',  [ __CLASS__, 'save_user_profile_fields' ] );
		add_action( 'edit_user_profile_update', [ __CLASS__, 'save_user_profile_fields' ] );
	}

	public static function register_metaboxes() {
		add_meta_box(
			'ig_enna_percorso_details',
			__( 'Dettagli percorso', 'ig-enna' ),
			[ __CLASS__, 'render' ],
			'ig_percorso',
			'normal',
			'high'
		);
		add_meta_box(
			'ig_enna_percorso_users',
			__( 'Utenti assegnati', 'ig-enna' ),
			[ __CLASS__, 'render_users' ],
			'ig_percorso',
			'side',
			'default'
		);
	}

	public static function render( $post ) {
		wp_nonce_field( self::NONCE_KEY, self::NONCE_NAME );
		$tipo     = get_post_meta( $post->ID, '_ig_enna_percorso_tipo', true );
		$durata   = get_post_meta( $post->ID, '_ig_enna_percorso_durata', true );
		$fasi     = get_post_meta( $post->ID, '_ig_enna_percorso_fasi', true );
		$referente= get_post_meta( $post->ID, '_ig_enna_percorso_referente', true );
		?>
		<div class="ig-enna-meta-grid">
			<div class="ig-enna-meta-row">
				<p>
					<label><strong><?php esc_html_e( 'Tipo percorso', 'ig-enna' ); ?></strong></label>
					<select name="ig_enna_percorso_tipo" class="widefat">
						<option value=""><?php esc_html_e( '— Seleziona —', 'ig-enna' ); ?></option>
						<?php foreach ( self::tipi() as $k => $label ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $tipo, $k ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label><strong><?php esc_html_e( 'Durata', 'ig-enna' ); ?></strong></label>
					<input class="widefat ig-enna-input" type="text" name="ig_enna_percorso_durata" value="<?php echo esc_attr( $durata ); ?>" placeholder="<?php esc_attr_e( 'es. 8 settimane · 4 incontri', 'ig-enna' ); ?>" />
				</p>
			</div>

			<p>
				<label><strong><?php esc_html_e( 'Referente / Tutor', 'ig-enna' ); ?></strong></label>
				<input class="widefat ig-enna-input" type="text" name="ig_enna_percorso_referente" value="<?php echo esc_attr( $referente ); ?>" />
			</p>

			<p>
				<label><strong><?php esc_html_e( 'Fasi del percorso', 'ig-enna' ); ?></strong></label>
				<textarea class="widefat ig-enna-input" name="ig_enna_percorso_fasi" rows="6" placeholder="<?php esc_attr_e( "Una fase per riga. Esempio:\nIdeazione\nBusiness Model Canvas\nPiano finanziario\nPresentazione finale", 'ig-enna' ); ?>"><?php echo esc_textarea( $fasi ); ?></textarea>
				<span class="description"><?php esc_html_e( 'Una fase per riga.', 'ig-enna' ); ?></span>
			</p>
		</div>
		<?php
	}

	public static function render_users( $post ) {
		$assigned = self::users_for_path( $post->ID );
		echo '<p class="description">' . esc_html__( 'Utenti che stanno seguendo questo percorso. Modifica l\'assegnazione dalla scheda dell\'utente.', 'ig-enna' ) . '</p>';
		if ( $assigned ) {
			echo '<ul style="margin:0;padding-left:18px;">';
			foreach ( $assigned as $u ) {
				printf( '<li><a href="%s">%s</a></li>', esc_url( get_edit_user_link( $u->ID ) ), esc_html( $u->display_name ?: $u->user_login ) );
			}
			echo '</ul>';
		} else {
			echo '<p><em>' . esc_html__( 'Nessun utente assegnato.', 'ig-enna' ) . '</em></p>';
		}
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) { return; }
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_KEY ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$tipo = isset( $_POST['ig_enna_percorso_tipo'] ) ? sanitize_key( wp_unslash( $_POST['ig_enna_percorso_tipo'] ) ) : '';
		if ( $tipo && array_key_exists( $tipo, self::tipi() ) ) {
			update_post_meta( $post_id, '_ig_enna_percorso_tipo', $tipo );
		} else {
			delete_post_meta( $post_id, '_ig_enna_percorso_tipo' );
		}

		$durata = isset( $_POST['ig_enna_percorso_durata'] ) ? sanitize_text_field( wp_unslash( $_POST['ig_enna_percorso_durata'] ) ) : '';
		$ref    = isset( $_POST['ig_enna_percorso_referente'] ) ? sanitize_text_field( wp_unslash( $_POST['ig_enna_percorso_referente'] ) ) : '';
		$fasi   = isset( $_POST['ig_enna_percorso_fasi'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ig_enna_percorso_fasi'] ) ) : '';

		$durata ? update_post_meta( $post_id, '_ig_enna_percorso_durata',    $durata ) : delete_post_meta( $post_id, '_ig_enna_percorso_durata' );
		$ref    ? update_post_meta( $post_id, '_ig_enna_percorso_referente', $ref )    : delete_post_meta( $post_id, '_ig_enna_percorso_referente' );
		$fasi   ? update_post_meta( $post_id, '_ig_enna_percorso_fasi',      $fasi )   : delete_post_meta( $post_id, '_ig_enna_percorso_fasi' );
	}

	public static function fasi_as_array( $post_id ) {
		$raw = (string) get_post_meta( $post_id, '_ig_enna_percorso_fasi', true );
		if ( $raw === '' ) { return []; }
		$lines = preg_split( '/\r\n|\r|\n/', $raw );
		return array_values( array_filter( array_map( 'trim', $lines ) ) );
	}

	public static function columns( $cols ) {
		$new = [];
		$new['cb']        = $cols['cb'] ?? '';
		$new['title']     = __( 'Percorso', 'ig-enna' );
		$new['ig_tipo']   = __( 'Tipo', 'ig-enna' );
		$new['ig_durata'] = __( 'Durata', 'ig-enna' );
		$new['ig_users']  = __( 'Utenti', 'ig-enna' );
		$new['date']      = $cols['date'] ?? __( 'Data', 'ig-enna' );
		return $new;
	}

	public static function column_value( $col, $post_id ) {
		switch ( $col ) {
			case 'ig_tipo':
				$t = get_post_meta( $post_id, '_ig_enna_percorso_tipo', true );
				$labels = self::tipi();
				echo $t && isset( $labels[ $t ] ) ? '<span class="ig-enna-badge ig-enna-badge--ptipo-' . esc_attr( $t ) . '">' . esc_html( $labels[ $t ] ) . '</span>' : '—';
				break;
			case 'ig_durata':
				echo esc_html( get_post_meta( $post_id, '_ig_enna_percorso_durata', true ) ?: '—' );
				break;
			case 'ig_users':
				$n = count( self::users_for_path( $post_id ) );
				echo '<span class="ig-enna-num">' . (int) $n . '</span>';
				break;
		}
	}

	/* ---------- Assegnazione utenti ---------- */

	public static function paths_for_user( $user_id ) {
		$val = get_user_meta( $user_id, self::USER_META_PATHS, true );
		if ( ! is_array( $val ) ) { return []; }
		return array_map( 'intval', $val );
	}

	public static function users_for_path( $post_id ) {
		return get_users( [
			'meta_key'     => self::USER_META_PATHS,
			'meta_value'   => $post_id,
			'meta_compare' => 'LIKE',
			'fields'       => [ 'ID', 'display_name', 'user_login' ],
			'number'       => 200,
		] );
	}

	public static function user_profile_fields( $user ) {
		if ( ! current_user_can( 'ig_enna_manage_tickets' ) ) { return; }
		$assigned = self::paths_for_user( $user->ID );
		$paths    = get_posts( [
			'post_type'      => 'ig_percorso',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title', 'order' => 'ASC',
		] );
		?>
		<h2><?php esc_html_e( 'Percorsi impresa assegnati', 'ig-enna' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="ig_enna_user_paths"><?php esc_html_e( 'Percorsi', 'ig-enna' ); ?></label></th>
				<td>
					<?php wp_nonce_field( 'ig_enna_user_paths', '_ig_enna_user_paths_nonce' ); ?>
					<?php if ( $paths ) : ?>
						<?php foreach ( $paths as $p ) : ?>
							<label style="display:block;margin-bottom:4px;">
								<input type="checkbox" name="ig_enna_user_paths[]" value="<?php echo (int) $p->ID; ?>" <?php checked( in_array( (int) $p->ID, $assigned, true ) ); ?> />
								<?php echo esc_html( $p->post_title ); ?>
							</label>
						<?php endforeach; ?>
					<?php else : ?>
						<em><?php esc_html_e( 'Nessun percorso disponibile. Creane uno dalla voce Percorsi.', 'ig-enna' ); ?></em>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function save_user_profile_fields( $user_id ) {
		if ( ! current_user_can( 'ig_enna_manage_tickets' ) ) { return; }
		if ( ! isset( $_POST['_ig_enna_user_paths_nonce'] ) ) { return; }
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_enna_user_paths_nonce'] ) ), 'ig_enna_user_paths' ) ) { return; }

		$ids = isset( $_POST['ig_enna_user_paths'] ) && is_array( $_POST['ig_enna_user_paths'] )
			? array_map( 'intval', wp_unslash( $_POST['ig_enna_user_paths'] ) )
			: [];
		$ids = array_values( array_unique( array_filter( $ids, function ( $i ) {
			$p = get_post( $i );
			return $p && $p->post_type === 'ig_percorso';
		} ) ) );
		update_user_meta( $user_id, self::USER_META_PATHS, $ids );
	}
}
