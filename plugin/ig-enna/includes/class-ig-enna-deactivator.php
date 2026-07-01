<?php
/**
 * Deactivation hook — non distrugge dati.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Deactivator {

	public static function deactivate() {
		flush_rewrite_rules();
		// Rimuovi cron reminder deadline schede: verra' rischedulato all'attivazione.
		if ( class_exists( 'IG_Enna_Scheda_Protocol' ) ) {
			wp_clear_scheduled_hook( IG_Enna_Scheda_Protocol::CRON_HOOK );
		} else {
			wp_clear_scheduled_hook( 'ig_enna_scheda_deadline_reminders' );
		}
	}
}
