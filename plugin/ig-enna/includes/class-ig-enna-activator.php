<?php
/**
 * Activation hook.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Activator {

	public static function activate() {
		// Ruoli e capability.
		IG_Enna_Roles::install();

		// Tabelle custom.
		IG_Enna_DB::install();

		// CPT e tassonomie (necessari per il flush rewrite).
		IG_Enna_CPT::register();
		IG_Enna_Taxonomies::register();
		IG_Enna_Taxonomies::seed();

		// Settings di default.
		if ( false === get_option( 'ig_enna_settings' ) ) {
			add_option( 'ig_enna_settings', ig_enna_default_settings() );
		}

		flush_rewrite_rules();
	}
}
