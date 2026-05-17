<?php
/**
 * Salvataggi utente (bookmark) — tabella custom ig_enna_user_saves.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_User_Saves {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ig_enna_user_saves';
	}

	public static function is_saved( $user_id, $object_id, $object_type = 'scheda' ) {
		global $wpdb;
		$t = self::table();
		$found = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$t} WHERE user_id = %d AND object_id = %d AND object_type = %s LIMIT 1",
			$user_id, $object_id, $object_type
		) );
		return (bool) $found;
	}

	public static function save( $user_id, $object_id, $object_type = 'scheda' ) {
		global $wpdb;
		if ( self::is_saved( $user_id, $object_id, $object_type ) ) {
			return true;
		}
		$ok = $wpdb->insert( self::table(), [
			'user_id'     => $user_id,
			'object_type' => $object_type,
			'object_id'   => $object_id,
			'created_at'  => current_time( 'mysql' ),
		], [ '%d', '%s', '%d', '%s' ] );
		return (bool) $ok;
	}

	public static function unsave( $user_id, $object_id, $object_type = 'scheda' ) {
		global $wpdb;
		$ok = $wpdb->delete( self::table(), [
			'user_id'     => $user_id,
			'object_id'   => $object_id,
			'object_type' => $object_type,
		], [ '%d', '%d', '%s' ] );
		return false !== $ok;
	}

	public static function toggle( $user_id, $object_id, $object_type = 'scheda' ) {
		if ( self::is_saved( $user_id, $object_id, $object_type ) ) {
			self::unsave( $user_id, $object_id, $object_type );
			return false;
		}
		self::save( $user_id, $object_id, $object_type );
		return true;
	}

	/**
	 * Restituisce gli ID degli oggetti salvati.
	 *
	 * @return int[]
	 */
	public static function ids_for_user( $user_id, $object_type = 'scheda' ) {
		global $wpdb;
		$t = self::table();
		$rows = $wpdb->get_col( $wpdb->prepare(
			"SELECT object_id FROM {$t} WHERE user_id = %d AND object_type = %s ORDER BY created_at DESC",
			$user_id, $object_type
		) );
		return array_map( 'intval', (array) $rows );
	}

	public static function count_for_user( $user_id, $object_type = 'scheda' ) {
		global $wpdb;
		$t = self::table();
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$t} WHERE user_id = %d AND object_type = %s",
			$user_id, $object_type
		) );
	}
}
