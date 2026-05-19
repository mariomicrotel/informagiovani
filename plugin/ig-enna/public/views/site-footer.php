<?php
/**
 * Footer pubblico del sito.
 * Variabili: $org, $phone, $email, $url_opp, $url_eventi,
 *            $url_area, $url_colloquio, $url_newsletter.
 */
defined( 'ABSPATH' ) || exit;

$year = gmdate( 'Y' );
?>
<footer class="ig-enna-footer" role="contentinfo">
	<div class="ig-enna-footer__inner">

		<!-- Colonna brand -->
		<div class="ig-enna-footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ig-enna-footer__logo-link" aria-label="<?php esc_attr_e( 'Informagiovani Enna — home', 'ig-enna' ); ?>">
				<img src="<?php echo esc_url( IG_ENNA_URL . 'assets/images/logo.png' ); ?>" alt="<?php esc_attr_e( 'Informagiovani Enna', 'ig-enna' ); ?>" class="ig-enna-footer__logo-img" width="200" height="68" />
			</a>
			<p class="ig-enna-footer__desc">
				<?php esc_html_e( 'Sportello orientamento del Comune di Enna per giovani tra i 15 e i 35 anni. Gratuito, verificato, sempre aggiornato.', 'ig-enna' ); ?>
			</p>
			<div class="ig-enna-footer__contacts">
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="ig-enna-footer__contact-link">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.57 12 19.79 19.79 0 0 1 1.5 3.38 2 2 0 0 1 3.46 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.37a16 16 0 0 0 6 6l.8-.8a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.5 16a2 2 0 0 1 .5.92z"/></svg>
						<?php echo esc_html( $phone ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $email ) : ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>" class="ig-enna-footer__contact-link">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
						<?php echo esc_html( $email ); ?>
					</a>
				<?php endif; ?>
				<span class="ig-enna-footer__contact-link">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
					<?php esc_html_e( 'Piazza Garibaldi, 1 · 94100 Enna', 'ig-enna' ); ?>
				</span>
			</div>
		</div>

		<!-- Colonna Servizi -->
		<div class="ig-enna-footer__col">
			<h3 class="ig-enna-footer__col-title"><?php esc_html_e( 'Servizi', 'ig-enna' ); ?></h3>
			<ul class="ig-enna-footer__links">
				<?php
				$url_lavoro     = $url_opp ? add_query_arg( 'ig_area', 'lavoro',     $url_opp ) : '';
				$url_formazione = $url_opp ? add_query_arg( 'ig_area', 'formazione', $url_opp ) : '';
				$url_impresa    = $url_opp ? add_query_arg( 'ig_area', 'impresa',    $url_opp ) : '';
				$url_estero     = $url_opp ? add_query_arg( 'ig_area', 'estero',     $url_opp ) : '';
				$url_concorsi   = $url_opp ? add_query_arg( 'ig_area', 'concorso',   $url_opp ) : '';

				$servizi = [
					[ $url_opp,       __( 'Tutte le opportunità', 'ig-enna' ) ],
					[ $url_lavoro,    __( 'Lavoro', 'ig-enna' ) ],
					[ $url_formazione,__( 'Formazione', 'ig-enna' ) ],
					[ $url_impresa,   __( 'Fare impresa', 'ig-enna' ) ],
					[ $url_estero,    __( 'Estero', 'ig-enna' ) ],
					[ $url_concorsi,  __( 'Concorsi', 'ig-enna' ) ],
					[ $url_eventi,    __( 'Eventi', 'ig-enna' ) ],
				];
				foreach ( $servizi as $s ) :
					if ( ! $s[0] ) continue; ?>
					<li><a href="<?php echo esc_url( $s[0] ); ?>"><?php echo esc_html( $s[1] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<!-- Colonna Sportello -->
		<div class="ig-enna-footer__col">
			<h3 class="ig-enna-footer__col-title"><?php esc_html_e( 'Sportello', 'ig-enna' ); ?></h3>
			<ul class="ig-enna-footer__links">
				<?php if ( $url_colloquio ) : ?>
					<li><a href="<?php echo esc_url( $url_colloquio ); ?>"><?php esc_html_e( 'Prenota un colloquio', 'ig-enna' ); ?></a></li>
				<?php endif; ?>
				<?php if ( $url_area ) : ?>
					<li><a href="<?php echo esc_url( $url_area ); ?>"><?php esc_html_e( 'Area personale', 'ig-enna' ); ?></a></li>
				<?php endif; ?>
				<?php if ( $url_newsletter ) : ?>
					<li><a href="<?php echo esc_url( $url_newsletter ); ?>"><?php esc_html_e( 'Newsletter', 'ig-enna' ); ?></a></li>
				<?php endif; ?>
				<li><a href="<?php echo esc_url( home_url( '/partner/' ) ); ?>"><?php esc_html_e( 'Partner', 'ig-enna' ); ?></a></li>
			</ul>
			<h3 class="ig-enna-footer__col-title" style="margin-top:20px"><?php esc_html_e( 'Orari', 'ig-enna' ); ?></h3>
			<p class="ig-enna-footer__hours">
				<?php esc_html_e( 'Lun–Ven', 'ig-enna' ); ?><br>
				9:00 – 13:00 / 15:00 – 17:30
			</p>
		</div>

		<!-- Colonna Comune -->
		<div class="ig-enna-footer__col">
			<h3 class="ig-enna-footer__col-title"><?php esc_html_e( 'Comune di Enna', 'ig-enna' ); ?></h3>
			<ul class="ig-enna-footer__links">
				<li><a href="https://www.comune.enna.it" target="_blank" rel="noopener"><?php esc_html_e( 'Sito istituzionale', 'ig-enna' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_privacy_policy_url() ?: home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'ig-enna' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Note legali', 'ig-enna' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Accessibilità', 'ig-enna' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Cookie policy', 'ig-enna' ); ?></a></li>
			</ul>
		</div>

	</div>

	<!-- Bottom bar -->
	<div class="ig-enna-footer__bottom">
		<div class="ig-enna-footer__bottom-inner">
			<span>
				&copy; <?php echo esc_html( $year ); ?>
				<?php echo esc_html( $org ); ?> —
				<?php esc_html_e( 'Comune di Enna', 'ig-enna' ); ?>
			</span>
			<span class="ig-enna-footer__bottom-right">
				<?php esc_html_e( 'Dati aggiornati dagli operatori dello sportello', 'ig-enna' ); ?>
			</span>
		</div>
	</div>
</footer>
