<?php
/**
 * Carica il textdomain.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_I18n {

	public static function load() {
		load_plugin_textdomain( 'ig-enna', false, dirname( IG_ENNA_BASENAME ) . '/languages' );
	}
}
