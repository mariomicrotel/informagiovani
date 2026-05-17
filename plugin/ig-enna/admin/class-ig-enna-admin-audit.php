<?php
/**
 * Pagina admin: visualizzazione audit log.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Audit {

	public static function render() {
		if ( ! current_user_can( 'ig_enna_view_reports' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}

		$f_action = isset( $_GET['log_action'] ) ? sanitize_key( $_GET['log_action'] ) : '';
		$f_object = isset( $_GET['object_type'] ) ? sanitize_key( $_GET['object_type'] ) : '';
		$paged    = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 );
		$per_page = 50;

		$res = IG_Enna_Audit::query( [
			'action'      => $f_action ?: null,
			'object_type' => $f_object ?: null,
			'limit'       => $per_page,
			'offset'      => ( $paged - 1 ) * $per_page,
		] );
		?>
		<div class="wrap ig-enna-admin">
			<h1><?php esc_html_e( 'Audit log', 'ig-enna' ); ?></h1>

			<?php if ( ! IG_Enna_Audit::is_enabled() ) : ?>
				<div class="notice notice-warning"><p>
					<?php esc_html_e( 'Audit log disabilitato. Abilitalo da Impostazioni → Abilita audit log.', 'ig-enna' ); ?>
				</p></div>
			<?php endif; ?>

			<form method="get" style="margin: 14px 0;">
				<input type="hidden" name="page" value="ig-enna-audit" />
				<input type="text" name="log_action" value="<?php echo esc_attr( $f_action ); ?>" placeholder="<?php esc_attr_e( 'Action (es. ticket.update)', 'ig-enna' ); ?>" />
				<select name="object_type">
					<option value=""><?php esc_html_e( 'Qualsiasi oggetto', 'ig-enna' ); ?></option>
					<?php foreach ( [ 'ticket', 'ig_scheda', 'ig_evento', 'ig_partner', 'ig_percorso' ] as $t ) : ?>
						<option value="<?php echo esc_attr( $t ); ?>" <?php selected( $f_object, $t ); ?>><?php echo esc_html( $t ); ?></option>
					<?php endforeach; ?>
				</select>
				<button class="button"><?php esc_html_e( 'Filtra', 'ig-enna' ); ?></button>
			</form>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width:140px;"><?php esc_html_e( 'Quando', 'ig-enna' ); ?></th>
						<th style="width:140px;"><?php esc_html_e( 'Utente', 'ig-enna' ); ?></th>
						<th style="width:160px;"><?php esc_html_e( 'Azione', 'ig-enna' ); ?></th>
						<th style="width:120px;"><?php esc_html_e( 'Oggetto', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Dettagli', 'ig-enna' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $res['rows'] ) : foreach ( $res['rows'] as $r ) :
						$u  = $r['user_id'] ? get_userdata( (int) $r['user_id'] ) : null;
						$ts = strtotime( $r['created_at'] );
						$meta = $r['meta'] ? json_decode( $r['meta'], true ) : null;
					?>
						<tr>
							<td><?php echo $ts ? esc_html( date_i18n( 'd M Y H:i', $ts ) ) : '—'; ?></td>
							<td><?php echo $u ? esc_html( $u->display_name ?: $u->user_login ) : '<em>sistema</em>'; ?></td>
							<td><code><?php echo esc_html( $r['action'] ); ?></code></td>
							<td><?php echo esc_html( $r['object_type'] ); ?> #<?php echo (int) $r['object_id']; ?></td>
							<td><?php
								if ( is_array( $meta ) ) {
									echo '<code style="font-size:11px;">' . esc_html( wp_json_encode( $meta, JSON_UNESCAPED_UNICODE ) ) . '</code>';
								} else {
									echo '—';
								}
							?></td>
						</tr>
					<?php endforeach; else : ?>
						<tr><td colspan="5"><?php esc_html_e( 'Nessun evento registrato.', 'ig-enna' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php
			$total_pages = max( 1, (int) ceil( $res['total'] / $per_page ) );
			if ( $total_pages > 1 ) :
				$base_url = admin_url( 'admin.php?page=ig-enna-audit' );
				if ( $f_action ) { $base_url = add_query_arg( 'log_action', $f_action, $base_url ); }
				if ( $f_object ) { $base_url = add_query_arg( 'object_type', $f_object, $base_url ); } ?>
				<div class="tablenav"><div class="tablenav-pages">
					<?php for ( $i = 1; $i <= $total_pages; $i++ ) :
						$cur = $i === $paged ? 'current' : ''; $pu = esc_url( add_query_arg( 'paged', $i, $base_url ) ); ?>
						<a class="button <?php echo esc_attr( $cur ); ?>" href="<?php echo $pu; ?>"><?php echo $i; ?></a>
					<?php endfor; ?>
				</div></div>
			<?php endif; ?>
		</div>
		<?php
	}
}
