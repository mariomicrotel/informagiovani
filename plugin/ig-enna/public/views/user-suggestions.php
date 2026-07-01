<?php
/**
 * Tab "Per te" — suggerimenti personalizzati.
 * Variabile: $user (WP_User) — passata dall'area-personale.php.
 */
defined( 'ABSPATH' ) || exit;

$sugg = IG_Enna_Suggestions::for_user( $user->ID );

// URL utili.
$url_opp = get_page_by_path( 'lista-opportunita' ) ? get_permalink( get_page_by_path( 'lista-opportunita' ) ) : home_url( '/opportunita/' );
$url_ev  = get_page_by_path( 'lista-eventi' )      ? get_permalink( get_page_by_path( 'lista-eventi' ) )      : home_url( '/eventi/' );
$url_pro = get_permalink() . '?ig_tab=profilo';
?>
<section class="ig-enna-area__panel ig-enna-per-te">

	<header class="ig-enna-per-te__head">
		<h2>
			<?php
			/* translators: %s = nome utente */
			printf( esc_html__( 'Suggerimenti per te, %s', 'ig-enna' ), esc_html( $user->first_name ?: $user->display_name ) );
			?>
		</h2>
		<p class="ig-enna-per-te__sub">
			<?php
			if ( $sugg['has_profile'] ) {
				esc_html_e( 'Opportunità, eventi e news scelti in base al tuo profilo. Più completo è il tuo profilo, più mirati saranno i suggerimenti.', 'ig-enna' );
			} else {
				esc_html_e( 'Al momento vedi solo suggerimenti generici. Completa il tuo profilo per ricevere opportunità cucite su di te.', 'ig-enna' );
			}
			?>
		</p>
	</header>

	<!-- Banner profilo incompleto -->
	<?php if ( $sugg['profile_completion'] < 60 ) : ?>
		<div class="ig-enna-per-te__banner">
			<div>
				<strong>
					<?php
					/* translators: %d = percentuale */
					printf( esc_html__( 'Profilo al %d%% — aggiungi qualche dato per migliorare i suggerimenti', 'ig-enna' ), (int) $sugg['profile_completion'] );
					?>
				</strong>
				<p><?php esc_html_e( 'Le aree di interesse, l\'età e la città sono i dati che fanno più differenza.', 'ig-enna' ); ?></p>
			</div>
			<a class="ig-enna-btn ig-enna-btn--primary ig-enna-btn--sm" href="<?php echo esc_url( $url_pro ); ?>">
				<?php esc_html_e( 'Completa il profilo →', 'ig-enna' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<!-- Chip riepilogo criteri -->
	<?php if ( $sugg['interests'] || $sugg['age'] || $sugg['city'] ) : ?>
		<div class="ig-enna-per-te__criteria">
			<span class="ig-enna-per-te__criteria-label"><?php esc_html_e( 'Basato su:', 'ig-enna' ); ?></span>
			<?php foreach ( $sugg['interests'] as $t ) : ?>
				<span class="ig-enna-chip">🎯 <?php echo esc_html( $t ); ?></span>
			<?php endforeach; ?>
			<?php if ( $sugg['age'] ) : ?>
				<span class="ig-enna-chip">🎂 <?php echo (int) $sugg['age']; ?> <?php esc_html_e( 'anni', 'ig-enna' ); ?></span>
			<?php endif; ?>
			<?php if ( $sugg['city'] ) : ?>
				<span class="ig-enna-chip">📍 <?php echo esc_html( $sugg['city'] ); ?></span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<!-- =========== OPPORTUNITA' =========== -->
	<h3 class="ig-enna-per-te__section-title">💡 <?php esc_html_e( 'Opportunità per te', 'ig-enna' ); ?></h3>

	<?php if ( $sugg['schede'] ) : ?>
		<div class="ig-enna-per-te__list">
			<?php foreach ( $sugg['schede'] as $item ) :
				$post = $item['post'];
				$pid  = $post->ID;
				$meta = IG_Enna_Frontend::get_scheda_meta( $pid );
				$area_slug = IG_Enna_Frontend::area_slug( $pid );
				$area_lab  = IG_Enna_Frontend::area_label( $pid );
			?>
				<article class="ig-enna-per-te__card">
					<div class="ig-enna-per-te__card-badges">
						<?php if ( $area_slug ) : ?>
							<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area_slug ); ?>"><?php echo esc_html( $area_lab ); ?></span>
						<?php endif; ?>
						<?php if ( $meta['tipo'] ) : ?>
							<span class="ig-enna-badge ig-enna-badge--type"><?php echo esc_html( $meta['tipo'] ); ?></span>
						<?php endif; ?>
					</div>
					<h4 class="ig-enna-per-te__card-title">
						<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
					</h4>
					<?php if ( $meta['short'] ) : ?>
						<p class="ig-enna-per-te__card-lead"><?php echo esc_html( wp_trim_words( $meta['short'], 24 ) ); ?></p>
					<?php endif; ?>
					<?php if ( $item['reasons'] ) : ?>
						<div class="ig-enna-per-te__why">
							<span class="ig-enna-per-te__why-label"><?php esc_html_e( 'Perché per te:', 'ig-enna' ); ?></span>
							<?php foreach ( $item['reasons'] as $r ) : ?>
								<span class="ig-enna-per-te__why-tag"><?php echo esc_html( $r ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="ig-enna-empty">
			<h4><?php esc_html_e( 'Nessuna opportunità corrispondente al tuo profilo', 'ig-enna' ); ?></h4>
			<p>
				<?php esc_html_e( 'Prova ad aggiungere altre aree di interesse nel profilo, oppure esplora tutte le opportunità.', 'ig-enna' ); ?>
				<a href="<?php echo esc_url( $url_opp ); ?>"><?php esc_html_e( 'Vedi tutte →', 'ig-enna' ); ?></a>
			</p>
		</div>
	<?php endif; ?>

	<!-- =========== EVENTI =========== -->
	<?php if ( $sugg['eventi'] ) : ?>
		<h3 class="ig-enna-per-te__section-title">🗓 <?php esc_html_e( 'Eventi consigliati', 'ig-enna' ); ?></h3>
		<ul class="ig-enna-per-te__events">
			<?php foreach ( $sugg['eventi'] as $item ) :
				$post = $item['post'];
				$pid  = $post->ID;
				$d = get_post_meta( $pid, '_ig_enna_event_date', true );
				$t = get_post_meta( $pid, '_ig_enna_event_time', true );
				$place = get_post_meta( $pid, '_ig_enna_event_place', true );
				$ts = $d ? strtotime( ( $t ? $d . ' ' . $t : $d ) ) : 0;
			?>
				<li>
					<div class="ig-enna-per-te__event-date">
						<span class="ig-enna-per-te__event-day"><?php echo $ts ? esc_html( date_i18n( 'd', $ts ) ) : '—'; ?></span>
						<span class="ig-enna-per-te__event-mon"><?php echo $ts ? esc_html( strtoupper( date_i18n( 'M', $ts ) ) ) : ''; ?></span>
					</div>
					<div class="ig-enna-per-te__event-body">
						<a class="ig-enna-per-te__event-title" href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
						<div class="ig-enna-per-te__event-meta">
							<?php if ( $t ) : ?><?php echo esc_html( $t ); ?><?php endif; ?>
							<?php if ( $place ) : ?> · <?php echo esc_html( $place ); ?><?php endif; ?>
						</div>
						<?php if ( $item['reasons'] ) : ?>
							<div class="ig-enna-per-te__why-inline">
								<?php foreach ( $item['reasons'] as $r ) : ?>
									<span class="ig-enna-per-te__why-tag"><?php echo esc_html( $r ); ?></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<!-- =========== NEWS =========== -->
	<?php if ( $sugg['news'] ) : ?>
		<h3 class="ig-enna-per-te__section-title">📰 <?php esc_html_e( 'Approfondimenti per te', 'ig-enna' ); ?></h3>
		<ul class="ig-enna-per-te__news">
			<?php foreach ( $sugg['news'] as $item ) :
				$post = $item['post']; $pid = $post->ID;
				$terms = get_the_terms( $pid, 'ig_area' );
				$area = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
			?>
				<li>
					<?php if ( $area ) : ?>
						<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area->slug ); ?>"><?php echo esc_html( $area->name ); ?></span>
					<?php endif; ?>
					<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
					<small><?php echo esc_html( get_the_date( 'd M Y', $post ) ); ?></small>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<footer class="ig-enna-per-te__foot">
		<a class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm" href="<?php echo esc_url( $url_opp ); ?>">
			<?php esc_html_e( 'Vedi tutte le opportunità →', 'ig-enna' ); ?>
		</a>
	</footer>
</section>
