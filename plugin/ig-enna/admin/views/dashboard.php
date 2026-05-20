<?php
/**
 * Dashboard amministratore Informagiovani Enna.
 * Mostra KPI, attività recente e accessi rapidi a tutte le sezioni.
 */
defined( 'ABSPATH' ) || exit;

global $wpdb;

// ============ KPI numerici ============
$sch_counts = wp_count_posts( 'ig_scheda' );
$ev_counts  = wp_count_posts( 'ig_evento' );
$pa_counts  = wp_count_posts( 'ig_partner' );
$pe_counts  = wp_count_posts( 'ig_percorso' );
$ne_counts  = wp_count_posts( 'ig_news' );

$users_total = function_exists( 'count_users' ) ? count_users() : [ 'total_users' => 0 ];
$users_count = (int) ( $users_total['total_users'] ?? 0 );

// Newsletter confermati.
$nl_tbl = $wpdb->prefix . 'ig_enna_newsletter_subs';
$nl_conf = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$nl_tbl} WHERE confirmed = 1" );

// Ticket aperti.
$tk_by_status = IG_Enna_Tickets::count_by_status();
$tk_open = 0;
foreach ( [ 'new', 'assigned', 'work', 'wait' ] as $k ) {
	$tk_open += $tk_by_status[ $k ] ?? 0;
}
$tk_total = array_sum( $tk_by_status );

// Scadenze imminenti (schede con deadline ≤ 7 gg).
$today    = current_time( 'Y-m-d' );
$max_urg  = gmdate( 'Y-m-d', current_time( 'timestamp' ) + 7 * DAY_IN_SECONDS );
$urg_q    = new WP_Query( [
	'post_type'      => 'ig_scheda',
	'post_status'    => 'publish',
	'posts_per_page' => 5,
	'meta_key'       => '_ig_enna_deadline',
	'orderby'        => 'meta_value',
	'order'          => 'ASC',
	'meta_query'     => [ [ 'key' => '_ig_enna_deadline', 'value' => [ $today, $max_urg ], 'compare' => 'BETWEEN', 'type' => 'DATE' ] ],
	'no_found_rows'  => false,
] );
$urg_count = (int) $urg_q->found_posts;

// Eventi prossimi (data ≥ oggi).
$ev_q = new WP_Query( [
	'post_type'      => 'ig_evento',
	'post_status'    => 'publish',
	'posts_per_page' => 3,
	'meta_key'       => '_ig_enna_event_date',
	'orderby'        => 'meta_value',
	'order'          => 'ASC',
	'meta_query'     => [ [ 'key' => '_ig_enna_event_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' ] ],
	'no_found_rows'  => false,
] );
$ev_upcoming = (int) $ev_q->found_posts;

// Appuntamenti prossimi.
$ap_rows = IG_Enna_Appointments::query( [
	'limit'   => 5,
	'orderby' => 'slot_start ASC',
] );

// Ultimi ticket aperti.
$tk_recent = IG_Enna_Tickets::query( [ 'limit' => 5 ] );

// Stato sistema.
$gdpr_exporters = count( apply_filters( 'wp_privacy_personal_data_exporters', [] ) );
$gdpr_erasers   = count( apply_filters( 'wp_privacy_personal_data_erasers', [] ) );
$seed_state     = get_option( IG_Enna_Seed::OPTION_KEY );

// Quick action capability checks.
$can_view_reports = current_user_can( 'ig_enna_view_reports' );
$can_manage_tk    = current_user_can( 'ig_enna_manage_tickets' );
$can_manage_part  = current_user_can( 'ig_enna_manage_partners' );

// Messaggi seed.
$seeded_ok = ! empty( $_GET['ig_seeded'] );

// Helper KPI card.
$kpi_card = function ( $label, $num, $hint, $cta_url, $cta_text, $tone = 'primary' ) {
	?>
	<div class="ig-enna-kpi ig-enna-kpi--<?php echo esc_attr( $tone ); ?>">
		<div class="ig-enna-kpi__label"><?php echo esc_html( $label ); ?></div>
		<div class="ig-enna-kpi__num"><?php echo esc_html( (string) $num ); ?></div>
		<?php if ( $hint ) : ?>
			<div class="ig-enna-kpi__hint"><?php echo esc_html( $hint ); ?></div>
		<?php endif; ?>
		<?php if ( $cta_url && $cta_text ) : ?>
			<a class="ig-enna-kpi__link" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?> →</a>
		<?php endif; ?>
	</div>
	<?php
};

// Helper sezione (quick link).
$sect_card = function ( $icon, $title, $desc, $url, $count = null ) {
	?>
	<a class="ig-enna-sect" href="<?php echo esc_url( $url ); ?>">
		<div class="ig-enna-sect__icon"><span class="dashicons <?php echo esc_attr( $icon ); ?>"></span></div>
		<div class="ig-enna-sect__body">
			<div class="ig-enna-sect__head">
				<h3><?php echo esc_html( $title ); ?></h3>
				<?php if ( $count !== null ) : ?>
					<span class="ig-enna-sect__count"><?php echo esc_html( (string) $count ); ?></span>
				<?php endif; ?>
			</div>
			<p><?php echo esc_html( $desc ); ?></p>
		</div>
	</a>
	<?php
};
?>
<div class="wrap ig-enna-admin ig-enna-dashboard">

	<!-- ============ HEADER ============ -->
	<header class="ig-enna-dashboard__head">
		<div>
			<h1><?php esc_html_e( 'Informagiovani Enna · Dashboard', 'ig-enna' ); ?></h1>
			<p class="ig-enna-dashboard__sub">
				<?php
				/* translators: %s = nome utente */
				printf( esc_html__( 'Benvenuto, %s. Da qui controlli lo stato dello sportello e accedi a tutte le aree del plugin.', 'ig-enna' ), esc_html( wp_get_current_user()->display_name ?: wp_get_current_user()->user_login ) );
				?>
			</p>
		</div>
		<div class="ig-enna-dashboard__quick">
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ig_scheda' ) ); ?>">
				<span class="dashicons dashicons-plus-alt2" style="margin-top:3px;"></span>
				<?php esc_html_e( 'Nuova scheda', 'ig-enna' ); ?>
			</a>
			<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ig_evento' ) ); ?>">
				<span class="dashicons dashicons-calendar-alt" style="margin-top:3px;"></span>
				<?php esc_html_e( 'Nuovo evento', 'ig-enna' ); ?>
			</a>
			<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ig_news' ) ); ?>">
				<span class="dashicons dashicons-megaphone" style="margin-top:3px;"></span>
				<?php esc_html_e( 'Nuova news', 'ig-enna' ); ?>
			</a>
		</div>
	</header>

	<?php if ( $seeded_ok ) : ?>
		<div class="notice notice-success is-dismissible"><p>
			<?php
			$created_lines = [];
			foreach ( [ 'pages', 'schede', 'eventi', 'partner', 'percorsi', 'news' ] as $k ) {
				$n = isset( $_GET[ 'ig_n_' . $k ] ) ? (int) $_GET[ 'ig_n_' . $k ] : 0;
				$created_lines[] = sprintf( '%s: %d', $k, $n );
			}
			echo esc_html( __( 'Seed eseguito. ', 'ig-enna' ) . implode( ' · ', $created_lines ) );
			?>
		</p></div>
	<?php endif; ?>

	<!-- ============ KPI ============ -->
	<h2 class="ig-enna-dashboard__title"><?php esc_html_e( 'KPI principali', 'ig-enna' ); ?></h2>
	<div class="ig-enna-kpi-grid">
		<?php
		$kpi_card(
			__( 'Schede pubblicate', 'ig-enna' ),
			(int) ( $sch_counts->publish ?? 0 ),
			sprintf( __( 'bozze: %d', 'ig-enna' ), (int) ( $sch_counts->draft ?? 0 ) ),
			admin_url( 'edit.php?post_type=ig_scheda' ),
			__( 'Gestisci', 'ig-enna' ),
			'primary'
		);
		$kpi_card(
			__( 'Eventi prossimi', 'ig-enna' ),
			$ev_upcoming,
			sprintf( __( 'totali: %d', 'ig-enna' ), (int) ( $ev_counts->publish ?? 0 ) ),
			admin_url( 'edit.php?post_type=ig_evento' ),
			__( 'Gestisci', 'ig-enna' ),
			'teal'
		);
		$kpi_card(
			__( 'Scadenze ≤ 7gg', 'ig-enna' ),
			$urg_count,
			__( 'opportunità urgenti', 'ig-enna' ),
			admin_url( 'edit.php?post_type=ig_scheda' ),
			__( 'Vedi', 'ig-enna' ),
			$urg_count > 0 ? 'amber' : 'neutral'
		);
		$kpi_card(
			__( 'Ticket aperti', 'ig-enna' ),
			$tk_open,
			sprintf( __( 'totali: %d', 'ig-enna' ), (int) $tk_total ),
			admin_url( 'admin.php?page=ig-enna-tickets' ),
			__( 'Gestisci', 'ig-enna' ),
			$tk_open > 0 ? 'red' : 'neutral'
		);
		$kpi_card(
			__( 'Utenti registrati', 'ig-enna' ),
			$users_count,
			__( 'tutti i ruoli', 'ig-enna' ),
			admin_url( 'users.php' ),
			__( 'Apri', 'ig-enna' ),
			'violet'
		);
		$kpi_card(
			__( 'Iscritti newsletter', 'ig-enna' ),
			$nl_conf,
			__( 'confermati', 'ig-enna' ),
			admin_url( 'admin.php?page=ig-enna-newsletter' ),
			__( 'Apri', 'ig-enna' ),
			'green'
		);
		$kpi_card(
			__( 'Partner', 'ig-enna' ),
			(int) ( $pa_counts->publish ?? 0 ),
			__( 'enti della rete', 'ig-enna' ),
			admin_url( 'edit.php?post_type=ig_partner' ),
			__( 'Apri', 'ig-enna' ),
			'teal'
		);
		$kpi_card(
			__( 'News', 'ig-enna' ),
			(int) ( $ne_counts->publish ?? 0 ),
			sprintf( __( 'percorsi attivi: %d', 'ig-enna' ), (int) ( $pe_counts->publish ?? 0 ) ),
			admin_url( 'edit.php?post_type=ig_news' ),
			__( 'Apri', 'ig-enna' ),
			'primary'
		);
		?>
	</div>

	<!-- ============ ATTIVITÀ RECENTE ============ -->
	<div class="ig-enna-dashboard__row">

		<!-- Scadenze imminenti -->
		<section class="ig-enna-widget">
			<header class="ig-enna-widget__head">
				<h3>⏰ <?php esc_html_e( 'Scadenze imminenti', 'ig-enna' ); ?></h3>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ig_scheda' ) ); ?>"><?php esc_html_e( 'Tutte →', 'ig-enna' ); ?></a>
			</header>
			<?php if ( $urg_q->have_posts() ) : ?>
				<ul class="ig-enna-widget__list">
					<?php while ( $urg_q->have_posts() ) : $urg_q->the_post();
						$pid = get_the_ID();
						$d   = get_post_meta( $pid, '_ig_enna_deadline', true );
						$days = $d ? max( 0, (int) floor( ( strtotime( $d . ' 23:59:59' ) - current_time( 'timestamp' ) ) / DAY_IN_SECONDS ) ) : null;
					?>
						<li>
							<a href="<?php echo esc_url( get_edit_post_link( $pid ) ); ?>"><?php the_title(); ?></a>
							<?php if ( $days !== null ) : ?>
								<span class="ig-enna-widget__tag ig-enna-badge ig-enna-badge--urg-<?php echo $days <= 7 ? 'urgent' : ( $days <= 21 ? 'soon' : 'ok' ); ?>"><?php
									printf( esc_html( _n( '%d giorno', '%d giorni', $days, 'ig-enna' ) ), $days );
								?></span>
							<?php endif; ?>
						</li>
					<?php endwhile; wp_reset_postdata(); ?>
				</ul>
			<?php else : ?>
				<p class="ig-enna-widget__empty"><?php esc_html_e( 'Nessuna scadenza nei prossimi 7 giorni.', 'ig-enna' ); ?></p>
			<?php endif; ?>
		</section>

		<!-- Ultimi ticket -->
		<section class="ig-enna-widget">
			<header class="ig-enna-widget__head">
				<h3>📨 <?php esc_html_e( 'Ultimi ticket', 'ig-enna' ); ?></h3>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-tickets' ) ); ?>"><?php esc_html_e( 'Tutti →', 'ig-enna' ); ?></a>
			</header>
			<?php if ( ! empty( $tk_recent['rows'] ) ) : ?>
				<ul class="ig-enna-widget__list">
					<?php foreach ( $tk_recent['rows'] as $tk ) :
						$user = $tk['user_id'] ? get_user_by( 'id', (int) $tk['user_id'] ) : null;
						$ts   = strtotime( $tk['created_at'] );
					?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-tickets&ticket=' . (int) $tk['id'] ) ); ?>">
								R-<?php echo (int) $tk['id']; ?> · <?php echo esc_html( wp_trim_words( $tk['subject'], 8 ) ); ?>
							</a>
							<span class="ig-enna-widget__tag ig-enna-badge ig-enna-badge--tkstate-<?php echo esc_attr( $tk['status'] ); ?>">
								<?php echo esc_html( IG_Enna_Tickets::status_label( $tk['status'] ) ); ?>
							</span>
							<small><?php echo $user ? esc_html( $user->display_name ?: $user->user_login ) : esc_html__( 'ospite', 'ig-enna' ); ?> · <?php echo $ts ? esc_html( human_time_diff( $ts, current_time( 'timestamp' ) ) ) . ' ' . esc_html__( 'fa', 'ig-enna' ) : ''; ?></small>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="ig-enna-widget__empty"><?php esc_html_e( 'Nessun ticket aperto.', 'ig-enna' ); ?></p>
			<?php endif; ?>
		</section>

		<!-- Appuntamenti prossimi -->
		<section class="ig-enna-widget">
			<header class="ig-enna-widget__head">
				<h3>🗓 <?php esc_html_e( 'Appuntamenti prossimi', 'ig-enna' ); ?></h3>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-appointments' ) ); ?>"><?php esc_html_e( 'Tutti →', 'ig-enna' ); ?></a>
			</header>
			<?php if ( $ap_rows ) : ?>
				<ul class="ig-enna-widget__list">
					<?php foreach ( $ap_rows as $ap ) :
						$user = $ap['user_id'] ? get_user_by( 'id', (int) $ap['user_id'] ) : null;
						$ts   = strtotime( $ap['slot_start'] );
					?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=ig-enna-appointments' ) ); ?>">
								<?php echo $ts ? esc_html( date_i18n( 'd M · H:i', $ts ) ) : ''; ?> ·
								<?php echo $user ? esc_html( $user->display_name ?: $user->user_login ) : esc_html__( 'ospite', 'ig-enna' ); ?>
							</a>
							<span class="ig-enna-widget__tag ig-enna-badge ig-enna-badge--apstate-<?php echo esc_attr( $ap['status'] ); ?>">
								<?php echo esc_html( IG_Enna_Appointments::status_label( $ap['status'] ) ); ?>
							</span>
							<span class="ig-enna-widget__tag ig-enna-badge ig-enna-badge--mode-<?php echo esc_attr( $ap['mode'] ); ?>"><?php echo esc_html( $ap['mode'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="ig-enna-widget__empty"><?php esc_html_e( 'Nessun appuntamento in calendario.', 'ig-enna' ); ?></p>
			<?php endif; ?>
		</section>

	</div>

	<!-- ============ ACCESSI RAPIDI ============ -->
	<h2 class="ig-enna-dashboard__title"><?php esc_html_e( 'Aree del plugin', 'ig-enna' ); ?></h2>
	<div class="ig-enna-sect-grid">
		<?php
		// Contenuti.
		$sect_card( 'dashicons-portfolio',     __( 'Schede informative', 'ig-enna' ), __( 'Bandi, opportunità, programmi.', 'ig-enna' ),
			admin_url( 'edit.php?post_type=ig_scheda' ), (int) ( $sch_counts->publish ?? 0 ) );
		$sect_card( 'dashicons-calendar-alt',  __( 'Eventi', 'ig-enna' ), __( 'Workshop, webinar, open day.', 'ig-enna' ),
			admin_url( 'edit.php?post_type=ig_evento' ), (int) ( $ev_counts->publish ?? 0 ) );
		$sect_card( 'dashicons-groups',        __( 'Partner', 'ig-enna' ), __( 'Rete enti del territorio.', 'ig-enna' ),
			admin_url( 'edit.php?post_type=ig_partner' ), (int) ( $pa_counts->publish ?? 0 ) );
		$sect_card( 'dashicons-clipboard',     __( 'Percorsi Impresa', 'ig-enna' ), __( 'Business plan, microcredito, ecc.', 'ig-enna' ),
			admin_url( 'edit.php?post_type=ig_percorso' ), (int) ( $pe_counts->publish ?? 0 ) );
		$sect_card( 'dashicons-megaphone',     __( 'News', 'ig-enna' ), __( 'Articoli, comunicati, storie.', 'ig-enna' ),
			admin_url( 'edit.php?post_type=ig_news' ), (int) ( $ne_counts->publish ?? 0 ) );

		// Operatività.
		if ( $can_manage_tk ) :
			$sect_card( 'dashicons-tickets-alt', __( 'Ticket', 'ig-enna' ), __( 'Richieste utenti, SLA, assegnazioni.', 'ig-enna' ),
				admin_url( 'admin.php?page=ig-enna-tickets' ), $tk_open );
			$sect_card( 'dashicons-calendar', __( 'Appuntamenti', 'ig-enna' ), __( 'Slot sportello in presenza/online.', 'ig-enna' ),
				admin_url( 'admin.php?page=ig-enna-appointments' ), null );
			$sect_card( 'dashicons-format-chat', __( 'Colloqui', 'ig-enna' ), __( 'Esiti orientamento, next step.', 'ig-enna' ),
				admin_url( 'admin.php?page=ig-enna-colloqui' ), null );
		endif;

		// Engagement.
		if ( $can_view_reports ) :
			$sect_card( 'dashicons-email-alt',   __( 'Newsletter', 'ig-enna' ), __( 'Iscritti, doppio opt-in, export.', 'ig-enna' ),
				admin_url( 'admin.php?page=ig-enna-newsletter' ), $nl_conf );
			$sect_card( 'dashicons-chart-area',  __( 'Report', 'ig-enna' ), __( 'KPI completi, top schede, export.', 'ig-enna' ),
				admin_url( 'admin.php?page=ig-enna-report' ), null );
			$sect_card( 'dashicons-list-view',   __( 'Audit log', 'ig-enna' ), __( 'Tracciato operazioni utenti/operatori.', 'ig-enna' ),
				admin_url( 'admin.php?page=ig-enna-audit' ), null );
		endif;

		// Configurazione.
		$sect_card( 'dashicons-admin-home',    __( 'Home page', 'ig-enna' ), __( 'Testi hero, percorsi, CTA.', 'ig-enna' ),
			admin_url( 'admin.php?page=ig-enna-home' ), null );
		$sect_card( 'dashicons-admin-generic', __( 'Impostazioni', 'ig-enna' ), __( 'Topbar, telefono, indirizzo, SLA.', 'ig-enna' ),
			admin_url( 'admin.php?page=ig-enna-settings' ), null );
		?>
	</div>

	<!-- ============ STATO SISTEMA ============ -->
	<div class="ig-enna-dashboard__row ig-enna-dashboard__row--2">

		<section class="ig-enna-widget">
			<header class="ig-enna-widget__head">
				<h3>🩺 <?php esc_html_e( 'Stato sistema', 'ig-enna' ); ?></h3>
			</header>
			<dl class="ig-enna-status-dl">
				<dt><?php esc_html_e( 'Versione plugin', 'ig-enna' ); ?></dt>
				<dd><code><?php echo esc_html( IG_ENNA_VERSION ); ?></code></dd>
				<dt><?php esc_html_e( 'Versione DB', 'ig-enna' ); ?></dt>
				<dd><code><?php echo esc_html( (string) get_option( 'ig_enna_db_version', '0' ) ); ?></code></dd>
				<dt><?php esc_html_e( 'GDPR exporters', 'ig-enna' ); ?></dt>
				<dd><?php echo (int) $gdpr_exporters; ?> · <?php echo (int) $gdpr_erasers; ?> <?php esc_html_e( 'erasers', 'ig-enna' ); ?></dd>
				<dt><?php esc_html_e( 'WordPress', 'ig-enna' ); ?></dt>
				<dd><code><?php echo esc_html( get_bloginfo( 'version' ) ); ?></code></dd>
				<dt><?php esc_html_e( 'PHP', 'ig-enna' ); ?></dt>
				<dd><code><?php echo esc_html( PHP_VERSION ); ?></code></dd>
				<?php if ( $seed_state && isset( $seed_state['last_run'] ) ) : ?>
					<dt><?php esc_html_e( 'Ultimo seed', 'ig-enna' ); ?></dt>
					<dd><?php echo esc_html( $seed_state['last_run'] ); ?></dd>
				<?php endif; ?>
			</dl>
		</section>

		<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<section class="ig-enna-widget">
			<header class="ig-enna-widget__head">
				<h3>🌱 <?php esc_html_e( 'Seed dati demo', 'ig-enna' ); ?></h3>
			</header>
			<p class="ig-enna-widget__hint">
				<?php esc_html_e( 'Crea pagine pubbliche con testi introduttivi, 8 schede di esempio, eventi, partner, percorsi e news. Idempotente: salta gli elementi già presenti.', 'ig-enna' ); ?>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ig_enna_seed" />
				<?php wp_nonce_field( IG_Enna_Seed::NONCE, '_ig_nonce' ); ?>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Esegui seed demo', 'ig-enna' ); ?>
				</button>
			</form>
		</section>
		<?php endif; ?>

	</div>

</div>
