<?php
/**
 * Tabelle custom — installazione e migrazione via dbDelta.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_DB {

	/**
	 * Restituisce il prefisso completo delle tabelle del plugin.
	 */
	public static function prefix() {
		global $wpdb;
		return $wpdb->prefix . 'ig_enna_';
	}

	/**
	 * Crea/aggiorna le tabelle custom.
	 */
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();
		$p       = self::prefix();

		$sql = [];

		$sql[] = "CREATE TABLE {$p}tickets (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			operator_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			subject VARCHAR(255) NOT NULL DEFAULT '',
			message LONGTEXT NULL,
			area_slug VARCHAR(64) NOT NULL DEFAULT '',
			priority VARCHAR(20) NOT NULL DEFAULT 'media',
			status VARCHAR(20) NOT NULL DEFAULT 'new',
			sla_due DATETIME NULL DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY operator_id (operator_id),
			KEY status (status),
			KEY area_slug (area_slug),
			KEY created_at (created_at)
		) {$charset};";

		$sql[] = "CREATE TABLE {$p}appointments (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			operator_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			slot_start DATETIME NOT NULL,
			slot_end DATETIME NOT NULL,
			mode VARCHAR(20) NOT NULL DEFAULT 'presenza',
			status VARCHAR(20) NOT NULL DEFAULT 'requested',
			notes TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY operator_id (operator_id),
			KEY slot_start (slot_start),
			KEY status (status)
		) {$charset};";

		$sql[] = "CREATE TABLE {$p}colloqui (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			operator_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			date DATETIME NOT NULL,
			area_slug VARCHAR(64) NOT NULL DEFAULT '',
			outcome TEXT NULL,
			next_step TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY operator_id (operator_id),
			KEY date (date)
		) {$charset};";

		$sql[] = "CREATE TABLE {$p}event_registrations (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT(20) UNSIGNED NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'registered',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY user_id (user_id),
			KEY status (status)
		) {$charset};";

		$sql[] = "CREATE TABLE {$p}user_saves (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			object_type VARCHAR(40) NOT NULL DEFAULT 'scheda',
			object_id BIGINT(20) UNSIGNED NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_object (user_id,object_type,object_id)
		) {$charset};";

		$sql[] = "CREATE TABLE {$p}audit_log (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			action VARCHAR(80) NOT NULL DEFAULT '',
			object_type VARCHAR(40) NOT NULL DEFAULT '',
			object_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			meta LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY action (action),
			KEY object (object_type,object_id),
			KEY created_at (created_at)
		) {$charset};";

		$sql[] = "CREATE TABLE {$p}newsletter_subs (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			email VARCHAR(190) NOT NULL,
			user_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
			interests TEXT NULL,
			confirmed TINYINT(1) NOT NULL DEFAULT 0,
			token VARCHAR(64) NOT NULL DEFAULT '',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY email (email),
			KEY confirmed (confirmed)
		) {$charset};";

		foreach ( $sql as $stmt ) {
			dbDelta( $stmt );
		}

		update_option( 'ig_enna_db_version', IG_ENNA_DB_VERSION );
	}

	/**
	 * Esegue migrazione se la versione DB salvata è inferiore alla corrente.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( 'ig_enna_db_version', '0' );
		if ( version_compare( $installed, IG_ENNA_DB_VERSION, '<' ) ) {
			self::install();
		}
	}
}
