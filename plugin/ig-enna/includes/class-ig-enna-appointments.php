<?php
/**
 * Data layer appuntamenti — CRUD su wp_ig_enna_appointments.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Appointments {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ig_enna_appointments';
	}

	public static function modes() {
		return [
			'presenza' => __( 'In presenza', 'ig-enna' ),
			'online'   => __( 'Online',      'ig-enna' ),
		];
	}

	public static function statuses() {
		return [
			'requested' => __( 'Richiesto',   'ig-enna' ),
			'confirmed' => __( 'Confermato',  'ig-enna' ),
			'cancelled' => __( 'Annullato',   'ig-enna' ),
			'done'      => __( 'Concluso',    'ig-enna' ),
			'no_show'   => __( 'No show',     'ig-enna' ),
		];
	}

	public static function status_label( $k ) {
		$s = self::statuses();
		return isset( $s[ $k ] ) ? $s[ $k ] : $k;
	}

	/**
	 * Verifica se esiste già uno slot sovrapposto (operatore o utente) attivo.
	 *
	 * @return bool true se c'è conflitto.
	 */
	public static function has_conflict( $start, $end, $operator_id = 0, $user_id = 0, $exclude_id = 0 ) {
		global $wpdb;
		if ( ! $start || ! $end ) { return false; }
		$table = self::table();
		// Esclude appuntamenti cancellati / conclusi.
		$active = "status NOT IN ('cancelled','done','no_show')";
		$where  = [];
		$params = [ $start, $end ]; // sovrapposizione: existing.start < new.end AND existing.end > new.start
		$overlap = "(slot_start < %s AND slot_end > %s)";
		if ( $operator_id > 0 ) {
			$where[] = "(operator_id = %d AND {$overlap})";
			$params[] = (int) $operator_id;
			$params[] = $end;
			$params[] = $start;
		}
		if ( $user_id > 0 ) {
			$where[] = "(user_id = %d AND {$overlap})";
			$params[] = (int) $user_id;
			$params[] = $end;
			$params[] = $start;
		}
		// Rimuove i 2 placeholder iniziali non usati se nessun ramo è attivo.
		if ( ! $where ) { return false; }
		array_shift( $params ); array_shift( $params );

		$sql = "SELECT id FROM {$table} WHERE {$active} AND (" . implode( ' OR ', $where ) . ")";
		if ( $exclude_id > 0 ) {
			$sql .= " AND id <> %d";
			$params[] = (int) $exclude_id;
		}
		$sql .= " LIMIT 1";
		$id = $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
		return (bool) $id;
	}

	public static function create( array $data ) {
		global $wpdb;
		$start = isset( $data['slot_start'] ) ? sanitize_text_field( $data['slot_start'] ) : '';
		$end   = isset( $data['slot_end'] )   ? sanitize_text_field( $data['slot_end'] )   : '';
		if ( ! $start || ! $end ) { return false; }
		// Verifica ordine cronologico.
		$ts_start = strtotime( $start );
		$ts_end   = strtotime( $end );
		if ( ! $ts_start || ! $ts_end || $ts_end <= $ts_start ) { return false; }
		// Anti-conflitto: stesso operatore o stesso utente con sovrapposizione.
		$operator_id = isset( $data['operator_id'] ) ? (int) $data['operator_id'] : 0;
		$user_id     = isset( $data['user_id'] )     ? (int) $data['user_id']     : 0;
		if ( self::has_conflict( $start, $end, $operator_id, $user_id ) ) {
			return false;
		}

		$mode = isset( $data['mode'] ) ? sanitize_key( $data['mode'] ) : 'presenza';
		if ( ! array_key_exists( $mode, self::modes() ) ) { $mode = 'presenza'; }

		$status = isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'requested';
		if ( ! array_key_exists( $status, self::statuses() ) ) { $status = 'requested'; }

		$ok = $wpdb->insert( self::table(), [
			'user_id'     => isset( $data['user_id'] )     ? (int) $data['user_id']     : 0,
			'operator_id' => isset( $data['operator_id'] ) ? (int) $data['operator_id'] : 0,
			'slot_start'  => $start,
			'slot_end'    => $end,
			'mode'        => $mode,
			'status'      => $status,
			'notes'       => isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : '',
			'created_at'  => current_time( 'mysql' ),
		], [ '%d','%d','%s','%s','%s','%s','%s','%s' ] );

		return $ok ? (int) $wpdb->insert_id : false;
	}

	public static function update( $id, array $data ) {
		global $wpdb;
		$id = (int) $id;
		$allowed = [];
		if ( isset( $data['status'] ) ) {
			$s = sanitize_key( $data['status'] );
			if ( array_key_exists( $s, self::statuses() ) ) { $allowed['status'] = $s; }
		}
		if ( array_key_exists( 'operator_id', $data ) ) { $allowed['operator_id'] = (int) $data['operator_id']; }
		if ( isset( $data['notes'] ) ) { $allowed['notes'] = sanitize_textarea_field( $data['notes'] ); }
		if ( ! $allowed ) { return true; }
		return false !== $wpdb->update( self::table(), $allowed, [ 'id' => $id ] );
	}

	public static function delete( $id ) {
		global $wpdb;
		return false !== $wpdb->delete( self::table(), [ 'id' => (int) $id ], [ '%d' ] );
	}

	public static function query( array $args = [] ) {
		global $wpdb;
		$args = array_merge( [
			'user_id' => null, 'operator_id' => null, 'status' => null,
			'limit'   => 50, 'offset' => 0, 'orderby' => 'slot_start ASC',
		], $args );
		$where = [ '1=1' ]; $params = [];
		if ( $args['user_id']     !== null ) { $where[] = 'user_id = %d';     $params[] = (int) $args['user_id']; }
		if ( $args['operator_id'] !== null ) { $where[] = 'operator_id = %d'; $params[] = (int) $args['operator_id']; }
		if ( $args['status'] && array_key_exists( $args['status'], self::statuses() ) ) {
			$where[] = 'status = %s'; $params[] = $args['status'];
		}
		$ob = in_array( $args['orderby'], [ 'slot_start ASC', 'slot_start DESC' ], true ) ? $args['orderby'] : 'slot_start ASC';
		$limit  = max( 1, (int) $args['limit'] );
		$offset = max( 0, (int) $args['offset'] );
		$where_sql = implode( ' AND ', $where );
		$table = self::table();
		$rows  = $wpdb->get_results( $params
			? $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$ob} LIMIT {$limit} OFFSET {$offset}", $params )
			: "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$ob} LIMIT {$limit} OFFSET {$offset}",
			ARRAY_A );
		return $rows ?: [];
	}
}
