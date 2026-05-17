<?php
/**
 * Data layer ticket — CRUD su wp_ig_enna_tickets.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Tickets {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ig_enna_tickets';
	}

	public static function statuses() {
		return [
			'new'      => __( 'Nuovo',            'ig-enna' ),
			'assigned' => __( 'Assegnato',        'ig-enna' ),
			'work'     => __( 'In lavorazione',   'ig-enna' ),
			'wait'     => __( 'In attesa utente', 'ig-enna' ),
			'done'     => __( 'Evaso',            'ig-enna' ),
			'closed'   => __( 'Chiuso',           'ig-enna' ),
		];
	}

	public static function priorities() {
		return [
			'bassa' => __( 'Bassa', 'ig-enna' ),
			'media' => __( 'Media', 'ig-enna' ),
			'alta'  => __( 'Alta',  'ig-enna' ),
		];
	}

	public static function status_label( $key ) {
		$s = self::statuses();
		return isset( $s[ $key ] ) ? $s[ $key ] : $key;
	}

	public static function priority_label( $key ) {
		$p = self::priorities();
		return isset( $p[ $key ] ) ? $p[ $key ] : $key;
	}

	/**
	 * Crea un ticket. $data: user_id, subject, message, area_slug, priority.
	 *
	 * @return int|false ID inserito o false.
	 */
	public static function create( array $data ) {
		global $wpdb;
		$subject = isset( $data['subject'] )   ? sanitize_text_field( $data['subject'] )       : '';
		$message = isset( $data['message'] )   ? wp_kses_post( $data['message'] )              : '';
		$area    = isset( $data['area_slug'] ) ? sanitize_title( $data['area_slug'] )          : '';
		$prio    = isset( $data['priority'] )  ? sanitize_key( $data['priority'] )             : 'media';
		$user_id = isset( $data['user_id'] )   ? (int) $data['user_id']                        : get_current_user_id();

		if ( ! array_key_exists( $prio, self::priorities() ) ) {
			$prio = 'media';
		}
		if ( $subject === '' || $message === '' ) {
			return false;
		}

		$sla = ig_enna_get_setting( 'default_sla_hours', 48 );
		$sla_due = $sla > 0 ? gmdate( 'Y-m-d H:i:s', current_time( 'timestamp', true ) + ( $sla * HOUR_IN_SECONDS ) ) : null;

		$row = [
			'user_id'    => $user_id,
			'subject'    => $subject,
			'message'    => $message,
			'area_slug'  => $area,
			'priority'   => $prio,
			'status'     => 'new',
			'sla_due'    => $sla_due,
			'created_at' => current_time( 'mysql' ),
			'updated_at' => current_time( 'mysql' ),
		];
		$ok = $wpdb->insert( self::table(), $row, [ '%d','%s','%s','%s','%s','%s','%s','%s','%s' ] );
		if ( ! $ok ) { return false; }
		$id = (int) $wpdb->insert_id;

		/**
		 * Ticket creato.
		 *
		 * @param int   $id   ID ticket.
		 * @param array $row  Dati inseriti.
		 */
		do_action( 'ig_enna_ticket_created', $id, $row );
		return $id;
	}

	public static function update( $id, array $data ) {
		global $wpdb;
		$id = (int) $id;
		if ( ! $id ) { return false; }

		$allowed = [];
		if ( isset( $data['status'] ) ) {
			$s = sanitize_key( $data['status'] );
			if ( array_key_exists( $s, self::statuses() ) ) {
				$allowed['status'] = $s;
			}
		}
		if ( isset( $data['priority'] ) ) {
			$p = sanitize_key( $data['priority'] );
			if ( array_key_exists( $p, self::priorities() ) ) {
				$allowed['priority'] = $p;
			}
		}
		if ( array_key_exists( 'operator_id', $data ) ) {
			$allowed['operator_id'] = (int) $data['operator_id'];
		}
		if ( isset( $data['area_slug'] ) ) {
			$allowed['area_slug'] = sanitize_title( $data['area_slug'] );
		}
		if ( isset( $data['subject'] ) ) {
			$allowed['subject'] = sanitize_text_field( $data['subject'] );
		}
		if ( isset( $data['message'] ) ) {
			$allowed['message'] = wp_kses_post( $data['message'] );
		}

		if ( ! $allowed ) {
			return true;
		}
		$before = self::get( $id );
		$allowed['updated_at'] = current_time( 'mysql' );
		$ok = false !== $wpdb->update( self::table(), $allowed, [ 'id' => $id ] );
		if ( $ok ) {
			$after = self::get( $id );
			/**
			 * Ticket aggiornato.
			 *
			 * @param int   $id     ID ticket.
			 * @param array $before Stato precedente.
			 * @param array $after  Nuovo stato.
			 */
			do_action( 'ig_enna_ticket_updated', $id, $before ?: [], $after ?: [] );
		}
		return $ok;
	}

	public static function get( $id ) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::table() . " WHERE id = %d", (int) $id ), ARRAY_A );
		return $row ?: null;
	}

	/**
	 * Lista ticket con filtri.
	 *
	 * @param array $args  ['user_id'=>int, 'operator_id'=>int|0, 'status'=>string, 'area'=>string, 'limit'=>int, 'offset'=>int, 'orderby'=>string]
	 * @return array{rows: array<int,array<string,mixed>>, total: int}
	 */
	public static function query( array $args = [] ) {
		global $wpdb;
		$args = array_merge( [
			'user_id'     => null,
			'operator_id' => null,
			'status'      => null,
			'area'        => null,
			'limit'       => 20,
			'offset'      => 0,
			'orderby'     => 'created_at DESC',
		], $args );

		$where  = [ '1=1' ];
		$params = [];
		if ( $args['user_id']     !== null ) { $where[] = 'user_id = %d';     $params[] = (int) $args['user_id']; }
		if ( $args['operator_id'] !== null ) { $where[] = 'operator_id = %d'; $params[] = (int) $args['operator_id']; }
		if ( $args['status'] && array_key_exists( $args['status'], self::statuses() ) ) {
			$where[] = 'status = %s'; $params[] = $args['status'];
		}
		if ( $args['area'] ) { $where[] = 'area_slug = %s'; $params[] = sanitize_title( $args['area'] ); }

		// orderby whitelist.
		$orderby_safe = 'created_at DESC';
		$allowed_ob   = [ 'created_at DESC', 'created_at ASC', 'updated_at DESC', 'priority DESC', 'sla_due ASC' ];
		if ( in_array( $args['orderby'], $allowed_ob, true ) ) {
			$orderby_safe = $args['orderby'];
		}

		$limit  = max( 1, (int) $args['limit'] );
		$offset = max( 0, (int) $args['offset'] );

		$table  = self::table();
		$where_sql = implode( ' AND ', $where );

		$total_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) $wpdb->get_var( $params ? $wpdb->prepare( $total_sql, $params ) : $total_sql );

		$rows_sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby_safe} LIMIT {$limit} OFFSET {$offset}";
		$rows     = $wpdb->get_results( $params ? $wpdb->prepare( $rows_sql, $params ) : $rows_sql, ARRAY_A );

		return [ 'rows' => $rows ?: [], 'total' => $total ];
	}

	public static function count_by_status() {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT status, COUNT(*) as n FROM " . self::table() . " GROUP BY status", ARRAY_A );
		$out = [];
		foreach ( (array) $rows as $r ) { $out[ $r['status'] ] = (int) $r['n']; }
		return $out;
	}

	public static function delete( $id ) {
		global $wpdb;
		return false !== $wpdb->delete( self::table(), [ 'id' => (int) $id ], [ '%d' ] );
	}
}
