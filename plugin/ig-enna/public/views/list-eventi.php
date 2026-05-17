<?php
/**
 * Lista eventi prossimi.
 * Variabili: $query (WP_Query), $atts
 */
defined( 'ABSPATH' ) || exit;

$areas        = get_terms( [ 'taxonomy' => 'ig_area', 'hide_empty' => false ] );
$current_area = isset( $_GET['ig_area'] ) ? sanitize_title( wp_unslash( $_GET['ig_area'] ) ) : '';
$base         = get_permalink();
?>
<div class="ig-enna ig ig-enna-list-wrap">
	<header class="ig-enna-list-header">
		<h1 class="ig-enna-list-header__title"><?php esc_html_e( 'Eventi in programma', 'ig-enna' ); ?></h1>
		<p class="ig-enna-list-header__sub">
			<?php
			printf(
				esc_html( _n( '%d evento', '%d eventi', (int) $query->found_posts, 'ig-enna' ) ),
				(int) $query->found_posts
			);
			?>
		</p>
	</header>

	<form class="ig-enna-filters" method="get" action="<?php echo esc_url( $base ); ?>">
		<select name="ig_area" class="ig-enna-select" onchange="this.form.submit()">
			<option value=""><?php esc_html_e( 'Tutte le aree', 'ig-enna' ); ?></option>
			<?php foreach ( $areas as $t ) : ?>
				<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $current_area, $t->slug ); ?>>
					<?php echo esc_html( $t->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="ig-enna-btn ig-enna-btn--primary ig-enna-btn--sm"><?php esc_html_e( 'Filtra', 'ig-enna' ); ?></button>
	</form>

	<?php if ( $query->have_posts() ) : ?>
		<div class="ig-enna-events">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$pid    = get_the_ID();
				$d      = get_post_meta( $pid, '_ig_enna_event_date', true );
				$t      = get_post_meta( $pid, '_ig_enna_event_time', true );
				$mode   = get_post_meta( $pid, '_ig_enna_event_mode', true );
				$place  = get_post_meta( $pid, '_ig_enna_event_place', true );
				$ev_url = get_post_meta( $pid, '_ig_enna_event_url', true );
				$cap    = get_post_meta( $pid, '_ig_enna_event_capacity', true );
				$status = get_post_meta( $pid, '_ig_enna_event_status', true );
				$area_s = IG_Enna_Frontend::area_slug( $pid );
				$area_l = IG_Enna_Frontend::area_label( $pid );
				$ts     = $d ? strtotime( $d ) : 0;
				$modes  = IG_Enna_Evento_Meta::modes();
				$statuses = IG_Enna_Evento_Meta::statuses();
			?>
				<article class="ig-enna-event">
					<div class="ig-enna-event__date">
						<div class="ig-enna-event__day"><?php echo $ts ? esc_html( date_i18n( 'd', $ts ) ) : '—'; ?></div>
						<div class="ig-enna-event__month"><?php echo $ts ? esc_html( strtoupper( date_i18n( 'M', $ts ) ) ) : ''; ?></div>
					</div>
					<div class="ig-enna-event__body">
						<div class="ig-enna-card__badges">
							<?php if ( $area_s ) : ?>
								<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area_s ); ?>"><?php echo esc_html( $area_l ); ?></span>
							<?php endif; ?>
							<?php if ( $mode && isset( $modes[ $mode ] ) ) : ?>
								<span class="ig-enna-badge ig-enna-badge--mode-<?php echo esc_attr( $mode ); ?>"><?php echo esc_html( $modes[ $mode ] ); ?></span>
							<?php endif; ?>
							<?php if ( $status && isset( $statuses[ $status ] ) ) : ?>
								<span class="ig-enna-badge ig-enna-badge--evstate-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $statuses[ $status ] ); ?></span>
							<?php endif; ?>
						</div>
						<h3 class="ig-enna-event__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<ul class="ig-enna-event__meta">
							<?php if ( $t ) : ?>
								<li><strong><?php esc_html_e( 'Ora', 'ig-enna' ); ?>:</strong> <?php echo esc_html( $t ); ?></li>
							<?php endif; ?>
							<?php if ( $place ) : ?>
								<li><strong><?php esc_html_e( 'Dove', 'ig-enna' ); ?>:</strong> <?php echo esc_html( $place ); ?></li>
							<?php elseif ( $ev_url ) : ?>
								<li><strong><?php esc_html_e( 'Link', 'ig-enna' ); ?>:</strong>
									<a href="<?php echo esc_url( $ev_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( wp_parse_url( $ev_url, PHP_URL_HOST ) ?: $ev_url ); ?></a>
								</li>
							<?php endif; ?>
							<?php if ( $cap !== '' && $cap !== null ) : ?>
								<li><strong><?php esc_html_e( 'Capienza', 'ig-enna' ); ?>:</strong> <?php echo (int) $cap; ?></li>
							<?php endif; ?>
						</ul>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<div class="ig-enna-empty">
			<h3><?php esc_html_e( 'Nessun evento in programma', 'ig-enna' ); ?></h3>
			<p><?php esc_html_e( 'Torna a trovarci: pubblichiamo continuamente nuovi eventi.', 'ig-enna' ); ?></p>
		</div>
	<?php endif; ?>
</div>
