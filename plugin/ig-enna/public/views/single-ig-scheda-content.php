<?php
/**
 * Contenuto rendered su single ig_scheda (sostituisce the_content).
 * Variabili: $ig_post (WP_Post), $ig_content (string, contenuto originale già filtrato).
 */
defined( 'ABSPATH' ) || exit;

$post_id   = $ig_post->ID;
$meta      = IG_Enna_Frontend::get_scheda_meta( $post_id );
$area_slug = IG_Enna_Frontend::area_slug( $post_id );
$area_lab  = IG_Enna_Frontend::area_label( $post_id );
$targets   = IG_Enna_Frontend::targets( $post_id );
$territ    = IG_Enna_Frontend::territorio( $post_id );
$src_class = $meta['source_class'];
$urg       = $meta['urgency'];
$days      = $meta['days_left'];
$src_lab   = IG_Enna_Scheda_Meta::source_classes();
?>
<div class="ig-enna ig ig-enna-single ig-enna-single--scheda">
	<div class="ig-enna-single__head">
		<div class="ig-enna-single__badges">
			<?php if ( $area_slug ) : ?>
				<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area_slug ); ?>"><?php echo esc_html( $area_lab ); ?></span>
			<?php endif; ?>
			<?php if ( $meta['tipo'] ) : ?>
				<span class="ig-enna-badge ig-enna-badge--type"><?php echo esc_html( $meta['tipo'] ); ?></span>
			<?php endif; ?>
			<?php if ( $src_class && isset( $src_lab[ $src_class ] ) ) : ?>
				<span class="ig-enna-badge ig-enna-badge--src-<?php echo esc_attr( $src_class ); ?>"><?php echo esc_html( $src_lab[ $src_class ] ); ?></span>
			<?php endif; ?>
			<?php if ( $meta['codice'] ) : ?>
				<code class="ig-enna-code"><?php echo esc_html( $meta['codice'] ); ?></code>
			<?php endif; ?>
		</div>

		<?php if ( $meta['short'] ) : ?>
			<p class="ig-enna-single__lead"><?php echo esc_html( $meta['short'] ); ?></p>
		<?php endif; ?>
	</div>

	<div class="ig-enna-single__layout">
		<main class="ig-enna-single__main">
			<?php if ( trim( wp_strip_all_tags( $ig_content ) ) !== '' ) : ?>
				<section class="ig-enna-single__section">
					<h2><?php esc_html_e( 'Descrizione', 'ig-enna' ); ?></h2>
					<div class="ig-enna-prose"><?php echo $ig_content; // already filtered ?></div>
				</section>
			<?php endif; ?>

			<?php if ( $meta['contributo'] || $meta['durata'] || $territ || $targets || $meta['tipo'] ) : ?>
			<section class="ig-enna-single__section">
				<h2><?php esc_html_e( 'In sintesi', 'ig-enna' ); ?></h2>
				<dl class="ig-enna-dl">
					<?php if ( $meta['contributo'] ) : ?>
						<dt><?php esc_html_e( 'Contributo', 'ig-enna' ); ?></dt>
						<dd><?php echo esc_html( $meta['contributo'] ); ?></dd>
					<?php endif; ?>
					<?php if ( $meta['durata'] ) : ?>
						<dt><?php esc_html_e( 'Durata', 'ig-enna' ); ?></dt>
						<dd><?php echo esc_html( $meta['durata'] ); ?></dd>
					<?php endif; ?>
					<?php if ( $targets ) : ?>
						<dt><?php esc_html_e( 'A chi è rivolta', 'ig-enna' ); ?></dt>
						<dd><?php echo esc_html( implode( ' · ', $targets ) ); ?></dd>
					<?php endif; ?>
					<?php if ( $territ ) : ?>
						<dt><?php esc_html_e( 'Territorio', 'ig-enna' ); ?></dt>
						<dd><?php echo esc_html( $territ ); ?></dd>
					<?php endif; ?>
					<?php if ( $meta['tipo'] ) : ?>
						<dt><?php esc_html_e( 'Tipo', 'ig-enna' ); ?></dt>
						<dd><?php echo esc_html( $meta['tipo'] ); ?></dd>
					<?php endif; ?>
				</dl>
			</section>
			<?php endif; ?>
		</main>

		<aside class="ig-enna-single__aside">
			<div class="ig-enna-single__card-deadline">
				<div class="ig-enna-card__deadline-label"><?php esc_html_e( 'Scadenza', 'ig-enna' ); ?></div>
				<?php if ( $days === null ) : ?>
					<div class="ig-enna-card__deadline ig-enna-card__deadline--open"><?php esc_html_e( 'Sempre aperta', 'ig-enna' ); ?></div>
					<?php if ( $meta['deadline_label'] ) : ?>
						<div class="ig-enna-card__deadline-sub"><?php echo esc_html( $meta['deadline_label'] ); ?></div>
					<?php endif; ?>
				<?php else : ?>
					<div class="ig-enna-card__deadline ig-enna-card__deadline--<?php echo esc_attr( $urg ); ?>">
						<?php echo esc_html( IG_Enna_Frontend::format_days_left( $days ) ); ?>
					</div>
					<div class="ig-enna-card__deadline-sub">
						<?php
						if ( $meta['deadline_label'] ) {
							echo esc_html( $meta['deadline_label'] );
						} else {
							echo esc_html( date_i18n( 'd F Y', strtotime( $meta['deadline'] ) ) );
						}
						?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $meta['source'] ) : ?>
				<div class="ig-enna-single__card-source">
					<div class="ig-enna-card__deadline-label"><?php esc_html_e( 'Fonte', 'ig-enna' ); ?></div>
					<div class="ig-enna-source-name"><?php echo esc_html( $meta['source'] ); ?></div>
					<?php if ( $meta['source_url'] ) : ?>
						<a class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--sm" href="<?php echo esc_url( $meta['source_url'] ); ?>" target="_blank" rel="noopener">
							<?php esc_html_e( 'Vai alla fonte', 'ig-enna' ); ?>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</aside>
	</div>
</div>
