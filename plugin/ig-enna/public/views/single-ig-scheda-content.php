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

// URL della lista opportunità (per link sui badge).
$opp_page = get_page_by_path( 'lista-opportunita' ) ?: get_page_by_path( 'opportunita' );
$opp_base = $opp_page ? get_permalink( $opp_page ) : home_url( '/opportunita/' );

// Tassonomia ig_target slug → label (per badge target).
$target_terms = get_the_terms( $post_id, 'ig_target' );
$target_terms = ( $target_terms && ! is_wp_error( $target_terms ) ) ? $target_terms : [];

// Territorio term.
$terr_terms = get_the_terms( $post_id, 'ig_territorio' );
$terr_term  = ( $terr_terms && ! is_wp_error( $terr_terms ) ) ? $terr_terms[0] : null;
?>
<div class="ig-enna ig ig-enna-single ig-enna-single--scheda">
	<div class="ig-enna-single__head">
		<h1 class="ig-enna-single__title"><?php echo esc_html( get_the_title( $ig_post ) ); ?></h1>
		<div class="ig-enna-single__badges">
			<?php if ( $area_slug ) : ?>
				<a class="ig-enna-badge ig-enna-badge--link ig-enna-badge--area-<?php echo esc_attr( $area_slug ); ?>"
					href="<?php echo esc_url( add_query_arg( 'ig_area', $area_slug, $opp_base ) ); ?>"
					rel="tag"
					aria-label="<?php echo esc_attr( sprintf( __( 'Filtra opportunità per area: %s', 'ig-enna' ), $area_lab ) ); ?>">
					<?php echo esc_html( $area_lab ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $meta['tipo'] ) : ?>
				<a class="ig-enna-badge ig-enna-badge--link ig-enna-badge--type"
					href="<?php echo esc_url( add_query_arg( 'ig_q', $meta['tipo'], $opp_base ) ); ?>"
					rel="tag"
					aria-label="<?php echo esc_attr( sprintf( __( 'Cerca opportunità di tipo: %s', 'ig-enna' ), $meta['tipo'] ) ); ?>">
					<?php echo esc_html( $meta['tipo'] ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $src_class && isset( $src_lab[ $src_class ] ) ) : ?>
				<a class="ig-enna-badge ig-enna-badge--link ig-enna-badge--src-<?php echo esc_attr( $src_class ); ?>"
					href="<?php echo esc_url( add_query_arg( 'ig_src', $src_class, $opp_base ) ); ?>"
					rel="tag"
					aria-label="<?php echo esc_attr( sprintf( __( 'Filtra opportunità per fonte: %s', 'ig-enna' ), $src_lab[ $src_class ] ) ); ?>">
					<?php echo esc_html( $src_lab[ $src_class ] ); ?>
				</a>
			<?php endif; ?>
			<?php foreach ( $target_terms as $tt ) : ?>
				<a class="ig-enna-badge ig-enna-badge--link ig-enna-badge--target"
					href="<?php echo esc_url( add_query_arg( 'ig_target', $tt->slug, $opp_base ) ); ?>"
					rel="tag"
					aria-label="<?php echo esc_attr( sprintf( __( 'Filtra opportunità per target: %s', 'ig-enna' ), $tt->name ) ); ?>">
					<?php echo esc_html( $tt->name ); ?>
				</a>
			<?php endforeach; ?>
			<?php if ( $terr_term ) : ?>
				<a class="ig-enna-badge ig-enna-badge--link ig-enna-badge--territ"
					href="<?php echo esc_url( add_query_arg( 'ig_territorio', $terr_term->slug, $opp_base ) ); ?>"
					rel="tag"
					aria-label="<?php echo esc_attr( sprintf( __( 'Filtra opportunità per territorio: %s', 'ig-enna' ), $terr_term->name ) ); ?>">
					<?php echo esc_html( $terr_term->name ); ?>
				</a>
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
