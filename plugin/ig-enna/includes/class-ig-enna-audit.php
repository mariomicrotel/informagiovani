<?php
/**
 * Audit log — log delle operazioni sensibili.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Audit {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ig_enna_audit_log';
	}

	public static function is_enabled() {
		return (bool) ig_enna_get_setting( 'enable_audit_log', 1 );
	}

	public static function init() {
		// Auto-log su eventi ticket.
		add_action( 'ig_enna_ticket_created', [ __CLASS__, 'on_ticket_created' ], 10, 2 );
		add_action( 'ig_enna_ticket_updated', [ __CLASS__, 'on_ticket_updated' ], 10, 3 );

		// Auto-log su pubblicazione schede.
		add_action( 'transition_post_status', [ __CLASS__, 'on_post_transition' ], 10, 3 );
	}

	/**
	 * Registra un evento.
	 *
	 * @param string $action      es. 'ticket_create', 'ticket.status_change', 'scheda.publish'
	 * @param string $object_type es. 'ticket', 'scheda'
	 * @param int    $object_id
	 * @param array  $meta        dati strutturati (saranno serializzati JSON)
	 */
	public static function log( $action, $object_type, $object_id, $meta = [] ) {
		if ( ! self::is_enabled() ) {
			return false;
		}
		global $wpdb;
		return (bool) $wpdb->insert( self::table(), [
			'user_id'     => get_current_user_id(),
			'action'      => sanitize_key( $action ),
			'object_type' => sanitize_key( $object_type ),
			'object_id'   => (int) $object_id,
			'meta'        => $meta ? wp_json_encode( $meta ) : null,
			'created_at'  => current_time( 'mysql' ),
		], [ '%d','%s','%s','%d','%s','%s' ] );
	}

	public static function query( array $args = [] ) {
		global $wpdb;
		$args = array_merge( [
			'user_id' => null, 'action' => null, 'object_type' => null,
			'limit' => 100, 'offset' => 0,
		], $args );
		$where = [ '1=1' ]; $params = [];
		if ( $args['user_id']     !== null ) { $where[] = 'user_id = %d';     $params[] = (int) $args['user_id']; }
		if ( $args['action'] )       { $where[] = 'action = %s';      $params[] = sanitize_key( $args['action'] ); }
		if ( $args['object_type'] )  { $where[] = 'object_type = %s'; $params[] = sanitize_key( $args['object_type'] ); }

		$limit  = max( 1, (int) $args['limit'] );
		$offset = max( 0, (int) $args['offset'] );
		$where_sql = implode( ' AND ', $where );
		$table = self::table();
		$sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
		$rows = $wpdb->get_results( $params ? $wpdb->prepare( $sql, $params ) : $sql, ARRAY_A );

		$total_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total = (int) $wpdb->get_var( $params ? $wpdb->prepare( $total_sql, $params ) : $total_sql );

		return [ 'rows' => $rows ?: [], 'total' => $total ];
	}

	/* ============ Auto-log hooks ============ */

	public static function on_ticket_created( $ticket_id, $data ) {
		self::log( 'ticket_create', 'ticket', $ticket_id, [
			'subject'  => $data['subject']  ?? '',
			'priority' => $data['priority'] ?? '',
			'area'     => $data['area_slug']?? '',
		] );
	}

	public static function on_ticket_updated( $ticket_id, $before, $after ) {
		$diff = [];
		foreach ( [ 'status', 'priority', 'operator_id' ] as $k ) {
			if ( isset( $after[ $k ] ) && ( $before[ $k ] ?? null ) != $after[ $k ] ) {
				$diff[ $k ] = [ 'from' => $before[ $k ] ?? null, 'to' => $after[ $k ] ];
			}
		}
		if ( $diff ) {
			self::log( 'ticket_update', 'ticket', $ticket_id, $diff );
		}
	}

	public static function on_post_transition( $new, $old, $post ) {
		if ( ! in_array( $post->post_type, [ 'ig_scheda', 'ig_evento', 'ig_partner', 'ig_percorso' ], true ) ) {
			return;
		}
		if ( $new === $old ) {
			return;
		}
		if ( $new === 'publish' && $old !== 'publish' ) {
			self::log( $post->post_type . '_publish', $post->post_type, $post->ID, [ 'title' => $post->post_title ] );
		} elseif ( $old === 'publish' && $new !== 'publish' ) {
			self::log( $post->post_type . '_unpublish', $post->post_type, $post->ID, [ 'title' => $post->post_title, 'new_status' => $new ] );
		}
	}
}
