<?php
/**
 * Menu admin top-level del plugin.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_Menu {

	const SLUG = 'ig-enna';

	public static function register() {
		$cap = 'ig_enna_manage';

		add_menu_page(
			__( 'Informagiovani Enna', 'ig-enna' ),
			__( 'Informagiovani', 'ig-enna' ),
			$cap,
			self::SLUG,
			[ __CLASS__, 'render_dashboard' ],
			'dashicons-groups',
			25
		);

		add_submenu_page(
			self::SLUG,
			__( 'Dashboard', 'ig-enna' ),
			__( 'Dashboard', 'ig-enna' ),
			$cap,
			self::SLUG,
			[ __CLASS__, 'render_dashboard' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Home page', 'ig-enna' ),
			__( 'Home page', 'ig-enna' ),
			$cap,
			'ig-enna-home',
			[ 'IG_Enna_Admin_Home', 'render_page' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Schede informative', 'ig-enna' ),
			__( 'Schede informative', 'ig-enna' ),
			$cap,
			'edit.php?post_type=ig_scheda'
		);

		add_submenu_page(
			self::SLUG,
			__( 'Tipologie schede', 'ig-enna' ),
			__( '↳ Tipologie', 'ig-enna' ),
			$cap,
			IG_Enna_Admin_Types::PAGE_SLUG,
			[ 'IG_Enna_Admin_Types', 'render' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Eventi', 'ig-enna' ),
			__( 'Eventi', 'ig-enna' ),
			$cap,
			'edit.php?post_type=ig_evento'
		);

		add_submenu_page(
			self::SLUG,
			__( 'Ticket', 'ig-enna' ),
			__( 'Ticket', 'ig-enna' ),
			'ig_enna_manage_tickets',
			'ig-enna-tickets',
			[ 'IG_Enna_Admin_Tickets', 'render' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Appuntamenti', 'ig-enna' ),
			__( 'Appuntamenti', 'ig-enna' ),
			'ig_enna_manage_tickets',
			'ig-enna-appointments',
			[ 'IG_Enna_Admin_Appointments', 'render' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Colloqui', 'ig-enna' ),
			__( 'Colloqui', 'ig-enna' ),
			'ig_enna_manage_tickets',
			'ig-enna-colloqui',
			[ 'IG_Enna_Admin_Colloqui', 'render' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Partner', 'ig-enna' ),
			__( 'Partner', 'ig-enna' ),
			'ig_enna_manage_partners',
			'edit.php?post_type=ig_partner'
		);

		add_submenu_page(
			self::SLUG,
			__( 'Percorsi Impresa', 'ig-enna' ),
			__( 'Percorsi Impresa', 'ig-enna' ),
			$cap,
			'edit.php?post_type=ig_percorso'
		);

		add_submenu_page(
			self::SLUG,
			__( 'News', 'ig-enna' ),
			__( 'News', 'ig-enna' ),
			$cap,
			'edit.php?post_type=ig_news'
		);

		add_submenu_page(
			self::SLUG,
			__( 'Report', 'ig-enna' ),
			__( 'Report', 'ig-enna' ),
			'ig_enna_view_reports',
			'ig-enna-report',
			[ 'IG_Enna_Admin_Report', 'render' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Newsletter', 'ig-enna' ),
			__( 'Newsletter', 'ig-enna' ),
			'ig_enna_view_reports',
			'ig-enna-newsletter',
			[ 'IG_Enna_Admin_Newsletter', 'render' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Impostazioni', 'ig-enna' ),
			__( 'Impostazioni', 'ig-enna' ),
			$cap,
			'ig-enna-settings',
			[ 'IG_Enna_Settings', 'render_page' ]
		);

		add_submenu_page(
			self::SLUG,
			__( 'Audit log', 'ig-enna' ),
			__( 'Audit log', 'ig-enna' ),
			'ig_enna_view_reports',
			'ig-enna-audit',
			[ 'IG_Enna_Admin_Audit', 'render' ]
		);
	}

	public static function render_dashboard() {
		if ( ! current_user_can( 'ig_enna_manage' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ) );
		}
		include IG_ENNA_DIR . 'admin/views/dashboard.php';
	}

	public static function render_placeholder_tickets() {
		self::render_placeholder( __( 'Ticket', 'ig-enna' ), __( 'Gestione ticket — disponibile dalla FASE 5.', 'ig-enna' ) );
	}

	public static function render_placeholder_appointments() {
		self::render_placeholder( __( 'Appuntamenti', 'ig-enna' ), __( 'Gestione appuntamenti sportello — disponibile dalla FASE 5.', 'ig-enna' ) );
	}

	public static function render_placeholder_colloqui() {
		self::render_placeholder( __( 'Colloqui', 'ig-enna' ), __( 'Gestione colloqui di orientamento — disponibile dalla FASE 5.', 'ig-enna' ) );
	}

	public static function render_placeholder_audit() {
		self::render_placeholder( __( 'Audit log', 'ig-enna' ), __( 'Tracciamento operazioni — disponibile dalla FASE 7.', 'ig-enna' ) );
	}

	private static function render_placeholder( $title, $message ) {
		echo '<div class="wrap ig-enna-admin"><h1>' . esc_html( $title ) . '</h1>';
		echo '<div class="ig-enna-notice"><p>' . esc_html( $message ) . '</p></div></div>';
	}
}
