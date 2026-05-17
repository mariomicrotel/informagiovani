<?php
/**
 * Bootstrap del plugin — singleton, registra hook runtime.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// i18n.
		add_action( 'init', [ 'IG_Enna_I18n', 'load' ], 1 );

		// CPT + tassonomie ad ogni request.
		add_action( 'init', [ 'IG_Enna_CPT',        'register' ] );
		add_action( 'init', [ 'IG_Enna_Taxonomies', 'register' ] );

		// Migrazione DB se serve.
		add_action( 'plugins_loaded', [ 'IG_Enna_DB', 'maybe_upgrade' ], 5 );

		// Shortcode.
		add_action( 'init', [ 'IG_Enna_Shortcodes', 'register' ] );

		// Meta + admin list (FASE 2 / 6).
		IG_Enna_Scheda_Meta::init();
		IG_Enna_Evento_Meta::init();
		IG_Enna_Partner_Meta::init();
		IG_Enna_Percorso_Meta::init();
		if ( is_admin() ) {
			IG_Enna_Admin_List::init();
		}

		// Auth + REST (FASE 4).
		IG_Enna_Auth::init();
		IG_Enna_REST::init();

		// Audit + Newsletter + Notifiche (FASE 7).
		IG_Enna_Audit::init();
		IG_Enna_Newsletter::init();
		IG_Enna_Notifications::init();

		// Asset.
		IG_Enna_Assets::init();

		// Admin.
		if ( is_admin() ) {
			add_action( 'admin_menu', [ 'IG_Enna_Admin_Menu', 'register' ] );
			IG_Enna_Settings::init();
			IG_Enna_Admin_Tickets::init();
			IG_Enna_Admin_Appointments::init();
			IG_Enna_Admin_Colloqui::init();
			IG_Enna_Admin_Newsletter::init();
		}

		// Public layer.
		IG_Enna_Public::init();
		IG_Enna_Frontend::init();
	}
}
