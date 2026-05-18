<?php
/**
 * Entry-point per la parte pubblica del plugin.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Public {

	private static $home_page    = false;
	private static $booking_page = false;

	public static function init() {
		add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
		// 'wp' fires after main query + after 'init' (shortcode registration).
		add_action( 'wp', [ __CLASS__, 'detect_page_type' ] );
	}

	/**
	 * Imposta flag statici dopo che la query è pronta e gli shortcode sono registrati.
	 * Usati poi da body_class().
	 */
	public static function detect_page_type() {
		$page = get_queried_object();
		if ( ! ( $page instanceof WP_Post ) ) {
			return;
		}
		self::$home_page    = has_shortcode( $page->post_content, 'ig_enna_home' );
		self::$booking_page = has_shortcode( $page->post_content, 'ig_enna_prenota_colloquio' );
	}

	public static function body_class( $classes ) {
		if ( is_singular( [ 'ig_scheda', 'ig_evento', 'ig_partner' ] ) || is_post_type_archive( [ 'ig_scheda', 'ig_evento' ] ) ) {
			$classes[] = 'ig-enna-context';
		}
		if ( self::$home_page )    { $classes[] = 'ig-enna-home-page'; }
		if ( self::$booking_page ) { $classes[] = 'ig-enna-booking-page'; }
		return $classes;
	}
}
