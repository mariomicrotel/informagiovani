<?php
/**
 * Deactivation hook — non distrugge dati.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Deactivator {

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
