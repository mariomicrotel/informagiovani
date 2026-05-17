<?php
/**
 * Pagina admin: Report / KPI.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Report {

	public static function render() {
		if ( ! current_user_can( 'ig_enna_view_reports' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}

		// KPI contenuti.
		$sch_counts = wp_count_posts( 'ig_scheda' );
		$ev_counts  = wp_count_posts( 'ig_evento' );
		$pa_counts  = wp_count_posts( 'ig_partner' );
		$pe_counts  = wp_count_posts( 'ig_percorso' );

		// Ticket per stato.
		$tk_by_status = IG_Enna_Tickets::count_by_status();
		$tk_total     = array_sum( $tk_by_status );
		$tk_open      = 0;
		foreach ( [ 'new', 'assigned', 'work', 'wait' ] as $k ) {
			$tk_open += isset( $tk_by_status[ $k ] ) ? $tk_by_status[ $k ] : 0;
		}

		// Utenti.
		$users_total = count_users();
		$subs_count  = isset( $users_total['avail_roles']['subscriber'] ) ? (int) $users_total['avail_roles']['subscriber'] : 0;

		// Newsletter.
		$nl_total  = IG_Enna_Newsletter::query( [ 'limit' => 1 ] )['total'];
		$nl_confirmed = IG_Enna_Newsletter::query( [ 'limit' => 1, 'confirmed' => 1 ] )['total'];

		// Top schede salvate (le più "saved").
		global $wpdb;
		$saves_tbl = $wpdb->prefix . 'ig_enna_user_saves';
		$top_saved = $wpdb->get_results(
			"SELECT object_id, COUNT(*) AS n FROM {$saves_tbl} WHERE object_type='scheda' GROUP BY object_id ORDER BY n DESC LIMIT 10",
			ARRAY_A
		);

		// Eventi imminenti.
		$upcoming = get_posts( [
			'post_type'      => 'ig_evento',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'meta_key'       => '_ig_enna_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => [ [ 'key' => '_ig_enna_event_date', 'value' => current_time( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' ] ],
		] );

		$open_export_tickets = current_user_can( 'ig_enna_export_data' );
		?>
		<div class="wrap ig-enna-admin">
			<h1><?php esc_html_e( 'Report', 'ig-enna' ); ?></h1>

			<h2><?php esc_html_e( 'Contenuti', 'ig-enna' ); ?></h2>
			<div class="ig-enna-grid">
				<div class="ig-enna-card">
					<div class="ig-enna-card-label"><?php esc_html_e( 'Schede pubblicate', 'ig-enna' ); ?></div>
					<div class="ig-enna-card-num"><?php echo (int) ( $sch_counts->publish ?? 0 ); ?></div>
					<div class="ig-enna-card-hint">bozze: <?php echo (int) ( $sch_counts->draft ?? 0 ); ?></div>
				</div>
				<div class="ig-enna-card">
					<div class="ig-enna-card-label"><?php esc_html_e( 'Eventi pubblicati', 'ig-enna' ); ?></div>
					<div class="ig-enna-card-num"><?php echo (int) ( $ev_counts->publish ?? 0 ); ?></div>
				</div>
				<div class="ig-enna-card">
					<div class="ig-enna-card-label"><?php esc_html_e( 'Partner', 'ig-enna' ); ?></div>
					<div class="ig-enna-card-num"><?php echo (int) ( $pa_counts->publish ?? 0 ); ?></div>
				</div>
				<div class="ig-enna-card">
					<div class="ig-enna-card-label"><?php esc_html_e( 'Percorsi attivi', 'ig-enna' ); ?></div>
					<div class="ig-enna-card-num"><?php echo (int) ( $pe_counts->publish ?? 0 ); ?></div>
				</div>
			</div>

			<h2><?php esc_html_e( 'Ticket', 'ig-enna' ); ?></h2>
			<div class="ig-enna-grid">
				<div class="ig-enna-card">
					<div class="ig-enna-card-label"><?php esc_html_e( 'Totali', 'ig-enna' ); ?></div>
					<div class="ig-enna-card-num"><?php echo (int) $tk_total; ?></div>
				</div>
				<div class="ig-enna-card">
					<div class="ig-enna-card-label"><?php esc_html_e( 'Aperti', 'ig-enna' ); ?></div>
					<div class="ig-enna-card-num"><?php echo (int) $tk_open; ?></div>
					<div class="ig-enna-card-hint"><?php esc_html_e( 'new + assigned + work + wait', 'ig-enna' ); ?></div>
				</div>
				<?php foreach ( IG_Enna_Tickets::statuses() as $k => $label ) : ?>
					<div class="ig-enna-card">
						<div class="ig-enna-card-label"><?php echo esc_html( $label ); ?></div>
						<div class="ig-enna-card-num"><?php echo (int) ( $tk_by_status[ $k ] ?? 0 ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>

			<h2><?php esc_html_e( 'Utenti & Newsletter', 'ig-enna' ); ?></h2>
			<div class="ig-enna-grid">
				<div class="ig-enna-card">
					<div class="ig-enna-card-label"><?php esc_html_e( 'Utenti registrati', 'ig-enna' ); ?></div>
					<div class="ig-enna-card-num"><?php echo (int) $users_total['total_users']; ?></div>
					<div class="ig-enna-card-hint">subscribers: <?php echo (int) $subs_count; ?></div>
				</div>
				<div class="ig-enna-card">
					<div class="ig-enna-card-label"><?php esc_html_e( 'Iscritti newsletter', 'ig-enna' ); ?></div>
					<div class="ig-enna-card-num"><?php echo (int) $nl_total; ?></div>
					<div class="ig-enna-card-hint">confermati: <?php echo (int) $nl_confirmed; ?></div>
				</div>
			</div>

			<h2><?php esc_html_e( 'Top schede salvate', 'ig-enna' ); ?></h2>
			<?php if ( $top_saved ) : ?>
				<table class="wp-list-table widefat fixed striped" style="max-width:760px;">
					<thead><tr>
						<th><?php esc_html_e( 'Scheda', 'ig-enna' ); ?></th>
						<th style="width:120px;"><?php esc_html_e( 'Salvataggi', 'ig-enna' ); ?></th>
					</tr></thead>
					<tbody>
						<?php foreach ( $top_saved as $row ) :
							$p = get_post( (int) $row['object_id'] );
							if ( ! $p ) { continue; }
							$edit = get_edit_post_link( $p->ID );
						?>
							<tr>
								<td><a href="<?php echo esc_url( $edit ); ?>"><?php echo esc_html( $p->post_title ); ?></a></td>
								<td><span class="ig-enna-num"><?php echo (int) $row['n']; ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'Nessun salvataggio registrato.', 'ig-enna' ); ?></p>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Prossimi eventi', 'ig-enna' ); ?></h2>
			<?php if ( $upcoming ) : ?>
				<ul style="margin:0;">
					<?php foreach ( $upcoming as $ev ) :
						$d = get_post_meta( $ev->ID, '_ig_enna_event_date', true );
						$t = get_post_meta( $ev->ID, '_ig_enna_event_time', true );
						$ts = $d ? strtotime( $d . ' ' . ( $t ?: '00:00' ) ) : 0;
					?>
						<li>
							<a href="<?php echo esc_url( get_edit_post_link( $ev->ID ) ); ?>"><?php echo esc_html( $ev->post_title ); ?></a>
							<?php if ( $ts ) : ?> · <?php echo esc_html( date_i18n( 'd M Y H:i', $ts ) ); ?><?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="description"><?php esc_html_e( 'Nessun evento in programma.', 'ig-enna' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
