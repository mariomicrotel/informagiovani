<?php
/**
 * Profilo utente — user meta extra del giovane IG.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_User_Profile {

	/** Mappa meta key => sanitizer. */
	public static function fields() {
		return [
			'ig_phone'         => 'sanitize_text_field',
			'ig_age'           => [ __CLASS__, 'sanitize_age' ],
			'ig_city'          => 'sanitize_text_field',
			'ig_status'        => 'sanitize_text_field',
			'ig_interests'     => [ __CLASS__, 'sanitize_csv' ],
			'ig_competenze'    => [ __CLASS__, 'sanitize_csv' ],
			'ig_lingue'        => 'sanitize_text_field',
			'ig_studio_titolo' => 'sanitize_text_field',
			'ig_studio_anno'   => [ __CLASS__, 'sanitize_int' ],
			'ig_studio_voto'   => 'sanitize_text_field',
			'ig_consent_priv'  => [ __CLASS__, 'sanitize_bool' ],
		];
	}

	public static function status_options() {
		return [ 'Studente', 'Universitario', 'Diplomato', 'Neolaureato', 'NEET', 'Disoccupato', 'Occupato', 'Aspirante imprenditore' ];
	}

	public static function get( $user_id ) {
		$out = [];
		foreach ( array_keys( self::fields() ) as $key ) {
			$out[ $key ] = get_user_meta( $user_id, $key, true );
		}
		// Decodifica CSV.
		foreach ( [ 'ig_interests', 'ig_competenze' ] as $k ) {
			if ( ! empty( $out[ $k ] ) && is_string( $out[ $k ] ) ) {
				$out[ $k ] = array_values( array_filter( array_map( 'trim', explode( ',', $out[ $k ] ) ) ) );
			} else {
				$out[ $k ] = [];
			}
		}
		return $out;
	}

	/**
	 * Calcola % completamento del profilo (0-100).
	 */
	public static function completion( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return 0;
		}
		$profile = self::get( $user_id );
		$checks  = [
			! empty( $user->first_name ) || ! empty( $user->display_name ),
			! empty( $user->user_email ),
			! empty( $profile['ig_phone'] ),
			! empty( $profile['ig_city'] ),
			! empty( $profile['ig_age'] ),
			! empty( $profile['ig_status'] ),
			! empty( $profile['ig_interests'] ),
			! empty( $profile['ig_competenze'] ),
			! empty( $profile['ig_studio_titolo'] ),
			! empty( $profile['ig_consent_priv'] ),
		];
		$ok = count( array_filter( $checks ) );
		return (int) round( ( $ok / count( $checks ) ) * 100 );
	}

	/**
	 * Salva da array $_POST validato. Nonce + capability checked dal chiamante.
	 *
	 * @param int   $user_id
	 * @param array $input  raw input (es. $_POST['ig_profile'])
	 */
	public static function save( $user_id, array $input ) {
		foreach ( self::fields() as $key => $sanitizer ) {
			$raw = isset( $input[ $key ] ) ? $input[ $key ] : null;
			if ( $raw === null ) {
				if ( $sanitizer === [ __CLASS__, 'sanitize_bool' ] ) {
					update_user_meta( $user_id, $key, 0 );
				}
				continue;
			}
			if ( is_array( $raw ) && in_array( $sanitizer, [ [ __CLASS__, 'sanitize_csv' ] ], true ) ) {
				$value = self::sanitize_csv( implode( ',', $raw ) );
			} else {
				$value = call_user_func( $sanitizer, wp_unslash( $raw ) );
			}
			if ( $value === '' || $value === null ) {
				delete_user_meta( $user_id, $key );
			} else {
				update_user_meta( $user_id, $key, $value );
			}
		}

		// Aggiorna anche first_name/last_name/display_name se passati.
		if ( ! empty( $input['first_name'] ) ) {
			wp_update_user( [ 'ID' => $user_id, 'first_name' => sanitize_text_field( wp_unslash( $input['first_name'] ) ) ] );
		}
		if ( ! empty( $input['last_name'] ) ) {
			wp_update_user( [ 'ID' => $user_id, 'last_name' => sanitize_text_field( wp_unslash( $input['last_name'] ) ) ] );
		}
		if ( ! empty( $input['display_name'] ) ) {
			wp_update_user( [ 'ID' => $user_id, 'display_name' => sanitize_text_field( wp_unslash( $input['display_name'] ) ) ] );
		}
	}

	/* === Sanitizers === */

	public static function sanitize_age( $v ) {
		$v = (int) $v;
		if ( $v < 14 || $v > 120 ) { return ''; }
		return $v;
	}

	public static function sanitize_int( $v ) {
		$v = (int) $v;
		return $v > 0 ? $v : '';
	}

	public static function sanitize_bool( $v ) {
		return $v ? 1 : 0;
	}

	public static function sanitize_csv( $v ) {
		if ( is_array( $v ) ) { $v = implode( ',', $v ); }
		$parts = array_filter( array_map( 'trim', explode( ',', (string) $v ) ) );
		$parts = array_map( 'sanitize_text_field', $parts );
		return implode( ', ', $parts );
	}
}
