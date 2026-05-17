<?php
/**
 * Pagina admin colloqui: lista + registrazione esito.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Colloqui {

	const NONCE = 'ig_enna_admin_colloqui';

	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'handle_actions' ] );
	}

	public static function handle_actions() {
		if ( empty( $_POST['ig_enna_admin_action'] ) ) { return; }
		if ( ! current_user_can( 'ig_enna_manage_tickets' ) ) { return; }
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::NONCE ) ) {
			return;
		}
		$action = sanitize_key( $_POST['ig_enna_admin_action'] );
		if ( $action !== 'colloquio_create' ) { return; }

		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$time = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '12:00';
		IG_Enna_Colloqui::create( [
			'user_id'     => isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0,
			'operator_id' => get_current_user_id(),
			'date'        => $date ? $date . ' ' . $time . ':00' : '',
			'area_slug'   => isset( $_POST['area_slug'] ) ? sanitize_title( wp_unslash( $_POST['area_slug'] ) ) : '',
			'outcome'     => isset( $_POST['outcome'] )   ? wp_unslash( $_POST['outcome'] )   : '',
			'next_step'   => isset( $_POST['next_step'] ) ? wp_unslash( $_POST['next_step'] ) : '',
		] );
		wp_safe_redirect( add_query_arg( [ 'page' => 'ig-enna-colloqui', 'created' => 1 ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function render() {
		if ( ! current_user_can( 'ig_enna_manage_tickets' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}

		$rows  = IG_Enna_Colloqui::query( [ 'limit' => 50 ] );
		$users = get_users( [ 'role__in' => [ 'subscriber', 'ig_enna_operator' ], 'fields' => [ 'ID', 'display_name', 'user_email' ], 'number' => 500 ] );
		$areas = get_terms( [ 'taxonomy' => 'ig_area', 'hide_empty' => false ] );
		?>
		<div class="wrap ig-enna-admin">
			<h1><?php esc_html_e( 'Colloqui di orientamento', 'ig-enna' ); ?></h1>

			<?php if ( isset( $_GET['created'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Colloquio registrato.', 'ig-enna' ); ?></p></div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Registra colloquio', 'ig-enna' ); ?></h2>
			<form method="post" class="ig-enna-card-admin" style="max-width:760px;">
				<?php wp_nonce_field( self::NONCE, '_ig_nonce' ); ?>
				<input type="hidden" name="ig_enna_admin_action" value="colloquio_create" />

				<div class="ig-enna-form-row">
					<p>
						<label><strong><?php esc_html_e( 'Utente', 'ig-enna' ); ?></strong></label>
						<select name="user_id" required class="widefat">
							<option value=""><?php esc_html_e( '— Seleziona —', 'ig-enna' ); ?></option>
							<?php foreach ( $users as $u ) : ?>
								<option value="<?php echo (int) $u->ID; ?>"><?php echo esc_html( $u->display_name . ' · ' . $u->user_email ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Area', 'ig-enna' ); ?></strong></label>
						<select name="area_slug" class="widefat">
							<option value=""><?php esc_html_e( '— Non specificata —', 'ig-enna' ); ?></option>
							<?php foreach ( $areas as $t ) : ?>
								<option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				</div>

				<div class="ig-enna-form-row">
					<p>
						<label><strong><?php esc_html_e( 'Data', 'ig-enna' ); ?></strong></label>
						<input type="date" name="date" required />
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Ora', 'ig-enna' ); ?></strong></label>
						<input type="time" name="time" />
					</p>
				</div>

				<p>
					<label><strong><?php esc_html_e( 'Esito / Note', 'ig-enna' ); ?></strong></label>
					<textarea name="outcome" rows="4" class="widefat" required placeholder="<?php esc_attr_e( 'Riassunto del colloquio…', 'ig-enna' ); ?>"></textarea>
				</p>

				<p>
					<label><strong><?php esc_html_e( 'Prossimo passo', 'ig-enna' ); ?></strong></label>
					<textarea name="next_step" rows="2" class="widefat" placeholder="<?php esc_attr_e( 'Azione successiva concordata…', 'ig-enna' ); ?>"></textarea>
				</p>

				<button class="button button-primary"><?php esc_html_e( 'Salva colloquio', 'ig-enna' ); ?></button>
			</form>

			<h2 style="margin-top:32px;"><?php esc_html_e( 'Ultimi colloqui', 'ig-enna' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width:140px;"><?php esc_html_e( 'Data', 'ig-enna' ); ?></th>
						<th style="width:160px;"><?php esc_html_e( 'Utente', 'ig-enna' ); ?></th>
						<th style="width:120px;"><?php esc_html_e( 'Operatore', 'ig-enna' ); ?></th>
						<th style="width:100px;"><?php esc_html_e( 'Area', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Esito', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Prossimo passo', 'ig-enna' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $rows ) : foreach ( $rows as $r ) :
						$user = $r['user_id'] ? get_userdata( (int) $r['user_id'] ) : null;
						$op   = $r['operator_id'] ? get_userdata( (int) $r['operator_id'] ) : null;
						$ts   = strtotime( $r['date'] );
					?>
						<tr>
							<td><?php echo $ts ? esc_html( date_i18n( 'd M Y H:i', $ts ) ) : '—'; ?></td>
							<td><?php echo $user ? esc_html( $user->display_name ) : '—'; ?></td>
							<td><?php echo $op ? esc_html( $op->display_name ) : '—'; ?></td>
							<td><?php echo $r['area_slug'] ? '<span class="ig-enna-badge ig-enna-badge--area-' . esc_attr( $r['area_slug'] ) . '">' . esc_html( $r['area_slug'] ) . '</span>' : '—'; ?></td>
							<td><?php echo esc_html( wp_trim_words( $r['outcome'], 25 ) ); ?></td>
							<td><?php echo esc_html( wp_trim_words( $r['next_step'], 15 ) ); ?></td>
						</tr>
					<?php endforeach; else : ?>
						<tr><td colspan="6"><?php esc_html_e( 'Nessun colloquio registrato.', 'ig-enna' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
