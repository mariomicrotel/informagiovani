<?php
/**
 * Data layer colloqui — CRUD su wp_ig_enna_colloqui.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Colloqui {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ig_enna_colloqui';
	}

	public static function create( array $data ) {
		global $wpdb;
		$date = isset( $data['date'] ) ? sanitize_text_field( $data['date'] ) : '';
		if ( ! $date ) { return false; }
		$ok = $wpdb->insert( self::table(), [
			'user_id'     => isset( $data['user_id'] )     ? (int) $data['user_id']     : 0,
			'operator_id' => isset( $data['operator_id'] ) ? (int) $data['operator_id'] : get_current_user_id(),
			'date'        => $date,
			'area_slug'   => isset( $data['area_slug'] ) ? sanitize_title( $data['area_slug'] ) : '',
			'outcome'     => isset( $data['outcome'] )   ? sanitize_textarea_field( $data['outcome'] )   : '',
			'next_step'   => isset( $data['next_step'] ) ? sanitize_textarea_field( $data['next_step'] ) : '',
			'created_at'  => current_time( 'mysql' ),
		], [ '%d','%d','%s','%s','%s','%s','%s' ] );
		return $ok ? (int) $wpdb->insert_id : false;
	}

	public static function delete( $id ) {
		global $wpdb;
		return false !== $wpdb->delete( self::table(), [ 'id' => (int) $id ], [ '%d' ] );
	}

	public static function query( array $args = [] ) {
		global $wpdb;
		$args = array_merge( [
			'user_id' => null, 'operator_id' => null, 'limit' => 50, 'offset' => 0,
		], $args );
		$where = [ '1=1' ]; $params = [];
		if ( $args['user_id']     !== null ) { $where[] = 'user_id = %d';     $params[] = (int) $args['user_id']; }
		if ( $args['operator_id'] !== null ) { $where[] = 'operator_id = %d'; $params[] = (int) $args['operator_id']; }
		$limit  = max( 1, (int) $args['limit'] );
		$offset = max( 0, (int) $args['offset'] );
		$where_sql = implode( ' AND ', $where );
		$table = self::table();
		$sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY date DESC LIMIT {$limit} OFFSET {$offset}";
		$rows = $wpdb->get_results( $params ? $wpdb->prepare( $sql, $params ) : $sql, ARRAY_A );
		return $rows ?: [];
	}
}
