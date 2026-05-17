<?php
/**
 * Pagina admin: iscritti newsletter + export CSV.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Newsletter {

	const NONCE = 'ig_enna_admin_newsletter';

	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'handle_actions' ] );
	}

	public static function handle_actions() {
		if ( empty( $_GET['ig_enna_export'] ) || $_GET['ig_enna_export'] !== 'newsletter' ) {
			if ( empty( $_POST['ig_enna_admin_action'] ) ) { return; }
		}
		if ( ! current_user_can( 'ig_enna_export_data' ) ) { return; }

		// Export CSV.
		if ( ! empty( $_GET['ig_enna_export'] ) && $_GET['ig_enna_export'] === 'newsletter' ) {
			if ( ! isset( $_GET['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_ig_nonce'] ) ), 'ig_enna_export_newsletter' ) ) {
				wp_die( esc_html__( 'Sessione scaduta.', 'ig-enna' ) );
			}
			self::export_csv();
			exit;
		}

		// Delete.
		if ( ! empty( $_POST['ig_enna_admin_action'] ) && $_POST['ig_enna_admin_action'] === 'delete_sub' ) {
			if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::NONCE ) ) {
				wp_die( esc_html__( 'Sessione scaduta.', 'ig-enna' ) );
			}
			$id = (int) ( $_POST['sub_id'] ?? 0 );
			if ( $id ) {
				IG_Enna_Newsletter::delete( $id );
				IG_Enna_Audit::log( 'newsletter_delete', 'newsletter', $id );
			}
			wp_safe_redirect( add_query_arg( [ 'page' => 'ig-enna-newsletter', 'deleted' => 1 ], admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	private static function export_csv() {
		$res = IG_Enna_Newsletter::query( [ 'limit' => 100000 ] );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="ig-enna-newsletter-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$out = fopen( 'php://output', 'w' );
		// BOM per Excel.
		fwrite( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, [ 'id', 'email', 'confirmed', 'interests', 'user_id', 'created_at' ] );
		foreach ( $res['rows'] as $r ) {
			fputcsv( $out, [
				$r['id'], $r['email'], $r['confirmed'] ? 'yes' : 'no',
				$r['interests'], $r['user_id'], $r['created_at'],
			] );
		}
		fclose( $out );

		IG_Enna_Audit::log( 'newsletter_export', 'newsletter', 0, [ 'count' => $res['total'] ] );
	}

	public static function render() {
		if ( ! current_user_can( 'ig_enna_view_reports' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}
		$f_conf = isset( $_GET['confirmed'] ) ? (int) $_GET['confirmed'] : -1;
		$args   = [ 'limit' => 200 ];
		if ( $f_conf === 0 || $f_conf === 1 ) { $args['confirmed'] = $f_conf; }
		$res = IG_Enna_Newsletter::query( $args );

		$export_url = wp_nonce_url(
			admin_url( 'admin.php?page=ig-enna-newsletter&ig_enna_export=newsletter' ),
			'ig_enna_export_newsletter',
			'_ig_nonce'
		);
		?>
		<div class="wrap ig-enna-admin">
			<h1>
				<?php esc_html_e( 'Newsletter · Iscritti', 'ig-enna' ); ?>
				<?php if ( current_user_can( 'ig_enna_export_data' ) ) : ?>
					<a class="page-title-action" href="<?php echo esc_url( $export_url ); ?>">
						<?php esc_html_e( 'Esporta CSV', 'ig-enna' ); ?>
					</a>
				<?php endif; ?>
			</h1>

			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Iscrizione eliminata.', 'ig-enna' ); ?></p></div>
			<?php endif; ?>

			<ul class="subsubsub">
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-newsletter' ) ); ?>" class="<?php echo $f_conf < 0 ? 'current' : ''; ?>"><?php esc_html_e( 'Tutti', 'ig-enna' ); ?></a> |</li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-newsletter&confirmed=1' ) ); ?>" class="<?php echo $f_conf === 1 ? 'current' : ''; ?>"><?php esc_html_e( 'Confermati', 'ig-enna' ); ?></a> |</li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-newsletter&confirmed=0' ) ); ?>" class="<?php echo $f_conf === 0 ? 'current' : ''; ?>"><?php esc_html_e( 'Da confermare', 'ig-enna' ); ?></a></li>
			</ul>
			<div style="clear:both"></div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Email', 'ig-enna' ); ?></th>
						<th style="width:120px;"><?php esc_html_e( 'Stato', 'ig-enna' ); ?></th>
						<th><?php esc_html_e( 'Interessi', 'ig-enna' ); ?></th>
						<th style="width:120px;"><?php esc_html_e( 'Utente', 'ig-enna' ); ?></th>
						<th style="width:140px;"><?php esc_html_e( 'Iscritto il', 'ig-enna' ); ?></th>
						<th style="width:80px;"><?php esc_html_e( 'Azioni', 'ig-enna' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $res['rows'] ) : foreach ( $res['rows'] as $r ) :
						$u = $r['user_id'] ? get_userdata( (int) $r['user_id'] ) : null;
						$ts = strtotime( $r['created_at'] );
					?>
						<tr>
							<td><?php echo esc_html( $r['email'] ); ?></td>
							<td><?php echo $r['confirmed']
								? '<span class="ig-enna-badge ig-enna-badge--state-pub">' . esc_html__( 'Confermato', 'ig-enna' ) . '</span>'
								: '<span class="ig-enna-badge ig-enna-badge--state-review">' . esc_html__( 'In attesa', 'ig-enna' ) . '</span>'; ?>
							</td>
							<td><?php echo esc_html( $r['interests'] ?: '—' ); ?></td>
							<td><?php echo $u ? esc_html( $u->display_name ) : '—'; ?></td>
							<td><?php echo $ts ? esc_html( date_i18n( 'd M Y H:i', $ts ) ) : '—'; ?></td>
							<td>
								<?php if ( current_user_can( 'ig_enna_manage' ) ) : ?>
									<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Eliminare?', 'ig-enna' ) ); ?>');">
										<?php wp_nonce_field( self::NONCE, '_ig_nonce' ); ?>
										<input type="hidden" name="ig_enna_admin_action" value="delete_sub" />
										<input type="hidden" name="sub_id" value="<?php echo (int) $r['id']; ?>" />
										<button class="button-link-delete"><?php esc_html_e( 'Elimina', 'ig-enna' ); ?></button>
									</form>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; else : ?>
						<tr><td colspan="6"><?php esc_html_e( 'Nessun iscritto.', 'ig-enna' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
