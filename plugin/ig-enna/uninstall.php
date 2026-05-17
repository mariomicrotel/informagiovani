<?php
/**
 * Uninstall — eseguito SOLO se il plugin viene rimosso da WP.
 * Pulisce dati solo se la setting `delete_data_on_uninstall` è true.
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$settings = get_option( 'ig_enna_settings', [] );
$delete   = ! empty( $settings['delete_data_on_uninstall'] );

if ( ! $delete ) {
	return;
}

global $wpdb;

$tables = [
	'ig_enna_tickets',
	'ig_enna_appointments',
	'ig_enna_colloqui',
	'ig_enna_event_registrations',
	'ig_enna_user_saves',
	'ig_enna_audit_log',
	'ig_enna_newsletter_subs',
];

foreach ( $tables as $t ) {
	$table = $wpdb->prefix . $t;
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

delete_option( 'ig_enna_settings' );
delete_option( 'ig_enna_db_version' );

foreach ( [ 'ig_enna_responsabile', 'ig_enna_operator', 'ig_enna_editor_schede', 'ig_enna_partner' ] as $role ) {
	remove_role( $role );
}
