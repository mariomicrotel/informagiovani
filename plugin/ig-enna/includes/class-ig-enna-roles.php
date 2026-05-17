<?php
/**
 * Gestione ruoli e capability del plugin.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Roles {

	/**
	 * Installa ruoli + assegna capability all'amministratore.
	 */
	public static function install() {
		$caps_all = array_fill_keys( ig_enna_capabilities(), true );

		// Responsabile: tutte le cap.
		add_role(
			'ig_enna_responsabile',
			__( 'Responsabile IG', 'ig-enna' ),
			array_merge(
				[ 'read' => true, 'upload_files' => true ],
				$caps_all
			)
		);

		// Operatore: ticket, eventi, colloqui.
		add_role(
			'ig_enna_operator',
			__( 'Operatore Informagiovani', 'ig-enna' ),
			[
				'read'                    => true,
				'upload_files'            => true,
				'ig_enna_manage_tickets'  => true,
				'ig_enna_manage_events'   => true,
				'ig_enna_view_reports'    => true,
			]
		);

		// Editor schede: scrive/pubblica schede informative.
		add_role(
			'ig_enna_editor_schede',
			__( 'Editor Schede IG', 'ig-enna' ),
			[
				'read'                    => true,
				'upload_files'            => true,
				'ig_enna_edit_schede'     => true,
				'ig_enna_publish_schede'  => true,
			]
		);

		// Partner: visibilità ridotta sui propri contenuti.
		add_role(
			'ig_enna_partner',
			__( 'Partner IG', 'ig-enna' ),
			[
				'read'                    => true,
				'upload_files'            => true,
				'ig_enna_manage_partners' => true,
			]
		);

		// Admin riceve tutte le cap del plugin.
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( ig_enna_capabilities() as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Rimuove ruoli (solo in uninstall — usato da uninstall.php).
	 */
	public static function uninstall() {
		foreach ( [ 'ig_enna_responsabile', 'ig_enna_operator', 'ig_enna_editor_schede', 'ig_enna_partner' ] as $r ) {
			remove_role( $r );
		}
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( ig_enna_capabilities() as $cap ) {
				$admin->remove_cap( $cap );
			}
		}
	}
}
