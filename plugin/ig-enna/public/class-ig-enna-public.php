<?php
/**
 * Entry-point per la parte pubblica del plugin.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Public {

	public static function init() {
		// Placeholder per filtri/azioni pubbliche future (template_redirect, body_class, ecc.).
		add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
	}

	public static function body_class( $classes ) {
		if ( is_singular( [ 'ig_scheda', 'ig_evento', 'ig_partner' ] ) || is_post_type_archive( [ 'ig_scheda', 'ig_evento' ] ) ) {
			$classes[] = 'ig-enna-context';
		}
		return $classes;
	}
}
