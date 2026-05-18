<?php
defined( 'ABSPATH' ) || exit;

$counts = [
	'schede'   => wp_count_posts( 'ig_scheda' )->publish ?? 0,
	'eventi'   => wp_count_posts( 'ig_evento' )->publish ?? 0,
	'partner'  => wp_count_posts( 'ig_partner' )->publish ?? 0,
	'news'     => wp_count_posts( 'ig_news' )->publish ?? 0,
];
$seed_state = get_option( IG_Enna_Seed::OPTION_KEY );
$seeded_ok  = ! empty( $_GET['ig_seeded'] );
?>
<div class="wrap ig-enna-admin">
	<h1><?php esc_html_e( 'Informagiovani Enna · Dashboard', 'ig-enna' ); ?></h1>

	<p class="ig-enna-lead"><?php esc_html_e( 'Benvenuto. Questa è la base del plugin: la struttura è installata correttamente, le fasi successive aggiungeranno funzionalità.', 'ig-enna' ); ?></p>

	<div class="ig-enna-grid">
		<div class="ig-enna-card">
			<div class="ig-enna-card-label"><?php esc_html_e( 'Schede pubblicate', 'ig-enna' ); ?></div>
			<div class="ig-enna-card-num"><?php echo esc_html( (string) $counts['schede'] ); ?></div>
		</div>
		<div class="ig-enna-card">
			<div class="ig-enna-card-label"><?php esc_html_e( 'Eventi pubblicati', 'ig-enna' ); ?></div>
			<div class="ig-enna-card-num"><?php echo esc_html( (string) $counts['eventi'] ); ?></div>
		</div>
		<div class="ig-enna-card">
			<div class="ig-enna-card-label"><?php esc_html_e( 'Partner', 'ig-enna' ); ?></div>
			<div class="ig-enna-card-num"><?php echo esc_html( (string) $counts['partner'] ); ?></div>
		</div>
		<div class="ig-enna-card">
			<div class="ig-enna-card-label"><?php esc_html_e( 'News', 'ig-enna' ); ?></div>
			<div class="ig-enna-card-num"><?php echo esc_html( (string) $counts['news'] ); ?></div>
		</div>
		<div class="ig-enna-card">
			<div class="ig-enna-card-label"><?php esc_html_e( 'Versione DB', 'ig-enna' ); ?></div>
			<div class="ig-enna-card-num"><?php echo esc_html( (string) get_option( 'ig_enna_db_version', '0' ) ); ?></div>
		</div>
	</div>

	<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<h2><?php esc_html_e( 'Seed dati demo', 'ig-enna' ); ?></h2>
	<?php if ( $seeded_ok ) : ?>
		<div class="notice notice-success"><p>
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
	<p class="description"><?php esc_html_e( 'Crea pagine pubbliche con testi introduttivi e shortcode, 8 schede informative (una per area), 4 eventi futuri, 4 partner, 2 percorsi impresa, 4 articoli news. Idempotente: salta gli elementi già presenti.', 'ig-enna' ); ?></p>
	<?php if ( $seed_state && isset( $seed_state['last_run'] ) ) : ?>
		<p class="description"><em><?php printf( esc_html__( 'Ultima esecuzione: %s', 'ig-enna' ), esc_html( $seed_state['last_run'] ) ); ?></em></p>
	<?php endif; ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="ig_enna_seed" />
		<?php wp_nonce_field( IG_Enna_Seed::NONCE, '_ig_nonce' ); ?>
		<p>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Esegui seed dati demo', 'ig-enna' ); ?></button>
		</p>
	</form>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Stato attuale (FASE 1)', 'ig-enna' ); ?></h2>
	<ul class="ig-enna-list">
		<li><?php esc_html_e( 'Struttura plugin installabile · OK', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'Ruoli e capability · OK', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'Tabelle custom · OK', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'CPT e tassonomie · OK', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'Menu admin · OK', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'Pagina impostazioni · OK', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'Shortcode placeholder · OK', 'ig-enna' ); ?></li>
	</ul>

	<h2><?php esc_html_e( 'Prossime fasi', 'ig-enna' ); ?></h2>
	<ol class="ig-enna-list">
		<li><?php esc_html_e( 'FASE 2 — Backend admin schede/eventi (CRUD + badge stato).', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'FASE 3 — Frontend pubblico (home, lista, dettaglio).', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'FASE 4 — Area personale utente.', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'FASE 5 — Ticket, appuntamenti, colloqui.', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'FASE 6 — Partner, percorsi impresa.', 'ig-enna' ); ?></li>
		<li><?php esc_html_e( 'FASE 7 — Report, newsletter, notifiche, export, audit.', 'ig-enna' ); ?></li>
	</ol>
</div>
