<?php
/**
 * Newsletter — subscribe/confirm/list.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Newsletter {

	const NONCE = 'ig_enna_newsletter';

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ig_enna_newsletter_subs';
	}

	public static function init() {
		add_action( 'init', [ __CLASS__, 'handle_subscribe' ] );
		add_action( 'init', [ __CLASS__, 'handle_confirm' ], 5 );
	}

	/**
	 * Handler POST form iscrizione.
	 */
	public static function handle_subscribe() {
		if ( empty( $_POST['ig_enna_action'] ) || $_POST['ig_enna_action'] !== 'newsletter_subscribe' ) {
			return;
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::NONCE ) ) {
			IG_Enna_Auth::add_notice( 'error', __( 'Sessione scaduta. Riprova.', 'ig-enna' ) );
			return;
		}
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			IG_Enna_Auth::add_notice( 'error', __( 'Email non valida.', 'ig-enna' ) );
			return;
		}
		$interests = isset( $_POST['interests'] ) && is_array( $_POST['interests'] )
			? array_map( 'sanitize_title', wp_unslash( $_POST['interests'] ) )
			: [];

		$ok = self::subscribe( $email, $interests, is_user_logged_in() ? get_current_user_id() : null );
		if ( $ok ) {
			IG_Enna_Auth::add_notice( 'success', __( 'Iscrizione registrata. Riceverai un\'email di conferma.', 'ig-enna' ) );
		} else {
			IG_Enna_Auth::add_notice( 'error', __( 'Iscrizione non riuscita.', 'ig-enna' ) );
		}
		wp_safe_redirect( remove_query_arg( [ 'ig_enna_action', '_ig_nonce' ] ) );
		exit;
	}

	public static function handle_confirm() {
		if ( empty( $_GET['ig_newsletter_confirm'] ) || empty( $_GET['token'] ) ) {
			return;
		}
		$email = sanitize_email( wp_unslash( $_GET['ig_newsletter_confirm'] ) );
		$token = preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_GET['token'] ) );
		if ( ! $email || ! $token ) { return; }
		$ok = self::confirm( $email, $token );
		IG_Enna_Auth::add_notice( $ok ? 'success' : 'error',
			$ok ? __( 'Iscrizione confermata.', 'ig-enna' ) : __( 'Link di conferma non valido o scaduto.', 'ig-enna' )
		);
	}

	/**
	 * Crea / aggiorna iscrizione (non confermata) e invia email di conferma.
	 *
	 * @return bool
	 */
	public static function subscribe( $email, array $interests = [], $user_id = null ) {
		global $wpdb;
		$table = self::table();
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s", $email ), ARRAY_A );

		$token = wp_generate_password( 32, false );
		$payload = [
			'user_id'   => $user_id ? (int) $user_id : null,
			'interests' => implode( ', ', $interests ),
			'confirmed' => 0,
			'token'     => $token,
		];

		if ( $existing ) {
			// Reset created_at per ripartire il TTL del nuovo token.
			$payload['created_at'] = current_time( 'mysql' );
			$wpdb->update( $table, $payload, [ 'id' => (int) $existing['id'] ] );
		} else {
			$payload['email']      = $email;
			$payload['created_at'] = current_time( 'mysql' );
			$wpdb->insert( $table, $payload );
		}

		self::send_confirm_email( $email, $token );
		IG_Enna_Audit::log( 'newsletter_subscribe', 'newsletter', 0, [ 'email' => $email, 'interests' => $payload['interests'] ] );
		return true;
	}

	const TOKEN_TTL_HOURS = 48;

	public static function confirm( $email, $token ) {
		global $wpdb;
		$table = self::table();
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE email = %s AND token = %s LIMIT 1",
			$email, $token
		), ARRAY_A );
		if ( ! $row ) { return false; }
		// Token TTL: scaduto se la riga non è ancora confermata e created_at è più vecchio di TOKEN_TTL_HOURS.
		if ( empty( $row['confirmed'] ) ) {
			$created = strtotime( (string) $row['created_at'] );
			if ( $created && ( time() - $created ) > self::TOKEN_TTL_HOURS * HOUR_IN_SECONDS ) {
				// Invalida il token e segnala scadenza.
				$wpdb->update( $table, [ 'token' => '' ], [ 'id' => (int) $row['id'] ] );
				return false;
			}
		}
		$wpdb->update( $table, [ 'confirmed' => 1, 'token' => '' ], [ 'id' => (int) $row['id'] ] );
		IG_Enna_Audit::log( 'newsletter_confirm', 'newsletter', (int) $row['id'], [ 'email' => $email ] );
		return true;
	}

	private static function send_confirm_email( $email, $token ) {
		$confirm_url = add_query_arg( [
			'ig_newsletter_confirm' => $email,
			'token'                 => $token,
		], home_url( '/' ) );

		$org = ig_enna_get_setting( 'org_name', 'Informagiovani Enna' );
		$subj = sprintf(
			/* translators: %s = nome organizzazione */
			__( 'Conferma iscrizione · %s', 'ig-enna' ),
			$org
		);
		$body = sprintf(
			__( "Ciao,\n\nper completare l'iscrizione alla newsletter di %1\$s clicca sul link qui sotto:\n\n%2\$s\n\nSe non hai richiesto l'iscrizione, ignora questa email.\n\n— %1\$s", 'ig-enna' ),
			$org,
			$confirm_url
		);
		wp_mail( $email, $subj, $body );
	}

	public static function query( array $args = [] ) {
		global $wpdb;
		$args = array_merge( [
			'confirmed' => null, 'limit' => 100, 'offset' => 0,
		], $args );
		$where = [ '1=1' ]; $params = [];
		if ( $args['confirmed'] !== null ) { $where[] = 'confirmed = %d'; $params[] = (int) $args['confirmed']; }
		$limit  = max( 1, (int) $args['limit'] );
		$offset = max( 0, (int) $args['offset'] );
		$where_sql = implode( ' AND ', $where );
		$table = self::table();
		$sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}";
		$rows = $wpdb->get_results( $params ? $wpdb->prepare( $sql, $params ) : $sql, ARRAY_A );

		$total = (int) $wpdb->get_var( $params
			? $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", $params )
			: "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}"
		);
		return [ 'rows' => $rows ?: [], 'total' => $total ];
	}

	public static function delete( $id ) {
		global $wpdb;
		return false !== $wpdb->delete( self::table(), [ 'id' => (int) $id ], [ '%d' ] );
	}
}
