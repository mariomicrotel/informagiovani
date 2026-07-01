<?php
/**
 * Registry shortcode pubblici.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Shortcodes {

	public static function register() {
		add_shortcode( 'ig_enna_opportunita',   [ __CLASS__, 'sc_opportunita' ] );
		add_shortcode( 'ig_enna_area_personale',[ __CLASS__, 'sc_area_personale' ] );
		add_shortcode( 'ig_enna_eventi',        [ __CLASS__, 'sc_eventi' ] );
		add_shortcode( 'ig_enna_partner',       [ __CLASS__, 'sc_partner' ] );
		add_shortcode( 'ig_enna_newsletter',    [ __CLASS__, 'sc_newsletter' ] );
		add_shortcode( 'ig_enna_home',          [ __CLASS__, 'sc_home' ] );
		add_shortcode( 'ig_enna_prenota_colloquio', [ __CLASS__, 'sc_booking' ] );
		add_shortcode( 'ig_enna_news',          [ __CLASS__, 'sc_news' ] );
		add_shortcode( 'ig_enna_iscrizione_evento', [ __CLASS__, 'sc_event_signup' ] );
	}

	public static function sc_news( $atts = [], $content = '' ) {
		return IG_Enna_Frontend::render_news_list( (array) $atts );
	}

	public static function sc_event_signup( $atts = [], $content = '' ) {
		$atts = shortcode_atts( [ 'event_id' => 0 ], $atts, 'ig_enna_iscrizione_evento' );
		IG_Enna_Assets::enqueue_public();
		$preselect_event_id = (int) $atts['event_id'];
		ob_start();
		include IG_ENNA_DIR . 'public/views/form-event-registration.php';
		return ob_get_clean();
	}

	public static function sc_home( $atts = [], $content = '' ) {
		return IG_Enna_Frontend::render_home( (array) $atts );
	}

	public static function sc_booking( $atts = [], $content = '' ) {
		return IG_Enna_Frontend::render_booking( (array) $atts );
	}

	public static function sc_partner( $atts = [], $content = '' ) {
		return IG_Enna_Frontend::render_partner_list( (array) $atts );
	}

	public static function sc_newsletter( $atts = [], $content = '' ) {
		IG_Enna_Assets::enqueue_public();
		ob_start();
		include IG_ENNA_DIR . 'public/views/form-newsletter.php';
		return ob_get_clean();
	}

	public static function sc_opportunita( $atts = [], $content = '' ) {
		return IG_Enna_Frontend::render_opportunita_list( (array) $atts );
	}

	public static function sc_area_personale( $atts = [], $content = '' ) {
		IG_Enna_Assets::enqueue_public();
		ob_start();
		include IG_ENNA_DIR . 'public/views/area-personale.php';
		return ob_get_clean();
	}

	public static function sc_eventi( $atts = [], $content = '' ) {
		return IG_Enna_Frontend::render_eventi_list( (array) $atts );
	}

	private static function render_placeholder( $title, $body ) {
		ob_start();
		include IG_ENNA_DIR . 'public/views/shortcode-placeholder.php';
		return ob_get_clean();
	}
}
