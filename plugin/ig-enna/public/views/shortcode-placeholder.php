<?php
defined( 'ABSPATH' ) || exit;
/** @var string $title */
/** @var string $body  */
?>
<div class="ig-enna ig">
	<div class="ig-enna-placeholder">
		<div class="ig-enna-placeholder__badge"><?php esc_html_e( 'Informagiovani Enna', 'ig-enna' ); ?></div>
		<h2 class="ig-enna-placeholder__title"><?php echo esc_html( $title ); ?></h2>
		<p class="ig-enna-placeholder__body"><?php echo esc_html( $body ); ?></p>
	</div>
</div>
