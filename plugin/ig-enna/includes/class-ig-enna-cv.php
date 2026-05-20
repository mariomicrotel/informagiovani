<?php
/**
 * Curriculum Vitae utente — formato Europass.
 * Dati salvati in user_meta (chiave _ig_enna_cv) come array serializzato.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_CV {

	const META_KEY = '_ig_enna_cv';

	/** Livelli CEFR per lingue. */
	public static function cefr_levels() {
		return [
			''   => __( '— Seleziona —', 'ig-enna' ),
			'A1' => 'A1 · ' . __( 'Base · contatto', 'ig-enna' ),
			'A2' => 'A2 · ' . __( 'Base · sopravvivenza', 'ig-enna' ),
			'B1' => 'B1 · ' . __( 'Autonomo · soglia', 'ig-enna' ),
			'B2' => 'B2 · ' . __( 'Autonomo · progresso', 'ig-enna' ),
			'C1' => 'C1 · ' . __( 'Padronanza · efficacia', 'ig-enna' ),
			'C2' => 'C2 · ' . __( 'Padronanza · perfezione', 'ig-enna' ),
		];
	}

	public static function gender_options() {
		return [
			''  => __( '— Non specificato —', 'ig-enna' ),
			'F' => __( 'Femmina', 'ig-enna' ),
			'M' => __( 'Maschio', 'ig-enna' ),
			'X' => __( 'Altro / preferisco non rispondere', 'ig-enna' ),
		];
	}

	/** Struttura vuota del CV con tutte le sezioni. */
	public static function default_structure() {
		return [
			'personal' => [
				'first_name'  => '',
				'last_name'   => '',
				'birth_date'  => '',
				'birth_place' => '',
				'gender'      => '',
				'nationality' => '',
				'address'     => '',
				'city'        => '',
				'cap'         => '',
				'country'     => 'Italia',
				'phone'       => '',
				'email'       => '',
				'website'     => '',
				'linkedin'    => '',
			],
			'profile'      => '',
			'experience'   => [],
			'education'    => [],
			'languages'    => [],
			'digital_skills'      => '',
			'communication_skills'=> '',
			'organisational_skills' => '',
			'other_skills'        => '',
			'driving_licence'     => '',
			'updated_at'   => '',
		];
	}

	/**
	 * Restituisce il CV dell'utente, merging con i default.
	 *
	 * @return array<string,mixed>
	 */
	public static function get( $user_id ) {
		$user_id = (int) $user_id;
		if ( ! $user_id ) { return self::default_structure(); }
		$saved = get_user_meta( $user_id, self::META_KEY, true );
		if ( ! is_array( $saved ) ) { $saved = []; }
		$default = self::default_structure();
		// Merge superficiale per chiave (le repeatable sono sostituite intere).
		$out = $default;
		foreach ( $default as $k => $v ) {
			if ( ! isset( $saved[ $k ] ) ) { continue; }
			if ( is_array( $v ) && is_array( $saved[ $k ] ) ) {
				// Sezioni associative (personal) → merge field-per-field.
				if ( $k === 'personal' ) {
					$out[ $k ] = array_merge( $v, $saved[ $k ] );
				} else {
					$out[ $k ] = $saved[ $k ];
				}
			} else {
				$out[ $k ] = $saved[ $k ];
			}
		}
		return $out;
	}

	/**
	 * Sanitize + save.
	 *
	 * @param int   $user_id
	 * @param array<string,mixed> $input
	 * @return bool
	 */
	public static function save( $user_id, $input ) {
		$user_id = (int) $user_id;
		if ( ! $user_id ) { return false; }
		$clean = self::sanitize( $input );
		$clean['updated_at'] = current_time( 'mysql' );
		update_user_meta( $user_id, self::META_KEY, $clean );
		return true;
	}

	/** Sanitize completo dell'array CV. */
	public static function sanitize( $input ) {
		$default = self::default_structure();
		if ( ! is_array( $input ) ) { return $default; }
		$out = $default;

		// Sezione personale.
		$p = isset( $input['personal'] ) && is_array( $input['personal'] ) ? $input['personal'] : [];
		$out['personal'] = [
			'first_name'  => isset( $p['first_name'] )  ? sanitize_text_field( $p['first_name'] )  : '',
			'last_name'   => isset( $p['last_name'] )   ? sanitize_text_field( $p['last_name'] )   : '',
			'birth_date'  => isset( $p['birth_date'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $p['birth_date'] ) ? $p['birth_date'] : '',
			'birth_place' => isset( $p['birth_place'] ) ? sanitize_text_field( $p['birth_place'] ) : '',
			'gender'      => isset( $p['gender'] ) && array_key_exists( $p['gender'], self::gender_options() ) ? $p['gender'] : '',
			'nationality' => isset( $p['nationality'] ) ? sanitize_text_field( $p['nationality'] ) : '',
			'address'     => isset( $p['address'] )     ? sanitize_text_field( $p['address'] )     : '',
			'city'        => isset( $p['city'] )        ? sanitize_text_field( $p['city'] )        : '',
			'cap'         => isset( $p['cap'] )         ? sanitize_text_field( $p['cap'] )         : '',
			'country'     => isset( $p['country'] )     ? sanitize_text_field( $p['country'] )     : '',
			'phone'       => isset( $p['phone'] )       ? sanitize_text_field( $p['phone'] )       : '',
			'email'       => isset( $p['email'] )       ? sanitize_email( $p['email'] )            : '',
			'website'     => isset( $p['website'] )     ? esc_url_raw( $p['website'] )             : '',
			'linkedin'    => isset( $p['linkedin'] )    ? esc_url_raw( $p['linkedin'] )            : '',
		];

		$out['profile'] = isset( $input['profile'] ) ? sanitize_textarea_field( $input['profile'] ) : '';

		// Esperienze.
		$out['experience'] = [];
		$rows = isset( $input['experience'] ) && is_array( $input['experience'] ) ? $input['experience'] : [];
		foreach ( $rows as $r ) {
			if ( ! is_array( $r ) ) { continue; }
			$role     = isset( $r['role'] )     ? sanitize_text_field( $r['role'] )     : '';
			$employer = isset( $r['employer'] ) ? sanitize_text_field( $r['employer'] ) : '';
			// Salta righe completamente vuote.
			if ( $role === '' && $employer === '' ) { continue; }
			$out['experience'][] = [
				'from'        => isset( $r['from'] ) && preg_match( '/^\d{4}-\d{2}$/', $r['from'] ) ? $r['from'] : '',
				'to'          => isset( $r['to'] )   && preg_match( '/^\d{4}-\d{2}$/', $r['to'] )   ? $r['to']   : '',
				'current'     => ! empty( $r['current'] ) ? 1 : 0,
				'role'        => $role,
				'employer'    => $employer,
				'city'        => isset( $r['city'] )        ? sanitize_text_field( $r['city'] )    : '',
				'sector'      => isset( $r['sector'] )      ? sanitize_text_field( $r['sector'] )  : '',
				'description' => isset( $r['description'] ) ? sanitize_textarea_field( $r['description'] ) : '',
			];
		}

		// Istruzione.
		$out['education'] = [];
		$rows = isset( $input['education'] ) && is_array( $input['education'] ) ? $input['education'] : [];
		foreach ( $rows as $r ) {
			if ( ! is_array( $r ) ) { continue; }
			$qual   = isset( $r['qualification'] ) ? sanitize_text_field( $r['qualification'] ) : '';
			$school = isset( $r['school'] )        ? sanitize_text_field( $r['school'] )        : '';
			if ( $qual === '' && $school === '' ) { continue; }
			$out['education'][] = [
				'from'          => isset( $r['from'] ) && preg_match( '/^\d{4}-\d{2}$/', $r['from'] ) ? $r['from'] : '',
				'to'            => isset( $r['to'] )   && preg_match( '/^\d{4}-\d{2}$/', $r['to'] )   ? $r['to']   : '',
				'current'       => ! empty( $r['current'] ) ? 1 : 0,
				'qualification' => $qual,
				'school'        => $school,
				'city'          => isset( $r['city'] )     ? sanitize_text_field( $r['city'] ) : '',
				'subjects'      => isset( $r['subjects'] ) ? sanitize_textarea_field( $r['subjects'] ) : '',
				'grade'         => isset( $r['grade'] )    ? sanitize_text_field( $r['grade'] ) : '',
			];
		}

		// Lingue.
		$out['languages'] = [];
		$rows    = isset( $input['languages'] ) && is_array( $input['languages'] ) ? $input['languages'] : [];
		$levels  = array_keys( self::cefr_levels() );
		foreach ( $rows as $r ) {
			if ( ! is_array( $r ) ) { continue; }
			$lang = isset( $r['language'] ) ? sanitize_text_field( $r['language'] ) : '';
			if ( $lang === '' ) { continue; }
			$lvl = function( $key ) use ( $r, $levels ) {
				return ( isset( $r[ $key ] ) && in_array( $r[ $key ], $levels, true ) ) ? $r[ $key ] : '';
			};
			$out['languages'][] = [
				'language'           => $lang,
				'listening'          => $lvl( 'listening' ),
				'reading'            => $lvl( 'reading' ),
				'spoken_interaction' => $lvl( 'spoken_interaction' ),
				'spoken_production'  => $lvl( 'spoken_production' ),
				'writing'            => $lvl( 'writing' ),
			];
		}

		$out['digital_skills']        = isset( $input['digital_skills'] )        ? sanitize_textarea_field( $input['digital_skills'] )        : '';
		$out['communication_skills']  = isset( $input['communication_skills'] )  ? sanitize_textarea_field( $input['communication_skills'] )  : '';
		$out['organisational_skills'] = isset( $input['organisational_skills'] ) ? sanitize_textarea_field( $input['organisational_skills'] ) : '';
		$out['other_skills']          = isset( $input['other_skills'] )          ? sanitize_textarea_field( $input['other_skills'] )          : '';
		$out['driving_licence']       = isset( $input['driving_licence'] )       ? sanitize_text_field( $input['driving_licence'] )           : '';

		return $out;
	}

	/** % di completamento del CV (semplice euristica per dashboard). */
	public static function completion( $user_id ) {
		$cv = self::get( $user_id );
		$score = 0; $total = 8;
		$p = $cv['personal'];
		if ( $p['first_name'] && $p['last_name'] )      { $score++; }
		if ( $p['email'] || $p['phone'] )               { $score++; }
		if ( $p['birth_date'] && $p['nationality'] )    { $score++; }
		if ( $cv['profile'] )                            { $score++; }
		if ( count( $cv['experience'] ) > 0 )            { $score++; }
		if ( count( $cv['education'] ) > 0 )             { $score++; }
		if ( count( $cv['languages'] ) > 0 )             { $score++; }
		if ( $cv['digital_skills'] || $cv['communication_skills'] ) { $score++; }
		return (int) round( $score * 100 / $total );
	}
}
