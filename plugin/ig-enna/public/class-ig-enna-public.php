<?php
/**
 * Entry-point per la parte pubblica del plugin.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Public {

	private static $home_page    = false;
	private static $booking_page = false;
	private static $plugin_page  = false;

	/** Shortcode che identificano una pagina plugin. */
	private const PLUGIN_SHORTCODES = [
		'ig_enna_home', 'ig_enna_prenota_colloquio', 'ig_enna_opportunita',
		'ig_enna_eventi', 'ig_enna_partner', 'ig_enna_newsletter', 'ig_enna_area_personale',
	];

	public static function init() {
		add_filter( 'body_class', [ __CLASS__, 'body_class' ] );
		// 'wp' fires after main query + after 'init' (shortcode registration).
		add_action( 'wp',          [ __CLASS__, 'detect_page_type' ] );
		add_action( 'wp_body_open', [ __CLASS__, 'inject_header' ], 5 );
		add_action( 'wp_footer',    [ __CLASS__, 'inject_footer' ], 5 );
	}

	/**
	 * Imposta flag statici dopo che la query è pronta e gli shortcode sono registrati.
	 */
	public static function detect_page_type() {
		$page = get_queried_object();
		if ( ! ( $page instanceof WP_Post ) ) {
			// CPT singles e archivi del plugin.
			if ( is_singular( [ 'ig_scheda', 'ig_evento' ] ) || is_post_type_archive( [ 'ig_scheda', 'ig_evento' ] ) ) {
				self::$plugin_page = true;
			}
			return;
		}
		self::$home_page    = has_shortcode( $page->post_content, 'ig_enna_home' );
		self::$booking_page = has_shortcode( $page->post_content, 'ig_enna_prenota_colloquio' );
		foreach ( self::PLUGIN_SHORTCODES as $sc ) {
			if ( has_shortcode( $page->post_content, $sc ) ) {
				self::$plugin_page = true;
				break;
			}
		}
	}

	/**
	 * Inietta topbar + sitenav personalizzati via wp_body_open.
	 * Sostituisce funzionalmente il header del tema TT25 (nascosto via CSS).
	 */
	public static function inject_header() {
		if ( ! self::$plugin_page ) {
			return;
		}
		IG_Enna_Assets::enqueue_public();

		$org   = ig_enna_get_setting( 'org_name', 'Informagiovani Enna' );
		$phone = '0935 40 04 00';
		$home  = home_url( '/' );

		$url_for = function ( $slugs, $fallback = '' ) {
			foreach ( (array) $slugs as $slug ) {
				$p = get_page_by_path( $slug );
				if ( $p ) { return get_permalink( $p ); }
			}
			return $fallback;
		};

		$url_opp       = $url_for( [ 'lista-opportunita', 'opportunita' ], home_url( '/opportunita/' ) );
		$url_eventi    = $url_for( [ 'lista-eventi', 'eventi' ],           home_url( '/eventi/' ) );
		$url_area      = $url_for( [ 'area-personale' ],                   wp_login_url( $home ) );
		$url_colloquio = $url_for( [ 'prenota-colloquio', 'colloquio' ],   '' );
		$url_newsletter= $url_for( [ 'iscriviti', 'newsletter' ],          '' );

		$logged = is_user_logged_in();
		$me     = $logged ? wp_get_current_user() : null;

		include IG_ENNA_DIR . 'public/views/site-header.php';
	}

	/**
	 * Inietta footer personalizzato prima che il tema stampi il suo.
	 */
	public static function inject_footer() {
		if ( ! self::$plugin_page ) {
			return;
		}

		$org   = ig_enna_get_setting( 'org_name', 'Informagiovani Enna' );
		$phone = '0935 40 04 00';
		$email = ig_enna_get_setting( 'contact_email', '' );
		$home  = home_url( '/' );

		$url_for = function ( $slugs, $fallback = '' ) {
			foreach ( (array) $slugs as $slug ) {
				$p = get_page_by_path( $slug );
				if ( $p ) { return get_permalink( $p ); }
			}
			return $fallback;
		};

		$url_opp        = $url_for( [ 'lista-opportunita', 'opportunita' ], home_url( '/opportunita/' ) );
		$url_eventi     = $url_for( [ 'lista-eventi', 'eventi' ],           home_url( '/eventi/' ) );
		$url_area       = $url_for( [ 'area-personale' ],                   wp_login_url( $home ) );
		$url_colloquio  = $url_for( [ 'prenota-colloquio', 'colloquio' ],   '' );
		$url_newsletter = $url_for( [ 'iscriviti', 'newsletter' ],          '' );

		include IG_ENNA_DIR . 'public/views/site-footer.php';
	}

	public static function body_class( $classes ) {
		if ( is_singular( [ 'ig_scheda', 'ig_evento', 'ig_partner' ] ) || is_post_type_archive( [ 'ig_scheda', 'ig_evento' ] ) ) {
			$classes[] = 'ig-enna-context';
		}
		if ( self::$plugin_page )  { $classes[] = 'ig-enna-plugin-page'; }
		if ( self::$home_page )    { $classes[] = 'ig-enna-home-page'; }
		if ( self::$booking_page ) { $classes[] = 'ig-enna-booking-page'; }
		return $classes;
	}
}
