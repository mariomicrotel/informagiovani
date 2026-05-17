<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap ig-enna-admin">
	<h1><?php esc_html_e( 'Informagiovani Enna · Impostazioni', 'ig-enna' ); ?></h1>
	<form action="options.php" method="post">
		<?php
		settings_fields( IG_Enna_Settings::GROUP );
		do_settings_sections( IG_Enna_Settings::PAGE_SLUG );
		submit_button();
		?>
	</form>
</div>
