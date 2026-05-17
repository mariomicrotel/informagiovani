<?php
/**
 * Gestione enqueue asset (frontend on-demand, admin solo su pagine plugin).
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Assets {

	const HANDLE_TOKENS_CSS = 'ig-enna-tokens';
	const HANDLE_PUBLIC_CSS = 'ig-enna-public';
	const HANDLE_PUBLIC_JS  = 'ig-enna-public';
	const HANDLE_ADMIN_CSS  = 'ig-enna-admin';
	const HANDLE_ADMIN_JS   = 'ig-enna-admin';

	public static function init() {
		add_action( 'wp_enqueue_scripts',    [ __CLASS__, 'register_public' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin' ] );
	}

	/**
	 * Registra (senza enqueue) gli asset pubblici. L'enqueue avviene on-demand dagli shortcode.
	 */
	public static function register_public() {
		wp_register_style(
			self::HANDLE_TOKENS_CSS,
			IG_ENNA_URL . 'assets/css/tokens.css',
			[],
			IG_ENNA_VERSION
		);
		wp_register_style(
			self::HANDLE_PUBLIC_CSS,
			IG_ENNA_URL . 'assets/css/public.css',
			[ self::HANDLE_TOKENS_CSS ],
			IG_ENNA_VERSION
		);
		wp_register_script(
			self::HANDLE_PUBLIC_JS,
			IG_ENNA_URL . 'assets/js/public.js',
			[],
			IG_ENNA_VERSION,
			true
		);
	}

	/**
	 * Enqueue effettivo, chiamato dagli shortcode.
	 */
	public static function enqueue_public() {
		wp_enqueue_style( self::HANDLE_TOKENS_CSS );
		wp_enqueue_style( self::HANDLE_PUBLIC_CSS );
		wp_enqueue_script( self::HANDLE_PUBLIC_JS );

		$data = [
			'restUrl' => esc_url_raw( rest_url( 'ig-enna/v1/' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'i18n'    => [
				'loading' => __( 'Caricamento…', 'ig-enna' ),
				'error'   => __( 'Errore. Riprova.', 'ig-enna' ),
			],
		];

		// Tenta wp_add_inline_script (head/footer enqueue normale).
		wp_add_inline_script(
			self::HANDLE_PUBLIC_JS,
			'window.IG_ENNA = ' . wp_json_encode( $data ) . ';',
			'before'
		);

		// Fallback: emetti la config inline anche subito (gestisce il caso block-theme).
		if ( ! self::$config_printed ) {
			self::$config_printed = true;
			echo '<script id="ig-enna-config-js">window.IG_ENNA = ' . wp_json_encode( $data ) . ';</script>';
		}
	}

	private static $config_printed = false;

	/**
	 * Asset admin — solo su pagine del plugin.
	 */
	public static function enqueue_admin( $hook ) {
		$is_plugin_screen = false;

		if ( is_string( $hook ) && strpos( $hook, 'ig-enna' ) !== false ) {
			$is_plugin_screen = true;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && in_array( $screen->post_type, [ 'ig_scheda', 'ig_evento', 'ig_partner', 'ig_percorso' ], true ) ) {
			$is_plugin_screen = true;
		}

		if ( ! $is_plugin_screen ) {
			return;
		}

		wp_enqueue_style(
			self::HANDLE_TOKENS_CSS,
			IG_ENNA_URL . 'assets/css/tokens.css',
			[],
			IG_ENNA_VERSION
		);
		wp_enqueue_style(
			self::HANDLE_ADMIN_CSS,
			IG_ENNA_URL . 'assets/css/admin.css',
			[ self::HANDLE_TOKENS_CSS ],
			IG_ENNA_VERSION
		);
		wp_enqueue_script(
			self::HANDLE_ADMIN_JS,
			IG_ENNA_URL . 'assets/js/admin.js',
			[],
			IG_ENNA_VERSION,
			true
		);
	}
}
