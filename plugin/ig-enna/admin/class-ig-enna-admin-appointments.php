<?php
/**
 * Pagina admin appuntamenti: lista + creazione manuale.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Appointments {

	const NONCE = 'ig_enna_admin_appointments';

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

		if ( $action === 'appointment_create' ) {
			$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
			$time_start = isset( $_POST['time_start'] ) ? sanitize_text_field( wp_unslash( $_POST['time_start'] ) ) : '';
			$time_end   = isset( $_POST['time_end'] )   ? sanitize_text_field( wp_unslash( $_POST['time_end'] ) )   : '';
			IG_Enna_Appointments::create( [
				'user_id'     => isset( $_POST['user_id'] )     ? (int) $_POST['user_id']         : 0,
				'operator_id' => isset( $_POST['operator_id'] ) ? (int) $_POST['operator_id']     : get_current_user_id(),
				'slot_start'  => $date && $time_start ? $date . ' ' . $time_start . ':00' : '',
				'slot_end'    => $date && $time_end   ? $date . ' ' . $time_end   . ':00' : '',
				'mode'        => isset( $_POST['mode'] )  ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'presenza',
				'status'      => 'confirmed',
				'notes'       => isset( $_POST['notes'] ) ? wp_unslash( $_POST['notes'] ) : '',
			] );
			wp_safe_redirect( add_query_arg( [ 'page' => 'ig-enna-appointments', 'created' => 1 ], admin_url( 'admin.php' ) ) );
			exit;
		}

		if ( $action === 'appointment_status' ) {
			$id = (int) ( $_POST['appointment_id'] ?? 0 );
			IG_Enna_Appointments::update( $id, [ 'status' => sanitize_key( $_POST['status'] ?? '' ) ] );
			wp_safe_redirect( add_query_arg( [ 'page' => 'ig-enna-appointments', 'updated' => 1 ], admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	public static function render() {
		if ( ! current_user_can( 'ig_enna_manage_tickets' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}

		$rows = IG_Enna_Appointments::query( [ 'limit' => 50 ] );
		$ops  = get_users( [ 'role__in' => [ 'ig_enna_operator', 'ig_enna_responsabile', 'administrator' ], 'fields' => [ 'ID', 'display_name' ], 'number' => 200 ] );
		$users = get_users( [ 'role__in' => [ 'subscriber', 'ig_enna_operator' ], 'fields' => [ 'ID', 'display_name', 'user_email' ], 'number' => 500 ] );
		?>
		<div class="wrap ig-enna-admin">
			<h1><?php esc_html_e( 'Appuntamenti', 'ig-enna' ); ?></h1>

			<?php if ( isset( $_GET['created'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Appuntamento creato.', 'ig-enna' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Stato aggiornato.', 'ig-enna' ); ?></p></div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Nuovo appuntamento', 'ig-enna' ); ?></h2>
			<form method="post" class="ig-enna-card-admin" style="max-width:760px;">
				<?php wp_nonce_field( self::NONCE, '_ig_nonce' ); ?>
				<input type="hidden" name="ig_enna_admin_action" value="appointment_create" />

				<div class="ig-enna-form-row">
					<p>
						<label><strong><?php esc_html_e( 'Utente', 'ig-enna' ); ?></strong></label>
						<select name="user_id" class="widefat" required>
							<option value=""><?php esc_html_e( '— Seleziona —', 'ig-enna' ); ?></option>
							<?php foreach ( $users as $u ) : ?>
								<option value="<?php echo (int) $u->ID; ?>"><?php echo esc_html( $u->display_name . ' · ' . $u->user_email ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Operatore', 'ig-enna' ); ?></strong></label>
						<select name="operator_id" class="widefat">
							<?php $current = get_current_user_id(); foreach ( $ops as $u ) : ?>
								<option value="<?php echo (int) $u->ID; ?>" <?php selected( $u->ID, $current ); ?>><?php echo esc_html( $u->display_name ); ?></option>
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
						<label><strong><?php esc_html_e( 'Dalle', 'ig-enna' ); ?></strong></label>
						<input type="time" name="time_start" required />
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Alle', 'ig-enna' ); ?></strong></label>
						<input type="time" name="time_end" required />
					</p>
				</div>

				<p>
					<label><strong><?php esc_html_e( 'Modalità', 'ig-enna' ); ?></strong></label>
					<select name="mode">
						<?php foreach ( IG_Enna_Appointments::modes() as $k => $label ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<p>
					<label><strong><?php esc_html_e( 'Note', 'ig-enna' ); ?></strong></label>
					<textarea name="notes" rows="3" class="widefat"></textarea>
				</p>

				<button class="button button-primary"><?php esc_html_e( 'Crea appuntamento', 'ig-enna' ); ?></button>
			</form>

			<h2 style="margin-top:32px;"><?php esc_html_e( 'Prossimi appuntamenti', 'ig-enna' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Quando', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Utente', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Operatore', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Modalità', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Stato', 'ig-enna' ); ?></th>
						<th style="width:200px;"><?php esc_html_e( 'Azioni', 'ig-enna' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $rows ) : foreach ( $rows as $r ) :
						$user = $r['user_id'] ? get_userdata( (int) $r['user_id'] ) : null;
						$op   = $r['operator_id'] ? get_userdata( (int) $r['operator_id'] ) : null;
						$ts   = strtotime( $r['slot_start'] );
						$te   = strtotime( $r['slot_end'] );
					?>
						<tr>
							<td>
								<?php echo $ts ? esc_html( date_i18n( 'd M Y H:i', $ts ) ) : '—'; ?>
								<?php if ( $te ) : ?> – <?php echo esc_html( date_i18n( 'H:i', $te ) ); ?><?php endif; ?>
							</td>
							<td><?php echo $user ? esc_html( $user->display_name ) : '—'; ?></td>
							<td><?php echo $op ? esc_html( $op->display_name ) : '—'; ?></td>
							<td><?php echo esc_html( IG_Enna_Appointments::modes()[ $r['mode'] ] ?? $r['mode'] ); ?></td>
							<td><span class="ig-enna-badge ig-enna-badge--apstate-<?php echo esc_attr( $r['status'] ); ?>"><?php echo esc_html( IG_Enna_Appointments::status_label( $r['status'] ) ); ?></span></td>
							<td>
								<form method="post" style="display:inline-flex;gap:6px;">
									<?php wp_nonce_field( self::NONCE, '_ig_nonce' ); ?>
									<input type="hidden" name="ig_enna_admin_action" value="appointment_status" />
									<input type="hidden" name="appointment_id" value="<?php echo (int) $r['id']; ?>" />
									<select name="status">
										<?php foreach ( IG_Enna_Appointments::statuses() as $k => $label ) : ?>
											<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $r['status'], $k ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
									<button class="button button-small"><?php esc_html_e( 'Aggiorna', 'ig-enna' ); ?></button>
								</form>
							</td>
						</tr>
					<?php endforeach; else : ?>
						<tr><td colspan="6"><?php esc_html_e( 'Nessun appuntamento programmato.', 'ig-enna' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
