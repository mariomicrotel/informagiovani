<?php
/**
 * Card partial per una scheda nella lista.
 * Variabili attese: $post (impostato da setup_postdata).
 */
defined( 'ABSPATH' ) || exit;

$post_id   = get_the_ID();
$meta      = IG_Enna_Frontend::get_scheda_meta( $post_id );
$area_slug = IG_Enna_Frontend::area_slug( $post_id );
$area_lab  = IG_Enna_Frontend::area_label( $post_id );
$targets   = IG_Enna_Frontend::targets( $post_id );
$territ    = IG_Enna_Frontend::territorio( $post_id );
$src_class = $meta['source_class'];
$urg       = $meta['urgency'];
$days_left = $meta['days_left'];

$src_labels = IG_Enna_Scheda_Meta::source_classes();
?>
<article class="ig-enna-card ig-enna-card--scheda">
	<div class="ig-enna-card__body">
		<div class="ig-enna-card__badges">
			<?php if ( $area_slug ) : ?>
				<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area_slug ); ?>">
					<?php echo esc_html( $area_lab ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $meta['tipo'] ) : ?>
				<span class="ig-enna-badge ig-enna-badge--type"><?php echo esc_html( $meta['tipo'] ); ?></span>
			<?php endif; ?>
			<?php if ( $src_class && isset( $src_labels[ $src_class ] ) ) : ?>
				<span class="ig-enna-badge ig-enna-badge--src-<?php echo esc_attr( $src_class ); ?>">
					<?php echo esc_html( $src_labels[ $src_class ] ); ?>
				</span>
			<?php endif; ?>
		</div>

		<h3 class="ig-enna-card__title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h3>

		<?php if ( $meta['short'] ) : ?>
			<p class="ig-enna-card__excerpt"><?php echo esc_html( $meta['short'] ); ?></p>
		<?php else : ?>
			<p class="ig-enna-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
		<?php endif; ?>

		<ul class="ig-enna-card__meta">
			<?php if ( $targets ) : ?>
				<li>
					<span class="ig-enna-card__meta-label"><?php esc_html_e( 'Target', 'ig-enna' ); ?></span>
					<span class="ig-enna-card__meta-value"><?php echo esc_html( implode( ' · ', $targets ) ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( $territ ) : ?>
				<li>
					<span class="ig-enna-card__meta-label"><?php esc_html_e( 'Dove', 'ig-enna' ); ?></span>
					<span class="ig-enna-card__meta-value"><?php echo esc_html( $territ ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( $meta['source'] ) : ?>
				<li>
					<span class="ig-enna-card__meta-label"><?php esc_html_e( 'Fonte', 'ig-enna' ); ?></span>
					<span class="ig-enna-card__meta-value"><?php echo esc_html( $meta['source'] ); ?></span>
				</li>
			<?php endif; ?>
			<?php if ( $meta['codice'] ) : ?>
				<li>
					<span class="ig-enna-card__meta-label"><?php esc_html_e( 'Codice', 'ig-enna' ); ?></span>
					<code class="ig-enna-code"><?php echo esc_html( $meta['codice'] ); ?></code>
				</li>
			<?php endif; ?>
		</ul>
	</div>

	<aside class="ig-enna-card__side">
		<div class="ig-enna-card__deadline-label"><?php esc_html_e( 'Scadenza', 'ig-enna' ); ?></div>

		<?php if ( $days_left === null ) : ?>
			<div class="ig-enna-card__deadline ig-enna-card__deadline--open">
				<?php esc_html_e( 'Sempre aperta', 'ig-enna' ); ?>
			</div>
			<?php if ( $meta['deadline_label'] ) : ?>
				<div class="ig-enna-card__deadline-sub"><?php echo esc_html( $meta['deadline_label'] ); ?></div>
			<?php endif; ?>
		<?php else : ?>
			<div class="ig-enna-card__deadline ig-enna-card__deadline--<?php echo esc_attr( $urg ); ?>">
				<?php echo esc_html( IG_Enna_Frontend::format_days_left( $days_left ) ); ?>
			</div>
			<div class="ig-enna-card__deadline-sub">
				<?php
				if ( $meta['deadline_label'] ) {
					echo esc_html( $meta['deadline_label'] );
				} elseif ( $meta['deadline'] ) {
					echo esc_html( date_i18n( 'd F Y', strtotime( $meta['deadline'] ) ) );
				}
				?>
			</div>
		<?php endif; ?>

		<div class="ig-enna-card__actions">
			<a class="ig-enna-btn ig-enna-btn--primary" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Dettagli', 'ig-enna' ); ?>
			</a>
			<?php if ( is_user_logged_in() ) :
				$is_saved = IG_Enna_User_Saves::is_saved( get_current_user_id(), $post_id ); ?>
				<button type="button"
					class="ig-enna-btn ig-enna-btn--secondary ig-enna-btn--sm ig-enna-save-btn<?php echo $is_saved ? ' is-saved' : ''; ?>"
					data-ig-save="<?php echo (int) $post_id; ?>"
					aria-pressed="<?php echo $is_saved ? 'true' : 'false'; ?>">
					<span class="ig-enna-save-btn__label-default"><?php esc_html_e( 'Salva', 'ig-enna' ); ?></span>
					<span class="ig-enna-save-btn__label-saved"><?php esc_html_e( 'Salvata', 'ig-enna' ); ?></span>
				</button>
			<?php endif; ?>
		</div>
	</aside>
</article>
