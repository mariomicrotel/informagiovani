<?php
/**
 * Form iscrizione newsletter.
 */
defined( 'ABSPATH' ) || exit;

$areas = get_terms( [ 'taxonomy' => 'ig_area', 'hide_empty' => false ] );
$notices = IG_Enna_Auth::pop_notices();
?>
<div class="ig-enna ig ig-enna-newsletter">
	<?php foreach ( $notices as $n ) : ?>
		<div class="ig-enna-notice ig-enna-notice--<?php echo esc_attr( $n['type'] ); ?>"><?php echo esc_html( $n['msg'] ); ?></div>
	<?php endforeach; ?>

	<h2><?php esc_html_e( 'Iscriviti alla newsletter', 'ig-enna' ); ?></h2>
	<p class="ig-enna-newsletter__lead"><?php esc_html_e( 'Ricevi via email le nuove opportunità nelle aree che ti interessano.', 'ig-enna' ); ?></p>

	<form method="post" action="" class="ig-enna-form">
		<?php wp_nonce_field( IG_Enna_Newsletter::NONCE, '_ig_nonce' ); ?>
		<input type="hidden" name="ig_enna_action" value="newsletter_subscribe" />

		<label><span><?php esc_html_e( 'Email', 'ig-enna' ); ?></span>
			<input class="ig-enna-input" type="email" name="email" required />
		</label>

		<fieldset class="ig-enna-newsletter__interests">
			<legend><?php esc_html_e( 'Aree di interesse', 'ig-enna' ); ?></legend>
			<?php foreach ( $areas as $a ) : ?>
				<label class="ig-enna-checkbox">
					<input type="checkbox" name="interests[]" value="<?php echo esc_attr( $a->slug ); ?>" />
					<span><?php echo esc_html( $a->name ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>

		<button class="ig-enna-btn ig-enna-btn--primary" type="submit"><?php esc_html_e( 'Iscrivimi', 'ig-enna' ); ?></button>
	</form>
</div>
