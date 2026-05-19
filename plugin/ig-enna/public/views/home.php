<?php
/**
 * Homepage Informagiovani Enna.
 * Variabili: $hero_opps (WP_Query), $deadlines (WP_Query), $events (WP_Query), $kpi (array).
 */
defined( 'ABSPATH' ) || exit;

$org    = ig_enna_get_setting( 'org_name', 'Informagiovani Enna' );
$phone  = '0935 40 04 00';
$email  = ig_enna_get_setting( 'contact_email', '' );
$home   = home_url( '/' );
$cms    = ig_enna_get_home(); // contenuti editabili da Informagiovani → Home page

// URL pagine convenzionali (ricercate via slug; fallback al primo permalink utile).
$url_for = function ( $slugs, $fallback = '' ) {
	foreach ( (array) $slugs as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p ) { return get_permalink( $p ); }
	}
	return $fallback;
};
$url_opportunita = $url_for( [ 'lista-opportunita', 'opportunita' ], home_url( '/opportunita/' ) );
$url_eventi      = $url_for( [ 'lista-eventi', 'eventi' ],            home_url( '/eventi/' ) );
$url_area        = $url_for( [ 'area-personale', 'area' ],            wp_login_url( $home ) );
$url_partner     = $url_for( [ 'partner' ],                            '' );
$url_newsletter  = $url_for( [ 'iscriviti', 'newsletter' ],            '' );
$url_colloquio   = $url_for( [ 'prenota-colloquio', 'colloquio' ],     '' );
?>
<div class="ig-enna ig ig-enna-home">

	<!-- ===================== HERO ===================== -->
	<section class="ig-enna-hero">
		<div class="ig-enna-hero__bg" aria-hidden="true">
			<svg viewBox="0 0 600 600" class="ig-enna-hero__rings">
				<circle cx="300" cy="300" r="280" stroke="white" stroke-width="1.2" fill="none"/>
				<circle cx="300" cy="300" r="200" stroke="white" stroke-width="1.2" fill="none"/>
				<circle cx="300" cy="300" r="120" stroke="white" stroke-width="1.2" fill="none"/>
				<circle cx="300" cy="300" r="60"  stroke="white" stroke-width="1.2" fill="none"/>
			</svg>
			<span class="ig-enna-hero__glow"></span>
		</div>

		<div class="ig-enna-hero__inner">
			<div class="ig-enna-hero__copy">
				<div class="ig-enna-hero__chip">
					<?php echo esc_html( $cms['hero']['chip'] ); ?>
				</div>
				<h1 class="ig-enna-hero__title">
					<?php echo wp_kses_post( $cms['hero']['title_html'] ); ?>
				</h1>
				<p class="ig-enna-hero__lead">
					<?php echo esc_html( $cms['hero']['lead'] ); ?>
				</p>

				<form class="ig-enna-hero__search" method="get" action="<?php echo esc_url( $url_opportunita ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ig-enna-hero__search-icon">
						<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
					</svg>
					<input type="search" name="ig_q"
						placeholder="<?php echo esc_attr( $cms['hero']['search_placeholder'] ); ?>" />
					<button type="submit" class="ig-enna-btn ig-enna-btn--primary"><?php esc_html_e( 'Cerca', 'ig-enna' ); ?></button>
				</form>

				<div class="ig-enna-hero__suggestions">
					<span><?php esc_html_e( 'Ricerche popolari:', 'ig-enna' ); ?></span>
					<?php
					$pop = (array) $cms['hero']['popular'];
					foreach ( $pop as $term ) :
						$u = add_query_arg( 'ig_q', urlencode( $term ), $url_opportunita ); ?>
						<a href="<?php echo esc_url( $u ); ?>"><?php echo esc_html( $term ); ?></a>
					<?php endforeach; ?>
				</div>

				<?php if ( $url_colloquio ) : ?>
					<div class="ig-enna-hero__cta">
						<a class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--ghost-light" href="<?php echo esc_url( $url_colloquio ); ?>">
							📅 <?php esc_html_e( 'Prenota un colloquio', 'ig-enna' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<aside class="ig-enna-hero__numbers" aria-label="<?php esc_attr_e( 'Lo sportello in numeri', 'ig-enna' ); ?>">
				<div class="ig-enna-hero__numbers-label"><?php esc_html_e( 'Lo sportello in numeri', 'ig-enna' ); ?></div>
				<div class="ig-enna-hero__numbers-grid">
					<div>
						<div class="ig-enna-hero__num"><?php echo (int) $kpi['opportunita_attive']; ?></div>
						<div class="ig-enna-hero__num-label"><?php esc_html_e( 'Opportunità attive', 'ig-enna' ); ?></div>
					</div>
					<div>
						<div class="ig-enna-hero__num"><?php echo (int) $kpi['giovani_registrati']; ?></div>
						<div class="ig-enna-hero__num-label"><?php esc_html_e( 'Giovani registrati', 'ig-enna' ); ?></div>
					</div>
					<div>
						<div class="ig-enna-hero__num"><?php echo (int) ( wp_count_posts( 'ig_evento' )->publish ?? 0 ); ?></div>
						<div class="ig-enna-hero__num-label"><?php esc_html_e( 'Eventi in calendario', 'ig-enna' ); ?></div>
					</div>
					<div>
						<div class="ig-enna-hero__num"><?php echo (int) $kpi['sla_ore']; ?>h</div>
						<div class="ig-enna-hero__num-label"><?php esc_html_e( 'Risposta ticket', 'ig-enna' ); ?></div>
					</div>
				</div>
				<div class="ig-enna-hero__open">
					<span class="ig-enna-dot"></span>
					<?php esc_html_e( 'Sportello aperto · Lun-Ven 9:00-13:00 / 15:00-17:30', 'ig-enna' ); ?>
				</div>
			</aside>
		</div>
	</section>

	<!-- ===================== PERCORSI RAPIDI ===================== -->
	<section class="ig-enna-section ig-enna-section--band ig-enna-section--band-blue">
		<div class="ig-enna-section__head">
			<div>
				<div class="ig-enna-eyebrow"><?php esc_html_e( 'Da dove vuoi partire?', 'ig-enna' ); ?></div>
				<h2><?php esc_html_e( 'Percorsi rapidi', 'ig-enna' ); ?></h2>
			</div>
			<?php if ( $url_opportunita ) : ?>
				<a class="ig-enna-section__link" href="<?php echo esc_url( $url_opportunita ); ?>">
					<?php esc_html_e( 'Esplora tutti i servizi →', 'ig-enna' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="ig-enna-quickpaths">
			<?php
			$idx = 0;
			foreach ( (array) $cms['quickpaths'] as $p ) :
				$idx++;
				$area  = isset( $p['area'] )  ? $p['area']  : '';
				$title = isset( $p['title'] ) ? $p['title'] : '';
				$desc  = isset( $p['desc'] )  ? $p['desc']  : '';
				$img   = ig_enna_quickpath_image( $p );
				$u     = $area ? add_query_arg( 'ig_area', $area, $url_opportunita ) : $url_opportunita;
				?>
				<a class="ig-enna-quickpath<?php echo $area ? ' ig-enna-quickpath--' . esc_attr( $area ) : ''; ?>"
					href="<?php echo esc_url( $u ); ?>"
					style="background-image: url('<?php echo esc_url( $img ); ?>');">
					<span class="ig-enna-quickpath__idx"><?php printf( '%02d', $idx ); ?></span>
					<h3 class="ig-enna-quickpath__title"><?php echo esc_html( $title ); ?></h3>
					<p class="ig-enna-quickpath__desc"><?php echo esc_html( $desc ); ?></p>
					<span class="ig-enna-quickpath__cta"><?php esc_html_e( 'Esplora →', 'ig-enna' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- ===================== OPPORTUNITÀ IN EVIDENZA ===================== -->
	<?php if ( $hero_opps->have_posts() ) : ?>
	<section class="ig-enna-section">
		<div class="ig-enna-section__head">
			<div>
				<div class="ig-enna-eyebrow"><?php esc_html_e( 'Aggiornate dagli operatori', 'ig-enna' ); ?></div>
				<h2><?php esc_html_e( 'Opportunità in evidenza', 'ig-enna' ); ?></h2>
			</div>
			<a class="ig-enna-section__link" href="<?php echo esc_url( $url_opportunita ); ?>">
				<?php esc_html_e( 'Vedi tutte →', 'ig-enna' ); ?>
			</a>
		</div>

		<div class="ig-enna-home-opps">
			<?php while ( $hero_opps->have_posts() ) : $hero_opps->the_post();
				$pid       = get_the_ID();
				$meta      = IG_Enna_Frontend::get_scheda_meta( $pid );
				$area_slug = IG_Enna_Frontend::area_slug( $pid );
				$area_lab  = IG_Enna_Frontend::area_label( $pid );
				$territ    = IG_Enna_Frontend::territorio( $pid );
				$urg       = $meta['urgency'];
				$days      = $meta['days_left'];
			?>
				<article class="ig-enna-mini-card">
					<div class="ig-enna-card__badges">
						<?php if ( $area_slug ) : ?>
							<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area_slug ); ?>"><?php echo esc_html( $area_lab ); ?></span>
						<?php endif; ?>
						<?php if ( $days === null ) : ?>
							<span class="ig-enna-badge ig-enna-badge--evstate-open"><?php esc_html_e( 'Sempre aperta', 'ig-enna' ); ?></span>
						<?php elseif ( $days >= 0 ) : ?>
							<span class="ig-enna-badge ig-enna-badge--urg-<?php echo esc_attr( $urg ); ?>"><?php
								echo esc_html( sprintf(
									/* translators: %s = days */
									_n( 'Scade tra %s giorno', 'Scade tra %s giorni', max( 0, (int) $days ), 'ig-enna' ),
									(int) $days
								) );
							?></span>
						<?php endif; ?>
					</div>
					<h3 class="ig-enna-mini-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<?php if ( $meta['short'] ) : ?>
						<p class="ig-enna-mini-card__excerpt"><?php echo esc_html( wp_trim_words( $meta['short'], 22 ) ); ?></p>
					<?php endif; ?>
					<ul class="ig-enna-mini-card__meta">
						<?php if ( $territ ) : ?><li>📍 <?php echo esc_html( $territ ); ?></li><?php endif; ?>
						<?php if ( $meta['source'] ) : ?><li>🛡 <?php echo esc_html( $meta['source'] ); ?></li><?php endif; ?>
					</ul>
					<a class="ig-enna-btn ig-enna-btn--primary ig-enna-btn--sm" href="<?php the_permalink(); ?>">
						<?php esc_html_e( 'Dettagli', 'ig-enna' ); ?>
					</a>
				</article>
			<?php endwhile; ?>
		</div>
	</section>
	<?php endif; ?>

	<!-- ===================== SCADENZE + EVENTI ===================== -->
	<?php if ( $deadlines->have_posts() || $events->have_posts() ) : ?>
	<section class="ig-enna-section ig-enna-section--split ig-enna-section--band ig-enna-section--band-warm">
		<?php if ( $deadlines->have_posts() ) : ?>
			<div class="ig-enna-split-card">
				<header class="ig-enna-split-card__head">
					<div class="ig-enna-split-card__icon ig-enna-split-card__icon--amber">⏰</div>
					<div>
						<h3><?php esc_html_e( 'Scadenze imminenti', 'ig-enna' ); ?></h3>
						<p><?php esc_html_e( 'Non perdere queste opportunità', 'ig-enna' ); ?></p>
					</div>
					<a href="<?php echo esc_url( add_query_arg( 'ig_urg', 'soon', $url_opportunita ) ); ?>" class="ig-enna-split-card__more">
						<?php esc_html_e( 'Tutte →', 'ig-enna' ); ?>
					</a>
				</header>
				<ul class="ig-enna-deadlines">
					<?php while ( $deadlines->have_posts() ) : $deadlines->the_post();
						$pid = get_the_ID();
						$meta = IG_Enna_Frontend::get_scheda_meta( $pid );
						$urg = $meta['urgency'];
						$days = $meta['days_left'];
						$area_slug = IG_Enna_Frontend::area_slug( $pid );
						$area_lab = IG_Enna_Frontend::area_label( $pid );
					?>
						<li>
							<div class="ig-enna-deadlines__count ig-enna-deadlines__count--<?php echo esc_attr( $urg ?: 'ok' ); ?>">
								<span class="ig-enna-deadlines__num"><?php echo (int) max( 0, $days ); ?></span>
								<span class="ig-enna-deadlines__unit"><?php esc_html_e( 'giorni', 'ig-enna' ); ?></span>
							</div>
							<div class="ig-enna-deadlines__body">
								<a class="ig-enna-deadlines__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<div class="ig-enna-deadlines__meta">
									<?php if ( $area_slug ) : ?>
										<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area_slug ); ?>"><?php echo esc_html( $area_lab ); ?></span>
									<?php endif; ?>
									<?php if ( $meta['deadline'] ) : ?>
										<span><?php
											/* translators: %s = formatted date */
											printf( esc_html__( 'Scade il %s', 'ig-enna' ), esc_html( date_i18n( 'd M Y', strtotime( $meta['deadline'] ) ) ) );
										?></span>
									<?php endif; ?>
								</div>
							</div>
						</li>
					<?php endwhile; ?>
				</ul>
			</div>
		<?php endif; ?>

		<?php if ( $events->have_posts() ) : ?>
			<div class="ig-enna-split-card">
				<header class="ig-enna-split-card__head">
					<div class="ig-enna-split-card__icon ig-enna-split-card__icon--teal">📅</div>
					<div>
						<h3><?php esc_html_e( 'Eventi prossimi', 'ig-enna' ); ?></h3>
						<p><?php esc_html_e( 'Sportello e partner del territorio', 'ig-enna' ); ?></p>
					</div>
					<a href="<?php echo esc_url( $url_eventi ); ?>" class="ig-enna-split-card__more">
						<?php esc_html_e( 'Calendario →', 'ig-enna' ); ?>
					</a>
				</header>
				<ul class="ig-enna-mini-events">
					<?php while ( $events->have_posts() ) : $events->the_post();
						$pid = get_the_ID();
						$d = get_post_meta( $pid, '_ig_enna_event_date', true );
						$t = get_post_meta( $pid, '_ig_enna_event_time', true );
						$place = get_post_meta( $pid, '_ig_enna_event_place', true );
						$cap   = get_post_meta( $pid, '_ig_enna_event_capacity', true );
						$status = get_post_meta( $pid, '_ig_enna_event_status', true ) ?: 'open';
						$ts = $d ? strtotime( $d ) : 0;
					?>
						<li>
							<div class="ig-enna-mini-events__date">
								<span class="ig-enna-mini-events__day"><?php echo $ts ? esc_html( date_i18n( 'd', $ts ) ) : '—'; ?></span>
								<span class="ig-enna-mini-events__month"><?php echo $ts ? esc_html( strtoupper( date_i18n( 'M', $ts ) ) ) : ''; ?></span>
							</div>
							<div class="ig-enna-mini-events__body">
								<a class="ig-enna-mini-events__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<div class="ig-enna-mini-events__meta">
									<?php if ( $place ) : ?>📍 <?php echo esc_html( $place ); ?><?php endif; ?>
									<?php if ( $t ) : ?> · <?php echo esc_html( $t ); ?><?php endif; ?>
								</div>
							</div>
							<a class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--sm" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Info', 'ig-enna' ); ?></a>
						</li>
					<?php endwhile; ?>
				</ul>
			</div>
		<?php endif; ?>
	</section>
	<?php endif; ?>

	<!-- ===================== COME FUNZIONA ===================== -->
	<section class="ig-enna-section ig-enna-howit ig-enna-section--band ig-enna-section--band-sky">
		<div class="ig-enna-howit__intro">
			<div class="ig-enna-eyebrow"><?php echo esc_html( $cms['howit_intro']['eyebrow'] ); ?></div>
			<h2><?php echo wp_kses_post( $cms['howit_intro']['title_html'] ); ?></h2>
			<p><?php echo esc_html( $cms['howit_intro']['lead'] ); ?></p>
			<?php if ( $url_area ) : ?>
				<a class="ig-enna-btn ig-enna-btn--primary" href="<?php echo esc_url( $url_area ); ?>"><?php echo esc_html( $cms['howit_intro']['cta_label'] ); ?></a>
			<?php endif; ?>
		</div>

		<ol class="ig-enna-howit__steps">
			<?php $sn = 0; foreach ( (array) $cms['howit'] as $step ) : $sn++; ?>
				<li>
					<span class="ig-enna-howit__num"><?php printf( '%02d', $sn ); ?></span>
					<div>
						<h4><?php echo esc_html( isset( $step['title'] ) ? $step['title'] : '' ); ?></h4>
						<p><?php echo esc_html( isset( $step['desc'] ) ? $step['desc'] : '' ); ?></p>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<!-- ===================== CTA ORIENTAMENTO ===================== -->
	<section class="ig-enna-section">
		<div class="ig-enna-cta">
			<div class="ig-enna-cta__copy">
				<div class="ig-enna-eyebrow ig-enna-eyebrow--light"><?php echo esc_html( $cms['cta']['eyebrow'] ); ?></div>
				<h2><?php echo esc_html( $cms['cta']['title'] ); ?></h2>
				<p><?php echo esc_html( $cms['cta']['lead'] ); ?></p>
				<div class="ig-enna-cta__actions">
					<?php if ( $url_colloquio ) : ?>
						<a class="ig-enna-btn ig-enna-btn--secondary" href="<?php echo esc_url( $url_colloquio ); ?>">
							📅 <?php esc_html_e( 'Prenota un colloquio', 'ig-enna' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $url_area ) : ?>
						<a class="ig-enna-btn ig-enna-btn--ghost-light" href="<?php echo esc_url( add_query_arg( 'ig_tab', 'richieste', $url_area ) ); ?>">
							💬 <?php esc_html_e( 'Invia una richiesta', 'ig-enna' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
			<div class="ig-enna-cta__modes">
				<div class="ig-enna-cta__modes-label"><?php echo esc_html( $cms['cta']['modes_label'] ); ?></div>
				<ul>
					<?php foreach ( (array) $cms['cta']['modes'] as $m ) :
						$mlabel  = isset( $m['label'] )  ? $m['label']  : '';
						$mdetail = isset( $m['detail'] ) ? $m['detail'] : '';
						$micon   = isset( $m['icon'] )   ? $m['icon']   : '';
					?>
						<li><strong><?php echo esc_html( $micon . ' ' . $mlabel ); ?></strong><span><?php echo esc_html( $mdetail ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>

</div>
