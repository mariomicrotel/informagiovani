<?php
/**
 * Lista partner.
 * Variabili: $query (WP_Query), $atts.
 */
defined( 'ABSPATH' ) || exit;

$types = IG_Enna_Partner_Meta::types();
?>
<div class="ig-enna ig ig-enna-list-wrap">
	<header class="ig-enna-list-header">
		<h1 class="ig-enna-list-header__title"><?php esc_html_e( 'I nostri partner', 'ig-enna' ); ?></h1>
		<p class="ig-enna-list-header__sub">
			<?php
			printf(
				esc_html( _n( '%d partner', '%d partner', (int) $query->found_posts, 'ig-enna' ) ),
				(int) $query->found_posts
			);
			?>
		</p>
	</header>

	<?php if ( $query->have_posts() ) : ?>
		<div class="ig-enna-partner-grid">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$pid     = get_the_ID();
				$type    = get_post_meta( $pid, '_ig_enna_partner_type', true );
				$area    = get_post_meta( $pid, '_ig_enna_partner_area', true );
				$website = get_post_meta( $pid, '_ig_enna_partner_website', true );
				$email   = get_post_meta( $pid, '_ig_enna_partner_email', true );
				$phone   = get_post_meta( $pid, '_ig_enna_partner_phone', true );
				$address = get_post_meta( $pid, '_ig_enna_partner_address', true );
			?>
				<article class="ig-enna-partner-card">
					<header>
						<?php if ( $type && isset( $types[ $type ] ) ) : ?>
							<span class="ig-enna-badge ig-enna-badge--ptype-<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $types[ $type ] ); ?></span>
						<?php endif; ?>
						<h3 class="ig-enna-partner-card__title"><?php the_title(); ?></h3>
						<?php if ( $area ) : ?>
							<p class="ig-enna-partner-card__area"><?php echo esc_html( $area ); ?></p>
						<?php endif; ?>
					</header>

					<?php $excerpt = get_the_excerpt(); if ( $excerpt ) : ?>
						<p class="ig-enna-partner-card__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 35 ) ); ?></p>
					<?php endif; ?>

					<ul class="ig-enna-partner-card__contacts">
						<?php if ( $website ) : ?>
							<li>
								<span class="ig-enna-card__meta-label"><?php esc_html_e( 'Sito', 'ig-enna' ); ?></span>
								<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">
									<?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ?: $website ); ?>
								</a>
							</li>
						<?php endif; ?>
						<?php if ( $email ) : ?>
							<li>
								<span class="ig-enna-card__meta-label"><?php esc_html_e( 'Email', 'ig-enna' ); ?></span>
								<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
							</li>
						<?php endif; ?>
						<?php if ( $phone ) : ?>
							<li>
								<span class="ig-enna-card__meta-label"><?php esc_html_e( 'Telefono', 'ig-enna' ); ?></span>
								<?php echo esc_html( $phone ); ?>
							</li>
						<?php endif; ?>
						<?php if ( $address ) : ?>
							<li>
								<span class="ig-enna-card__meta-label"><?php esc_html_e( 'Sede', 'ig-enna' ); ?></span>
								<?php echo esc_html( $address ); ?>
							</li>
						<?php endif; ?>
					</ul>
				</article>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<div class="ig-enna-empty">
			<h3><?php esc_html_e( 'Nessun partner pubblicato', 'ig-enna' ); ?></h3>
			<p><?php esc_html_e( 'L\'elenco dei partner verrà aggiornato a breve.', 'ig-enna' ); ?></p>
		</div>
	<?php endif; ?>
</div>
