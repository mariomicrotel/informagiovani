<?php
defined( 'ABSPATH' ) || exit;

$counts = [
	'schede'   => wp_count_posts( 'ig_scheda' )->publish ?? 0,
	'eventi'   => wp_count_posts( 'ig_evento' )->publish ?? 0,
	'partner'  => wp_count_posts( 'ig_partner' )->publish ?? 0,
];
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
			<div class="ig-enna-card-label"><?php esc_html_e( 'Versione DB', 'ig-enna' ); ?></div>
			<div class="ig-enna-card-num"><?php echo esc_html( (string) get_option( 'ig_enna_db_version', '0' ) ); ?></div>
		</div>
	</div>

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
