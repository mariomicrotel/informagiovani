<?php
/**
 * Pagina admin: gestione ticket (lista + dettaglio + azioni).
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Tickets {

	const NONCE = 'ig_enna_admin_tickets';

	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'handle_actions' ] );
		add_action( 'admin_init', [ __CLASS__, 'handle_export' ] );
	}

	public static function handle_export() {
		if ( empty( $_GET['ig_enna_export'] ) || $_GET['ig_enna_export'] !== 'tickets' ) {
			return;
		}
		if ( ! current_user_can( 'ig_enna_export_data' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}
		if ( ! isset( $_GET['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_ig_nonce'] ) ), 'ig_enna_export_tickets' ) ) {
			wp_die( esc_html__( 'Sessione scaduta.', 'ig-enna' ) );
		}

		$args = [
			'limit'  => 100000,
			'status' => isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : null,
			'area'   => isset( $_GET['area'] )   ? sanitize_title( $_GET['area'] ) : null,
		];
		$res = IG_Enna_Tickets::query( $args );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="ig-enna-tickets-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, [ 'id', 'user', 'operator', 'subject', 'area', 'priority', 'status', 'sla_due', 'created_at', 'updated_at' ] );
		foreach ( $res['rows'] as $r ) {
			$u  = $r['user_id'] ? get_userdata( (int) $r['user_id'] ) : null;
			$op = $r['operator_id'] ? get_userdata( (int) $r['operator_id'] ) : null;
			fputcsv( $out, [
				'R-' . $r['id'],
				$u ? $u->user_email : '',
				$op ? $op->user_login : '',
				$r['subject'], $r['area_slug'], $r['priority'], $r['status'],
				$r['sla_due'], $r['created_at'], $r['updated_at'],
			] );
		}
		fclose( $out );

		IG_Enna_Audit::log( 'tickets_export', 'ticket', 0, [ 'count' => $res['total'], 'filters' => $args ] );
		exit;
	}

	public static function handle_actions() {
		if ( empty( $_POST['ig_enna_admin_action'] ) ) {
			return;
		}
		if ( ! current_user_can( 'ig_enna_manage_tickets' ) ) {
			return;
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::NONCE ) ) {
			wp_die( esc_html__( 'Sessione scaduta.', 'ig-enna' ) );
		}

		$action = sanitize_key( $_POST['ig_enna_admin_action'] );
		$id     = isset( $_POST['ticket_id'] ) ? (int) $_POST['ticket_id'] : 0;
		if ( ! $id ) { return; }

		if ( $action === 'update_ticket' ) {
			IG_Enna_Tickets::update( $id, [
				'status'      => isset( $_POST['status'] )      ? sanitize_key( $_POST['status'] )    : null,
				'priority'    => isset( $_POST['priority'] )    ? sanitize_key( $_POST['priority'] )  : null,
				'operator_id' => isset( $_POST['operator_id'] ) ? (int) $_POST['operator_id']         : null,
			] );
			wp_safe_redirect( add_query_arg( [ 'page' => 'ig-enna-tickets', 'view' => $id, 'updated' => 1 ], admin_url( 'admin.php' ) ) );
			exit;
		}

		if ( $action === 'delete_ticket' && current_user_can( 'ig_enna_manage' ) ) {
			IG_Enna_Tickets::delete( $id );
			wp_safe_redirect( add_query_arg( [ 'page' => 'ig-enna-tickets', 'deleted' => 1 ], admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	public static function render() {
		if ( ! current_user_can( 'ig_enna_manage_tickets' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}

		$view = isset( $_GET['view'] ) ? (int) $_GET['view'] : 0;
		if ( $view ) {
			self::render_detail( $view );
			return;
		}
		self::render_list();
	}

	private static function render_list() {
		$filter_status = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$filter_area   = isset( $_GET['area'] )   ? sanitize_title( $_GET['area'] ) : '';
		$paged         = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 );
		$per_page      = 20;

		$res = IG_Enna_Tickets::query( [
			'status' => $filter_status ?: null,
			'area'   => $filter_area   ?: null,
			'limit'  => $per_page,
			'offset' => ( $paged - 1 ) * $per_page,
		] );

		$counts = IG_Enna_Tickets::count_by_status();
		$total  = array_sum( $counts );
		?>
		<?php
		$export_url = wp_nonce_url(
			add_query_arg( array_filter( [
				'page' => 'ig-enna-tickets', 'ig_enna_export' => 'tickets',
				'status' => $filter_status ?: null, 'area' => $filter_area ?: null,
			] ), admin_url( 'admin.php' ) ),
			'ig_enna_export_tickets', '_ig_nonce'
		);
		?>
		<div class="wrap ig-enna-admin">
			<h1>
				<?php esc_html_e( 'Ticket / Richieste', 'ig-enna' ); ?>
				<?php if ( current_user_can( 'ig_enna_export_data' ) ) : ?>
					<a class="page-title-action" href="<?php echo esc_url( $export_url ); ?>">
						<?php esc_html_e( 'Esporta CSV', 'ig-enna' ); ?>
					</a>
				<?php endif; ?>
			</h1>

			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Ticket aggiornato.', 'ig-enna' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Ticket eliminato.', 'ig-enna' ); ?></p></div>
			<?php endif; ?>

			<ul class="subsubsub">
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-tickets' ) ); ?>" class="<?php echo $filter_status === '' ? 'current' : ''; ?>">
					<?php esc_html_e( 'Tutti', 'ig-enna' ); ?> <span class="count">(<?php echo (int) $total; ?>)</span></a>
				</li>
				<?php foreach ( IG_Enna_Tickets::statuses() as $k => $label ) :
					$n   = isset( $counts[ $k ] ) ? (int) $counts[ $k ] : 0;
					$url = esc_url( admin_url( 'admin.php?page=ig-enna-tickets&status=' . $k ) );
					$cur = $filter_status === $k ? 'current' : ''; ?>
					<li>|
						<a href="<?php echo $url; ?>" class="<?php echo esc_attr( $cur ); ?>">
							<?php echo esc_html( $label ); ?> <span class="count">(<?php echo $n; ?>)</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<div style="clear:both"></div>

			<form method="get" style="margin: 14px 0;">
				<input type="hidden" name="page" value="ig-enna-tickets" />
				<select name="area">
					<option value=""><?php esc_html_e( 'Tutte le aree', 'ig-enna' ); ?></option>
					<?php foreach ( get_terms( [ 'taxonomy' => 'ig_area', 'hide_empty' => false ] ) as $t ) : ?>
						<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $filter_area, $t->slug ); ?>>
							<?php echo esc_html( $t->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php if ( $filter_status ) : ?>
					<input type="hidden" name="status" value="<?php echo esc_attr( $filter_status ); ?>" />
				<?php endif; ?>
				<button class="button"><?php esc_html_e( 'Filtra', 'ig-enna' ); ?></button>
			</form>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 80px;"><?php esc_html_e( 'ID', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Oggetto', 'ig-enna' ); ?></th>
						<th style="width: 120px;"><?php esc_html_e( 'Utente', 'ig-enna' ); ?></th>
						<th style="width: 100px;"><?php esc_html_e( 'Area', 'ig-enna' ); ?></th>
						<th style="width: 80px;"><?php esc_html_e( 'Priorità', 'ig-enna' ); ?></th>
						<th style="width: 120px;"><?php esc_html_e( 'Stato', 'ig-enna' ); ?></th>
						<th style="width: 120px;"><?php esc_html_e( 'Operatore', 'ig-enna' ); ?></th>
						<th style="width: 140px;"><?php esc_html_e( 'Creato', 'ig-enna' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $res['rows'] ) : foreach ( $res['rows'] as $row ) :
						$user = $row['user_id'] ? get_userdata( (int) $row['user_id'] ) : null;
						$op   = $row['operator_id'] ? get_userdata( (int) $row['operator_id'] ) : null;
						$ts   = strtotime( $row['created_at'] );
						$url  = esc_url( admin_url( 'admin.php?page=ig-enna-tickets&view=' . (int) $row['id'] ) );
					?>
						<tr>
							<td><a href="<?php echo $url; ?>"><strong>R-<?php echo (int) $row['id']; ?></strong></a></td>
							<td><a href="<?php echo $url; ?>"><?php echo esc_html( $row['subject'] ); ?></a></td>
							<td><?php echo $user ? esc_html( $user->display_name ?: $user->user_login ) : '—'; ?></td>
							<td><?php echo $row['area_slug'] ? '<span class="ig-enna-badge ig-enna-badge--area-' . esc_attr( $row['area_slug'] ) . '">' . esc_html( $row['area_slug'] ) . '</span>' : '—'; ?></td>
							<td><span class="ig-enna-badge ig-enna-badge--prio-<?php echo esc_attr( $row['priority'] ); ?>"><?php echo esc_html( IG_Enna_Tickets::priority_label( $row['priority'] ) ); ?></span></td>
							<td><span class="ig-enna-badge ig-enna-badge--tkstate-<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( IG_Enna_Tickets::status_label( $row['status'] ) ); ?></span></td>
							<td><?php echo $op ? esc_html( $op->display_name ) : '<em>—</em>'; ?></td>
							<td><?php echo $ts ? esc_html( date_i18n( 'd M Y H:i', $ts ) ) : '—'; ?></td>
						</tr>
					<?php endforeach; else : ?>
						<tr><td colspan="8"><?php esc_html_e( 'Nessun ticket trovato.', 'ig-enna' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php
			$total_pages = max( 1, (int) ceil( $res['total'] / $per_page ) );
			if ( $total_pages > 1 ) :
				$base_url = admin_url( 'admin.php?page=ig-enna-tickets' );
				if ( $filter_status ) { $base_url = add_query_arg( 'status', $filter_status, $base_url ); }
				if ( $filter_area )   { $base_url = add_query_arg( 'area',   $filter_area,   $base_url ); }
			?>
				<div class="tablenav"><div class="tablenav-pages">
					<?php for ( $i = 1; $i <= $total_pages; $i++ ) :
						$cur = $i === $paged ? 'current' : '';
						$pu  = esc_url( add_query_arg( 'paged', $i, $base_url ) ); ?>
						<a class="button <?php echo esc_attr( $cur ); ?>" href="<?php echo $pu; ?>"><?php echo $i; ?></a>
					<?php endfor; ?>
				</div></div>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function render_detail( $id ) {
		$tk = IG_Enna_Tickets::get( $id );
		if ( ! $tk ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Ticket non trovato', 'ig-enna' ) . '</h1></div>';
			return;
		}
		$user = $tk['user_id']     ? get_userdata( (int) $tk['user_id'] ) : null;
		$op   = $tk['operator_id'] ? get_userdata( (int) $tk['operator_id'] ) : null;
		$ts   = strtotime( $tk['created_at'] );

		// Lista operatori candidati (ig_enna_operator + ig_enna_responsabile + administrator).
		$ops = get_users( [
			'role__in' => [ 'ig_enna_operator', 'ig_enna_responsabile', 'administrator' ],
			'orderby'  => 'display_name',
			'fields'   => [ 'ID', 'display_name', 'user_login' ],
			'number'   => 200,
		] );
		?>
		<div class="wrap ig-enna-admin">
			<h1>
				<?php
				/* translators: %d = ticket id */
				printf( esc_html__( 'Ticket R-%d', 'ig-enna' ), (int) $tk['id'] );
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-tickets' ) ); ?>" class="page-title-action">
					<?php esc_html_e( '← Tutti i ticket', 'ig-enna' ); ?>
				</a>
			</h1>

			<div class="ig-enna-ticket-detail">
				<div class="ig-enna-ticket-detail__main">
					<div class="ig-enna-card-admin">
						<div class="ig-enna-ticket-detail__badges">
							<span class="ig-enna-badge ig-enna-badge--tkstate-<?php echo esc_attr( $tk['status'] ); ?>"><?php echo esc_html( IG_Enna_Tickets::status_label( $tk['status'] ) ); ?></span>
							<span class="ig-enna-badge ig-enna-badge--prio-<?php echo esc_attr( $tk['priority'] ); ?>"><?php echo esc_html( IG_Enna_Tickets::priority_label( $tk['priority'] ) ); ?></span>
							<?php if ( $tk['area_slug'] ) : ?>
								<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $tk['area_slug'] ); ?>"><?php echo esc_html( $tk['area_slug'] ); ?></span>
							<?php endif; ?>
						</div>
						<h2 style="margin:8px 0 4px;"><?php echo esc_html( $tk['subject'] ); ?></h2>
						<p class="description">
							<?php echo $user ? esc_html( $user->display_name ?: $user->user_login ) : esc_html__( 'Utente non disponibile', 'ig-enna' ); ?>
							· <?php echo $ts ? esc_html( date_i18n( 'd M Y H:i', $ts ) ) : '—'; ?>
							<?php if ( $tk['sla_due'] ) : ?>
								· <?php esc_html_e( 'SLA:', 'ig-enna' ); ?> <?php echo esc_html( date_i18n( 'd M Y H:i', strtotime( $tk['sla_due'] ) ) ); ?>
							<?php endif; ?>
						</p>
						<div class="ig-enna-prose" style="margin-top:18px;">
							<?php echo wp_kses_post( wpautop( $tk['message'] ) ); ?>
						</div>
					</div>
				</div>

				<aside class="ig-enna-ticket-detail__side">
					<form method="post" class="ig-enna-card-admin">
						<?php wp_nonce_field( self::NONCE, '_ig_nonce' ); ?>
						<input type="hidden" name="ig_enna_admin_action" value="update_ticket" />
						<input type="hidden" name="ticket_id" value="<?php echo (int) $tk['id']; ?>" />

						<p>
							<label><strong><?php esc_html_e( 'Stato', 'ig-enna' ); ?></strong></label>
							<select name="status" class="widefat">
								<?php foreach ( IG_Enna_Tickets::statuses() as $k => $label ) : ?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $tk['status'], $k ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<label><strong><?php esc_html_e( 'Priorità', 'ig-enna' ); ?></strong></label>
							<select name="priority" class="widefat">
								<?php foreach ( IG_Enna_Tickets::priorities() as $k => $label ) : ?>
									<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $tk['priority'], $k ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<label><strong><?php esc_html_e( 'Operatore assegnato', 'ig-enna' ); ?></strong></label>
							<select name="operator_id" class="widefat">
								<option value="0"><?php esc_html_e( '— Non assegnato —', 'ig-enna' ); ?></option>
								<?php foreach ( $ops as $u ) : ?>
									<option value="<?php echo (int) $u->ID; ?>" <?php selected( (int) $tk['operator_id'], (int) $u->ID ); ?>>
										<?php echo esc_html( $u->display_name ?: $u->user_login ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</p>
						<button class="button button-primary"><?php esc_html_e( 'Salva', 'ig-enna' ); ?></button>
					</form>

					<?php if ( current_user_can( 'ig_enna_manage' ) ) : ?>
						<form method="post" class="ig-enna-card-admin" style="margin-top:14px;" onsubmit="return confirm('<?php echo esc_js( __( 'Eliminare il ticket?', 'ig-enna' ) ); ?>');">
							<?php wp_nonce_field( self::NONCE, '_ig_nonce' ); ?>
							<input type="hidden" name="ig_enna_admin_action" value="delete_ticket" />
							<input type="hidden" name="ticket_id" value="<?php echo (int) $tk['id']; ?>" />
							<button class="button button-link-delete"><?php esc_html_e( 'Elimina ticket', 'ig-enna' ); ?></button>
						</form>
					<?php endif; ?>
				</aside>
			</div>
		</div>
		<?php
	}
}
