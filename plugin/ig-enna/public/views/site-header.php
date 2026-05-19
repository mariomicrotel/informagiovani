<?php
/**
 * Header pubblico del sito (topbar + sitenav).
 * Variabili: $org, $phone, $url_opp, $url_eventi, $url_area,
 *            $url_colloquio, $url_newsletter, $logged, $me.
 */
defined( 'ABSPATH' ) || exit;
?>
<!-- IG Enna Site Header -->
<div class="ig-enna-site-header" id="ig-enna-site-header">

	<!-- Topbar utility -->
	<div class="ig-enna-topbar">
		<div class="ig-enna-topbar__inner">
			<span class="ig-enna-topbar__left">
				<span><?php echo esc_html( $org ); ?></span>
				<span class="ig-enna-topbar__sep" aria-hidden="true">·</span>
				<span><?php esc_html_e( 'Servizio per i giovani', 'ig-enna' ); ?></span>
			</span>
			<div class="ig-enna-topbar__right">
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="ig-enna-topbar__link">
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.57 12 19.79 19.79 0 0 1 1.5 3.38 2 2 0 0 1 3.46 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.37a16 16 0 0 0 6 6l.8-.8a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.5 16a2 2 0 0 1 .5.92z"></path></svg>
						<?php echo esc_html( $phone ); ?>
					</a>
				<?php endif; ?>
				<span class="ig-enna-topbar__sep" aria-hidden="true">·</span>
				<a href="#contenuto" class="ig-enna-topbar__link"><?php esc_html_e( 'Salta al contenuto', 'ig-enna' ); ?></a>
				<span class="ig-enna-topbar__sep" aria-hidden="true">·</span>
				<a href="#" class="ig-enna-topbar__link ig-enna-topbar__a11y"><?php esc_html_e( 'Accessibilità', 'ig-enna' ); ?></a>
			</div>
		</div>
	</div>

	<!-- Sitenav principale -->
	<nav class="ig-enna-sitenav" id="ig-enna-sitenav" role="navigation" aria-label="<?php esc_attr_e( 'Navigazione principale', 'ig-enna' ); ?>">
		<div class="ig-enna-sitenav__inner">

			<!-- Brand -->
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ig-enna-sitenav__brand" aria-label="<?php esc_attr_e( 'Informagiovani Enna — home', 'ig-enna' ); ?>">
				<img src="<?php echo esc_url( IG_ENNA_URL . 'assets/images/logo.png' ); ?>" alt="<?php esc_attr_e( 'Informagiovani Enna', 'ig-enna' ); ?>" class="ig-enna-sitenav__logo-img" width="220" height="76" />
			</a>

			<!-- Nav links (desktop) -->
			<ul class="ig-enna-sitenav__nav" role="list">
				<?php
				$url_lavoro      = $url_opp ? add_query_arg( 'ig_area', 'lavoro',     $url_opp ) : '';
				$url_formazione  = $url_opp ? add_query_arg( 'ig_area', 'formazione', $url_opp ) : '';
				$url_impresa     = $url_opp ? add_query_arg( 'ig_area', 'impresa',    $url_opp ) : '';
				$url_estero      = $url_opp ? add_query_arg( 'ig_area', 'estero',     $url_opp ) : '';

				$nav_items = [
					[ 'url' => $url_opp,       'label' => __( 'Opportunità',    'ig-enna' ) ],
					[ 'url' => $url_lavoro,    'label' => __( 'Lavoro',         'ig-enna' ) ],
					[ 'url' => $url_formazione,'label' => __( 'Formazione',     'ig-enna' ) ],
					[ 'url' => $url_impresa,   'label' => __( 'Fare impresa',   'ig-enna' ) ],
					[ 'url' => $url_estero,    'label' => __( 'Estero',         'ig-enna' ) ],
					[ 'url' => $url_eventi,    'label' => __( 'Eventi',         'ig-enna' ) ],
				];
				foreach ( $nav_items as $item ) :
					if ( ! $item['url'] ) continue;
					$current = ( remove_query_arg( [], home_url( add_query_arg( [] ) ) ) === $item['url'] );
					?>
					<li role="listitem">
						<a href="<?php echo esc_url( $item['url'] ); ?>"
						   class="ig-enna-sitenav__link<?php echo $current ? ' ig-enna-sitenav__link--active' : ''; ?>"
						   <?php echo $current ? 'aria-current="page"' : ''; ?>>
							<?php echo esc_html( $item['label'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
				<?php if ( $url_colloquio ) : ?>
					<li role="listitem">
						<a href="<?php echo esc_url( $url_colloquio ); ?>" class="ig-enna-sitenav__link ig-enna-sitenav__link--cta">
							<?php esc_html_e( 'Prenota colloquio', 'ig-enna' ); ?>
						</a>
					</li>
				<?php endif; ?>
			</ul>

			<!-- Actions -->
			<div class="ig-enna-sitenav__actions">
				<?php if ( $url_area ) : ?>
					<a href="<?php echo esc_url( $url_area ); ?>" class="ig-enna-sitenav__area-link">
						<?php esc_html_e( 'Area personale', 'ig-enna' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $logged && $me ) : ?>
					<a href="<?php echo esc_url( $url_area ); ?>" class="ig-enna-btn ig-enna-btn--primary ig-enna-sitenav__login-btn">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
						<?php echo esc_html( $me->display_name ?: $me->user_login ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( wp_login_url( home_url( '/' ) ) ); ?>" class="ig-enna-btn ig-enna-btn--primary ig-enna-sitenav__login-btn">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
						<?php esc_html_e( 'Accedi', 'ig-enna' ); ?>
					</a>
				<?php endif; ?>

				<!-- Hamburger (mobile) -->
				<button class="ig-enna-sitenav__hamburger" aria-expanded="false" aria-controls="ig-enna-sitenav-mobile" aria-label="<?php esc_attr_e( 'Apri menu', 'ig-enna' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
				</button>
			</div>
		</div>

		<!-- Mobile drawer -->
		<div class="ig-enna-sitenav__mobile" id="ig-enna-sitenav-mobile" hidden>
			<ul role="list">
				<?php foreach ( $nav_items as $item ) :
					if ( ! $item['url'] ) continue; ?>
					<li><a href="<?php echo esc_url( $item['url'] ); ?>" class="ig-enna-sitenav__mobile-link"><?php echo esc_html( $item['label'] ); ?></a></li>
				<?php endforeach; ?>
				<?php if ( $url_colloquio ) : ?>
					<li><a href="<?php echo esc_url( $url_colloquio ); ?>" class="ig-enna-sitenav__mobile-link"><?php esc_html_e( 'Prenota colloquio', 'ig-enna' ); ?></a></li>
				<?php endif; ?>
				<?php if ( $url_area ) : ?>
					<li><a href="<?php echo esc_url( $url_area ); ?>" class="ig-enna-sitenav__mobile-link"><?php esc_html_e( 'Area personale', 'ig-enna' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>
	</nav>

</div>
<!-- /IG Enna Site Header -->
