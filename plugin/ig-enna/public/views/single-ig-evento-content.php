<?php
/**
 * Contenuto rendered su single ig_evento (sostituisce the_content).
 * Variabili: $ig_post (WP_Post), $ig_content (string).
 */
defined( 'ABSPATH' ) || exit;

$pid     = $ig_post->ID;
$d       = get_post_meta( $pid, '_ig_enna_event_date', true );
$t       = get_post_meta( $pid, '_ig_enna_event_time', true );
$mode    = get_post_meta( $pid, '_ig_enna_event_mode', true );
$place   = get_post_meta( $pid, '_ig_enna_event_place', true );
$ev_url  = get_post_meta( $pid, '_ig_enna_event_url', true );
$cap     = get_post_meta( $pid, '_ig_enna_event_capacity', true );
$target  = get_post_meta( $pid, '_ig_enna_event_target_label', true );
$status  = get_post_meta( $pid, '_ig_enna_event_status', true );
$area_s  = IG_Enna_Frontend::area_slug( $pid );
$area_l  = IG_Enna_Frontend::area_label( $pid );
$ts      = $d ? strtotime( ( $t ? $d . ' ' . $t : $d ) ) : 0;
$modes   = IG_Enna_Evento_Meta::modes();
$statuses= IG_Enna_Evento_Meta::statuses();

// URL della lista eventi (per link sui badge).
$ev_page  = get_page_by_path( 'lista-eventi' ) ?: get_page_by_path( 'eventi' );
$ev_base  = $ev_page ? get_permalink( $ev_page ) : home_url( '/eventi/' );
?>
<div class="ig-enna ig ig-enna-single ig-enna-single--evento">
	<div class="ig-enna-single__head">
		<h1 class="ig-enna-single__title"><?php echo esc_html( get_the_title( $ig_post ) ); ?></h1>
		<div class="ig-enna-single__badges">
			<?php if ( $area_s ) : ?>
				<a class="ig-enna-badge ig-enna-badge--link ig-enna-badge--area-<?php echo esc_attr( $area_s ); ?>"
					href="<?php echo esc_url( add_query_arg( 'ig_area', $area_s, $ev_base ) ); ?>"
					rel="tag"
					aria-label="<?php echo esc_attr( sprintf( __( 'Filtra eventi per area: %s', 'ig-enna' ), $area_l ) ); ?>">
					<?php echo esc_html( $area_l ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $mode && isset( $modes[ $mode ] ) ) : ?>
				<a class="ig-enna-badge ig-enna-badge--link ig-enna-badge--mode-<?php echo esc_attr( $mode ); ?>"
					href="<?php echo esc_url( add_query_arg( 'ig_mode', $mode, $ev_base ) ); ?>"
					rel="tag"
					aria-label="<?php echo esc_attr( sprintf( __( 'Filtra eventi per modalità: %s', 'ig-enna' ), $modes[ $mode ] ) ); ?>">
					<?php echo esc_html( $modes[ $mode ] ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $status && isset( $statuses[ $status ] ) ) : ?>
				<span class="ig-enna-badge ig-enna-badge--evstate-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $statuses[ $status ] ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $ts ) : ?>
			<p class="ig-enna-single__lead">
				<?php echo esc_html( date_i18n( 'l d F Y', $ts ) ); ?><?php if ( $t ) : ?> · <?php echo esc_html( $t ); ?><?php endif; ?>
			</p>
		<?php endif; ?>
	</div>

	<div class="ig-enna-single__layout">
		<main class="ig-enna-single__main">
			<?php if ( trim( wp_strip_all_tags( $ig_content ) ) !== '' ) : ?>
				<section class="ig-enna-single__section">
					<h2><?php esc_html_e( 'Descrizione', 'ig-enna' ); ?></h2>
					<div class="ig-enna-prose"><?php echo $ig_content; ?></div>
				</section>
			<?php endif; ?>
		</main>

		<aside class="ig-enna-single__aside">
			<div class="ig-enna-single__card-deadline">
				<div class="ig-enna-card__deadline-label"><?php esc_html_e( 'Informazioni', 'ig-enna' ); ?></div>
				<dl class="ig-enna-dl">
					<?php if ( $place ) : ?>
						<dt><?php esc_html_e( 'Luogo', 'ig-enna' ); ?></dt>
						<dd><?php echo esc_html( $place ); ?></dd>
					<?php endif; ?>
					<?php if ( $ev_url ) : ?>
						<dt><?php esc_html_e( 'Link', 'ig-enna' ); ?></dt>
						<dd><a href="<?php echo esc_url( $ev_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ev_url ); ?></a></dd>
					<?php endif; ?>
					<?php if ( $cap !== '' && $cap !== null ) : ?>
						<dt><?php esc_html_e( 'Capienza', 'ig-enna' ); ?></dt>
						<dd><?php echo (int) $cap; ?></dd>
					<?php endif; ?>
					<?php if ( $target ) : ?>
						<dt><?php esc_html_e( 'Target', 'ig-enna' ); ?></dt>
						<dd><?php echo esc_html( $target ); ?></dd>
					<?php endif; ?>
				</dl>
			</div>
		</aside>
	</div>
</div>
