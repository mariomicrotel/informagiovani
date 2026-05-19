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
					<?php esc_html_e( 'Servizio del Comune di Enna', 'ig-enna' ); ?>
				</div>
				<h1 class="ig-enna-hero__title">
					<?php
					echo wp_kses_post( __(
						'Il punto di accesso alle <em>opportunità</em> per i giovani di Enna',
						'ig-enna'
					) );
					?>
				</h1>
				<p class="ig-enna-hero__lead">
					<?php esc_html_e( 'Lavoro, formazione, impresa, estero, concorsi, eventi e servizi utili — verificati dagli operatori dello sportello, in un\'unica piattaforma.', 'ig-enna' ); ?>
				</p>

				<form class="ig-enna-hero__search" method="get" action="<?php echo esc_url( $url_opportunita ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ig-enna-hero__search-icon">
						<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
					</svg>
					<input type="search" name="ig_q"
						placeholder="<?php esc_attr_e( 'Cerca lavoro, corsi, concorsi, bonus, eventi…', 'ig-enna' ); ?>" />
					<button type="submit" class="ig-enna-btn ig-enna-btn--primary"><?php esc_html_e( 'Cerca', 'ig-enna' ); ?></button>
				</form>

				<div class="ig-enna-hero__suggestions">
					<span><?php esc_html_e( 'Ricerche popolari:', 'ig-enna' ); ?></span>
					<?php
					$pop = [
						__( 'Servizio civile', 'ig-enna' ),
						__( 'Erasmus+', 'ig-enna' ),
						__( 'Resto al Sud', 'ig-enna' ),
						__( 'Concorsi Enna', 'ig-enna' ),
						__( 'Bonus cultura', 'ig-enna' ),
					];
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
			$quickpaths = [
				[ 'lavoro',     '💼', __( 'Cerco lavoro', 'ig-enna' ),                 __( 'Offerte, CPI, tirocini retribuiti e accompagnamento alla candidatura.', 'ig-enna' ) ],
				[ 'formazione', '🎓', __( 'Voglio formarmi', 'ig-enna' ),              __( 'Corsi, borse di studio, ITS, dottorati e percorsi professionalizzanti.', 'ig-enna' ) ],
				[ 'impresa',    '💡', __( 'Voglio aprire un\'impresa', 'ig-enna' ),    __( 'Business plan, microcredito, bandi giovani e accompagnamento dedicato.', 'ig-enna' ) ],
				[ 'estero',     '🌍', __( 'Voglio andare all\'estero', 'ig-enna' ),    __( 'Erasmus+, Corpo Europeo di Solidarietà, work&travel e mobilità internazionale.', 'ig-enna' ) ],
				[ 'concorso',   '🏆', __( 'Cerco un concorso', 'ig-enna' ),            __( 'Concorsi di Comune, Regione, Stato ed enti partecipati a misura di NEET.', 'ig-enna' ) ],
				[ 'diritti',    '❤️', __( 'Ho bisogno di un servizio', 'ig-enna' ),    __( 'Salute, casa, diritti, supporto psicologico, residenza, ISEE e bonus.', 'ig-enna' ) ],
			];
			$idx = 0;
			foreach ( $quickpaths as $p ) :
				$idx++;
				$u = add_query_arg( 'ig_area', $p[0], $url_opportunita );
				?>
				<a class="ig-enna-quickpath ig-enna-quickpath--<?php echo esc_attr( $p[0] ); ?>" href="<?php echo esc_url( $u ); ?>">
					<div class="ig-enna-quickpath__head">
						<div class="ig-enna-quickpath__icon"><?php echo esc_html( $p[1] ); ?></div>
						<div class="ig-enna-quickpath__idx"><?php printf( '%02d', $idx ); ?></div>
					</div>
					<h3 class="ig-enna-quickpath__title"><?php echo esc_html( $p[2] ); ?></h3>
					<p class="ig-enna-quickpath__desc"><?php echo esc_html( $p[3] ); ?></p>
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
			<div class="ig-enna-eyebrow"><?php esc_html_e( 'In 4 passi', 'ig-enna' ); ?></div>
			<h2><?php
				echo wp_kses_post( __( 'Come funziona il <em>servizio</em>', 'ig-enna' ) );
			?></h2>
			<p><?php esc_html_e( 'Da un\'idea generica a un percorso costruito su misura con i tuoi operatori dello sportello. Tutto in un\'unica piattaforma, gratuita e pubblica.', 'ig-enna' ); ?></p>
			<?php if ( $url_area ) : ?>
				<a class="ig-enna-btn ig-enna-btn--primary" href="<?php echo esc_url( $url_area ); ?>"><?php esc_html_e( 'Crea il tuo profilo', 'ig-enna' ); ?></a>
			<?php endif; ?>
		</div>

		<ol class="ig-enna-howit__steps">
			<li>
				<span class="ig-enna-howit__num">01</span>
				<div>
					<h4><?php esc_html_e( 'Cerca un\'opportunità', 'ig-enna' ); ?></h4>
					<p><?php esc_html_e( 'Esplora schede informative su lavoro, corsi, concorsi e bandi filtrate per età, interessi e territorio.', 'ig-enna' ); ?></p>
				</div>
			</li>
			<li>
				<span class="ig-enna-howit__num">02</span>
				<div>
					<h4><?php esc_html_e( 'Salva ciò che ti interessa', 'ig-enna' ); ?></h4>
					<p><?php esc_html_e( 'Tieni traccia di scadenze, requisiti e documenti. Ricevi promemoria automatici prima della chiusura.', 'ig-enna' ); ?></p>
				</div>
			</li>
			<li>
				<span class="ig-enna-howit__num">03</span>
				<div>
					<h4><?php esc_html_e( 'Prenota un colloquio', 'ig-enna' ); ?></h4>
					<p><?php esc_html_e( 'Parla con un operatore in presenza, in videochiamata o al telefono. Lo sportello è gratuito.', 'ig-enna' ); ?></p>
				</div>
			</li>
			<li>
				<span class="ig-enna-howit__num">04</span>
				<div>
					<h4><?php esc_html_e( 'Costruisci il tuo percorso', 'ig-enna' ); ?></h4>
					<p><?php esc_html_e( 'Insieme agli operatori definisci obiettivi, azioni e tappe. Tieni tutto nella tua area personale.', 'ig-enna' ); ?></p>
				</div>
			</li>
		</ol>
	</section>

	<!-- ===================== CTA ORIENTAMENTO ===================== -->
	<section class="ig-enna-section">
		<div class="ig-enna-cta">
			<div class="ig-enna-cta__copy">
				<div class="ig-enna-eyebrow ig-enna-eyebrow--light"><?php esc_html_e( 'Orientamento personalizzato', 'ig-enna' ); ?></div>
				<h2><?php esc_html_e( 'Hai bisogno di un confronto?', 'ig-enna' ); ?></h2>
				<p><?php esc_html_e( 'Parla con uno dei nostri operatori. Insieme analizziamo la tua situazione, definiamo un percorso e ti accompagniamo nelle candidature.', 'ig-enna' ); ?></p>
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
				<div class="ig-enna-cta__modes-label"><?php esc_html_e( 'Modalità colloquio', 'ig-enna' ); ?></div>
				<ul>
					<li><strong>👤 <?php esc_html_e( 'In presenza', 'ig-enna' ); ?></strong><span><?php esc_html_e( 'Sportello in Piazza Garibaldi, 1', 'ig-enna' ); ?></span></li>
					<li><strong>🎥 <?php esc_html_e( 'Videochiamata', 'ig-enna' ); ?></strong><span><?php esc_html_e( 'Google Meet o Zoom · 30 min', 'ig-enna' ); ?></span></li>
					<li><strong>📞 <?php esc_html_e( 'Telefono', 'ig-enna' ); ?></strong><span><?php echo esc_html( $phone ); ?></span></li>
					<li><strong>✉️ <?php esc_html_e( 'Email', 'ig-enna' ); ?></strong><span><?php echo esc_html( $email ?: __( 'Risposta entro 48 ore', 'ig-enna' ) ); ?></span></li>
				</ul>
			</div>
		</div>
	</section>

</div>
