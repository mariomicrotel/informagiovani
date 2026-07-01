<?php
/**
 * Data layer iscrizioni eventi — wp_ig_enna_event_registrations.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Event_Registrations {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ig_enna_event_registrations';
	}

	public static function statuses() {
		return [
			'registered' => __( 'Iscritto',           'ig-enna' ),
			'waitlist'   => __( 'In lista d\'attesa', 'ig-enna' ),
			'cancelled'  => __( 'Annullato',          'ig-enna' ),
			'attended'   => __( 'Presente',           'ig-enna' ),
			'no_show'    => __( 'No show',            'ig-enna' ),
		];
	}

	public static function status_label( $key ) {
		$s = self::statuses();
		return $s[ $key ] ?? $key;
	}

	/**
	 * Crea una nuova iscrizione, se non esiste già una attiva per stesso
	 * evento + email/user. Applica capacity dell'evento come waitlist.
	 *
	 * @param array{event_id:int,user_id?:int,name:string,email:string,phone?:string,notes?:string} $data
	 * @return int|false|WP_Error ID iscrizione, false su input non valido, WP_Error su duplicato/capacity.
	 */
	public static function create( array $data ) {
		global $wpdb;
		$event_id = isset( $data['event_id'] ) ? (int) $data['event_id'] : 0;
		$name     = isset( $data['name'] )     ? sanitize_text_field( $data['name'] )   : '';
		$email    = isset( $data['email'] )    ? sanitize_email( $data['email'] )       : '';
		$phone    = isset( $data['phone'] )    ? sanitize_text_field( $data['phone'] )  : '';
		$notes    = isset( $data['notes'] )    ? sanitize_textarea_field( $data['notes'] ) : '';
		$user_id  = isset( $data['user_id'] )  ? (int) $data['user_id']                 : 0;

		if ( ! $event_id || ! $name || ! is_email( $email ) ) {
			return false;
		}

		$event = get_post( $event_id );
		if ( ! $event || $event->post_type !== 'ig_evento' || $event->post_status !== 'publish' ) {
			return new WP_Error( 'invalid_event', __( 'Evento non trovato o non pubblicato.', 'ig-enna' ) );
		}

		// Verifica duplicato: stesso evento + stessa email (o stesso user_id) non ancora cancellata.
		$table = self::table();
		$dup = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$table} WHERE event_id = %d AND status != 'cancelled' AND (email = %s OR (user_id > 0 AND user_id = %d)) LIMIT 1",
			$event_id, $email, $user_id
		) );
		if ( $dup ) {
			return new WP_Error( 'already_registered', __( 'Sei già iscritto a questo evento.', 'ig-enna' ) );
		}

		// Capacity check → waitlist se pieno.
		$cap = (int) get_post_meta( $event_id, '_ig_enna_event_capacity', true );
		$status = 'registered';
		if ( $cap > 0 ) {
			$active = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE event_id = %d AND status IN ('registered','attended')",
				$event_id
			) );
			if ( $active >= $cap ) {
				$status = 'waitlist';
			}
		}

		$ok = $wpdb->insert( $table, [
			'event_id'   => $event_id,
			'user_id'    => $user_id,
			'name'       => $name,
			'email'      => $email,
			'phone'      => $phone,
			'notes'      => $notes,
			'status'     => $status,
			'created_at' => current_time( 'mysql' ),
		], [ '%d','%d','%s','%s','%s','%s','%s','%s' ] );
		if ( ! $ok ) {
			return new WP_Error( 'db_error', __( 'Errore salvataggio iscrizione.', 'ig-enna' ) );
		}
		$id = (int) $wpdb->insert_id;

		/**
		 * Iscrizione evento creata.
		 *
		 * @param int   $id       ID iscrizione.
		 * @param int   $event_id ID evento.
		 * @param array $data     Dati inseriti.
		 * @param string $status  'registered' | 'waitlist'.
		 */
		do_action( 'ig_enna_event_registered', $id, $event_id, $data, $status );

		if ( class_exists( 'IG_Enna_Audit' ) ) {
			IG_Enna_Audit::log( 'event_register', 'event', $event_id, [
				'email' => $email, 'status' => $status,
			] );
		}
		return $id;
	}

	public static function get( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::table() . " WHERE id = %d", (int) $id ), ARRAY_A ) ?: null;
	}

	public static function update_status( $id, $status ) {
		global $wpdb;
		if ( ! array_key_exists( $status, self::statuses() ) ) { return false; }
		return false !== $wpdb->update( self::table(), [ 'status' => $status ], [ 'id' => (int) $id ] );
	}

	public static function delete( $id ) {
		global $wpdb;
		return false !== $wpdb->delete( self::table(), [ 'id' => (int) $id ], [ '%d' ] );
	}

	/**
	 * Query iscrizioni con filtri.
	 *
	 * @param array{event_id?:int, user_id?:int, email?:string, status?:string, limit?:int, offset?:int} $args
	 * @return array{rows: array<int,array<string,mixed>>, total: int}
	 */
	public static function query( array $args = [] ) {
		global $wpdb;
		$args = array_merge( [
			'event_id' => null, 'user_id' => null, 'email' => null, 'status' => null,
			'limit'    => 50,   'offset'  => 0,
		], $args );

		$where = [ '1=1' ]; $params = [];
		if ( $args['event_id'] !== null ) { $where[] = 'event_id = %d'; $params[] = (int) $args['event_id']; }
		if ( $args['user_id']  !== null ) { $where[] = 'user_id = %d';  $params[] = (int) $args['user_id']; }
		if ( $args['email'] )              { $where[] = 'email = %s';    $params[] = sanitize_email( $args['email'] ); }
		if ( $args['status'] && array_key_exists( $args['status'], self::statuses() ) ) {
			$where[] = 'status = %s'; $params[] = $args['status'];
		}
		$where_sql = implode( ' AND ', $where );
		$table = self::table();

		$total = (int) $wpdb->get_var( $params
			? $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", $params )
			: "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}"
		);
		$limit  = max( 1, (int) $args['limit'] );
		$offset = max( 0, (int) $args['offset'] );
		$rows = $wpdb->get_results( $params
			? $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}", $params )
			: "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
			ARRAY_A
		);
		return [ 'rows' => $rows ?: [], 'total' => $total ];
	}

	/** Conteggio iscritti attivi (registered + attended) per un evento. */
	public static function count_active( $event_id ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM " . self::table() . " WHERE event_id = %d AND status IN ('registered','attended')",
			(int) $event_id
		) );
	}

	/**
	 * Restituisce gli eventi disponibili per iscrizione: pubblicati,
	 * con data >= oggi, stato open (o vuoto).
	 *
	 * @return WP_Post[]
	 */
	public static function available_events( $limit = 50 ) {
		$today = current_time( 'Y-m-d' );
		$q = new WP_Query( [
			'post_type'      => 'ig_evento',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $limit,
			'meta_key'       => '_ig_enna_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => [
				'relation' => 'AND',
				[ 'key' => '_ig_enna_event_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' ],
				[
					'relation' => 'OR',
					[ 'key' => '_ig_enna_event_status', 'compare' => 'NOT EXISTS' ],
					[ 'key' => '_ig_enna_event_status', 'value' => '', 'compare' => '=' ],
					[ 'key' => '_ig_enna_event_status', 'value' => 'open', 'compare' => '=' ],
				],
			],
			'no_found_rows'  => true,
		] );
		return $q->posts;
	}
}
