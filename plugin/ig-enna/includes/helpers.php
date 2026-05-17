<?php
/**
 * Helper functions condivise. Prefisso ig_enna_.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lista capability custom del plugin.
 *
 * @return string[]
 */
function ig_enna_capabilities() {
	return [
		'ig_enna_manage',
		'ig_enna_edit_schede',
		'ig_enna_publish_schede',
		'ig_enna_manage_tickets',
		'ig_enna_manage_events',
		'ig_enna_manage_partners',
		'ig_enna_view_reports',
		'ig_enna_export_data',
	];
}

/**
 * Aree tematiche di base (slug => label).
 *
 * @return array<string,string>
 */
function ig_enna_default_areas() {
	return [
		'lavoro'     => __( 'Lavoro', 'ig-enna' ),
		'formazione' => __( 'Formazione', 'ig-enna' ),
		'impresa'    => __( 'Fare Impresa', 'ig-enna' ),
		'estero'     => __( 'Estero & Mobilità', 'ig-enna' ),
		'diritti'    => __( 'Diritti', 'ig-enna' ),
		'cultura'    => __( 'Cultura', 'ig-enna' ),
		'civile'     => __( 'Servizio Civile', 'ig-enna' ),
		'concorso'   => __( 'Concorsi', 'ig-enna' ),
	];
}

/**
 * Target di base.
 *
 * @return string[]
 */
function ig_enna_default_targets() {
	return [
		'Universitari',
		'Neolaureati',
		'NEET',
		'18–29 anni',
		'18–35 anni',
		'Disoccupati',
		'Aspiranti imprenditori',
		'Studenti',
		'Famiglie ISEE',
	];
}

/**
 * Territori di base.
 *
 * @return string[]
 */
function ig_enna_default_territories() {
	return [
		'Enna città',
		'Provincia di Enna',
		'Sicilia',
		'Italia',
		'Italia · Sud',
		'Europa',
	];
}

/**
 * Fonti di base.
 *
 * @return string[]
 */
function ig_enna_default_sources() {
	return [ 'Ufficiale', 'Partner', 'Verificata' ];
}

/**
 * Settings default.
 *
 * @return array<string,mixed>
 */
function ig_enna_default_settings() {
	return [
		'org_name'                  => 'Informagiovani Enna',
		'contact_email'             => get_option( 'admin_email' ),
		'enable_public_registration'=> 1,
		'default_sla_hours'         => 48,
		'enable_audit_log'          => 1,
		'delete_data_on_uninstall'  => 0,
	];
}

/**
 * Sanitize callback per ig_enna_settings.
 *
 * @param mixed $input
 * @return array<string,mixed>
 */
function ig_enna_sanitize_settings( $input ) {
	$defaults = ig_enna_default_settings();
	$input    = is_array( $input ) ? $input : [];
	$out      = [];

	$out['org_name']                   = isset( $input['org_name'] ) ? sanitize_text_field( $input['org_name'] ) : $defaults['org_name'];
	$out['contact_email']              = isset( $input['contact_email'] ) ? sanitize_email( $input['contact_email'] ) : $defaults['contact_email'];
	$out['enable_public_registration'] = ! empty( $input['enable_public_registration'] ) ? 1 : 0;
	$out['default_sla_hours']          = isset( $input['default_sla_hours'] ) ? max( 1, (int) $input['default_sla_hours'] ) : $defaults['default_sla_hours'];
	$out['enable_audit_log']           = ! empty( $input['enable_audit_log'] ) ? 1 : 0;
	$out['delete_data_on_uninstall']   = ! empty( $input['delete_data_on_uninstall'] ) ? 1 : 0;

	return $out;
}

/**
 * Helper centrale per recuperare una setting.
 *
 * @param string $key
 * @param mixed  $fallback
 * @return mixed
 */
function ig_enna_get_setting( $key, $fallback = null ) {
	$opts = wp_parse_args( get_option( 'ig_enna_settings', [] ), ig_enna_default_settings() );
	return array_key_exists( $key, $opts ) ? $opts[ $key ] : $fallback;
}
