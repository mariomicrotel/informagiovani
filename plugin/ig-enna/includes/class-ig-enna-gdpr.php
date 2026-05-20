<?php
/**
 * GDPR — export/erase dati personali via Tools → Privacy di WordPress.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_GDPR {

	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', [ __CLASS__, 'register_exporters' ] );
		add_filter( 'wp_privacy_personal_data_erasers',   [ __CLASS__, 'register_erasers' ] );
	}

	public static function register_exporters( $exporters ) {
		$exporters['ig-enna-tickets'] = [
			'exporter_friendly_name' => __( 'Ticket Informagiovani', 'ig-enna' ),
			'callback'               => [ __CLASS__, 'export_tickets' ],
		];
		$exporters['ig-enna-appointments'] = [
			'exporter_friendly_name' => __( 'Appuntamenti Informagiovani', 'ig-enna' ),
			'callback'               => [ __CLASS__, 'export_appointments' ],
		];
		$exporters['ig-enna-colloqui'] = [
			'exporter_friendly_name' => __( 'Colloqui Informagiovani', 'ig-enna' ),
			'callback'               => [ __CLASS__, 'export_colloqui' ],
		];
		$exporters['ig-enna-saves'] = [
			'exporter_friendly_name' => __( 'Salvataggi Informagiovani', 'ig-enna' ),
			'callback'               => [ __CLASS__, 'export_saves' ],
		];
		$exporters['ig-enna-newsletter'] = [
			'exporter_friendly_name' => __( 'Newsletter Informagiovani', 'ig-enna' ),
			'callback'               => [ __CLASS__, 'export_newsletter' ],
		];
		$exporters['ig-enna-cv'] = [
			'exporter_friendly_name' => __( 'CV Informagiovani', 'ig-enna' ),
			'callback'               => [ __CLASS__, 'export_cv' ],
		];
		return $exporters;
	}

	public static function register_erasers( $erasers ) {
		$erasers['ig-enna-tickets'] = [
			'eraser_friendly_name' => __( 'Ticket Informagiovani', 'ig-enna' ),
			'callback'             => [ __CLASS__, 'erase_tickets' ],
		];
		$erasers['ig-enna-appointments'] = [
			'eraser_friendly_name' => __( 'Appuntamenti Informagiovani', 'ig-enna' ),
			'callback'             => [ __CLASS__, 'erase_appointments' ],
		];
		$erasers['ig-enna-colloqui'] = [
			'eraser_friendly_name' => __( 'Colloqui Informagiovani', 'ig-enna' ),
			'callback'             => [ __CLASS__, 'erase_colloqui' ],
		];
		$erasers['ig-enna-saves'] = [
			'eraser_friendly_name' => __( 'Salvataggi Informagiovani', 'ig-enna' ),
			'callback'             => [ __CLASS__, 'erase_saves' ],
		];
		$erasers['ig-enna-newsletter'] = [
			'eraser_friendly_name' => __( 'Newsletter Informagiovani', 'ig-enna' ),
			'callback'             => [ __CLASS__, 'erase_newsletter' ],
		];
		$erasers['ig-enna-cv'] = [
			'eraser_friendly_name' => __( 'CV Informagiovani', 'ig-enna' ),
			'callback'             => [ __CLASS__, 'erase_cv' ],
		];
		return $erasers;
	}

	private static function user_id_from_email( $email ) {
		$user = get_user_by( 'email', $email );
		return $user ? (int) $user->ID : 0;
	}

	private static function ok( $items, $done = true ) {
		return [
			'data'           => $items,
			'done'           => (bool) $done,
			'items_removed'  => false,
			'items_retained' => false,
			'messages'       => [],
		];
	}

	private static function ok_eraser( $removed, $retained = 0, $messages = [] ) {
		return [
			'items_removed'  => (bool) $removed,
			'items_retained' => (bool) $retained,
			'messages'       => (array) $messages,
			'done'           => true,
		];
	}

	/* ---------- EXPORTERS ---------- */

	public static function export_tickets( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok( [] ); }
		$res  = IG_Enna_Tickets::query( [ 'user_id' => $uid, 'limit' => 500 ] );
		$data = [];
		foreach ( (array) ( $res['rows'] ?? [] ) as $r ) {
			$data[] = [
				'group_id'    => 'ig-enna-tickets',
				'group_label' => __( 'Ticket Informagiovani', 'ig-enna' ),
				'item_id'     => 'ticket-' . (int) $r['id'],
				'data'        => [
					[ 'name' => __( 'Oggetto',     'ig-enna' ), 'value' => $r['subject']    ?? '' ],
					[ 'name' => __( 'Messaggio',   'ig-enna' ), 'value' => $r['message']    ?? '' ],
					[ 'name' => __( 'Area',        'ig-enna' ), 'value' => $r['area_slug']  ?? '' ],
					[ 'name' => __( 'Priorità',    'ig-enna' ), 'value' => $r['priority']   ?? '' ],
					[ 'name' => __( 'Stato',       'ig-enna' ), 'value' => $r['status']     ?? '' ],
					[ 'name' => __( 'Creato il',   'ig-enna' ), 'value' => $r['created_at'] ?? '' ],
				],
			];
		}
		return self::ok( $data );
	}

	public static function export_appointments( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok( [] ); }
		$rows = IG_Enna_Appointments::query( [ 'user_id' => $uid, 'limit' => 500 ] );
		$data = [];
		foreach ( $rows as $r ) {
			$data[] = [
				'group_id'    => 'ig-enna-appointments',
				'group_label' => __( 'Appuntamenti Informagiovani', 'ig-enna' ),
				'item_id'     => 'appointment-' . (int) $r['id'],
				'data'        => [
					[ 'name' => __( 'Dal',        'ig-enna' ), 'value' => $r['slot_start'] ],
					[ 'name' => __( 'Al',         'ig-enna' ), 'value' => $r['slot_end']   ],
					[ 'name' => __( 'Modalità',   'ig-enna' ), 'value' => $r['mode']       ],
					[ 'name' => __( 'Stato',      'ig-enna' ), 'value' => $r['status']     ],
					[ 'name' => __( 'Note',       'ig-enna' ), 'value' => $r['notes']      ],
					[ 'name' => __( 'Creato il',  'ig-enna' ), 'value' => $r['created_at'] ],
				],
			];
		}
		return self::ok( $data );
	}

	public static function export_colloqui( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok( [] ); }
		$rows = IG_Enna_Colloqui::query( [ 'user_id' => $uid, 'limit' => 500 ] );
		$data = [];
		foreach ( $rows as $r ) {
			$data[] = [
				'group_id'    => 'ig-enna-colloqui',
				'group_label' => __( 'Colloqui Informagiovani', 'ig-enna' ),
				'item_id'     => 'colloquio-' . (int) $r['id'],
				'data'        => [
					[ 'name' => __( 'Data',        'ig-enna' ), 'value' => $r['date']       ],
					[ 'name' => __( 'Area',        'ig-enna' ), 'value' => $r['area_slug']  ],
					[ 'name' => __( 'Esito',       'ig-enna' ), 'value' => $r['outcome']    ],
					[ 'name' => __( 'Next step',   'ig-enna' ), 'value' => $r['next_step']  ],
					[ 'name' => __( 'Registrato', 'ig-enna' ), 'value' => $r['created_at'] ],
				],
			];
		}
		return self::ok( $data );
	}

	public static function export_saves( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok( [] ); }
		global $wpdb;
		$t    = $wpdb->prefix . 'ig_enna_user_saves';
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t} WHERE user_id = %d", $uid ), ARRAY_A );
		$data = [];
		foreach ( (array) $rows as $r ) {
			$title = get_the_title( (int) $r['object_id'] );
			$data[] = [
				'group_id'    => 'ig-enna-saves',
				'group_label' => __( 'Salvataggi Informagiovani', 'ig-enna' ),
				'item_id'     => 'save-' . (int) $r['id'],
				'data'        => [
					[ 'name' => __( 'Tipo',       'ig-enna' ), 'value' => $r['object_type'] ],
					[ 'name' => __( 'Titolo',     'ig-enna' ), 'value' => $title ?: ('#' . $r['object_id']) ],
					[ 'name' => __( 'Salvato il', 'ig-enna' ), 'value' => $r['created_at'] ],
				],
			];
		}
		return self::ok( $data );
	}

	public static function export_newsletter( $email, $page = 1 ) {
		global $wpdb;
		$t   = $wpdb->prefix . 'ig_enna_newsletter_subs';
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE email = %s", $email ), ARRAY_A );
		if ( ! $row ) { return self::ok( [] ); }
		$data = [ [
			'group_id'    => 'ig-enna-newsletter',
			'group_label' => __( 'Newsletter Informagiovani', 'ig-enna' ),
			'item_id'     => 'newsletter-' . (int) $row['id'],
			'data'        => [
				[ 'name' => __( 'Email',      'ig-enna' ), 'value' => $row['email']      ],
				[ 'name' => __( 'Interessi',  'ig-enna' ), 'value' => $row['interests']  ],
				[ 'name' => __( 'Confermata', 'ig-enna' ), 'value' => $row['confirmed'] ? __( 'Sì', 'ig-enna' ) : __( 'No', 'ig-enna' ) ],
				[ 'name' => __( 'Creata il',  'ig-enna' ), 'value' => $row['created_at'] ],
			],
		] ];
		return self::ok( $data );
	}

	/* ---------- ERASERS ---------- */

	public static function erase_tickets( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok_eraser( 0 ); }
		global $wpdb;
		$t = $wpdb->prefix . 'ig_enna_tickets';
		// Anonymize rather than delete (audit/operational integrity).
		$n = (int) $wpdb->query( $wpdb->prepare(
			"UPDATE {$t} SET user_id = 0, subject = '[erased]', message = '[erased]' WHERE user_id = %d", $uid
		) );
		return self::ok_eraser( $n > 0, 0, $n
			? [ sprintf( _n( '%d ticket anonimizzato.', '%d ticket anonimizzati.', $n, 'ig-enna' ), $n ) ]
			: []
		);
	}

	public static function erase_appointments( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok_eraser( 0 ); }
		global $wpdb;
		$t = $wpdb->prefix . 'ig_enna_appointments';
		$n = (int) $wpdb->query( $wpdb->prepare(
			"UPDATE {$t} SET user_id = 0, notes = '[erased]' WHERE user_id = %d", $uid
		) );
		return self::ok_eraser( $n > 0 );
	}

	public static function erase_colloqui( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok_eraser( 0 ); }
		global $wpdb;
		$t = $wpdb->prefix . 'ig_enna_colloqui';
		$n = (int) $wpdb->query( $wpdb->prepare(
			"UPDATE {$t} SET user_id = 0, outcome = '[erased]', next_step = '[erased]' WHERE user_id = %d", $uid
		) );
		return self::ok_eraser( $n > 0 );
	}

	public static function erase_saves( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok_eraser( 0 ); }
		global $wpdb;
		$t = $wpdb->prefix . 'ig_enna_user_saves';
		$n = (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$t} WHERE user_id = %d", $uid ) );
		return self::ok_eraser( $n > 0 );
	}

	public static function export_cv( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok( [] ); }
		$cv = get_user_meta( $uid, IG_Enna_CV::META_KEY, true );
		if ( ! is_array( $cv ) || empty( $cv ) ) { return self::ok( [] ); }

		$rows = [];
		$p    = isset( $cv['personal'] ) ? (array) $cv['personal'] : [];
		foreach ( $p as $k => $v ) {
			if ( $v === '' || $v === null ) { continue; }
			$rows[] = [ 'name' => 'personal.' . $k, 'value' => is_array( $v ) ? wp_json_encode( $v ) : (string) $v ];
		}
		foreach ( [ 'profile', 'digital_skills', 'communication_skills', 'organisational_skills', 'other_skills', 'driving_licence', 'updated_at' ] as $k ) {
			if ( ! empty( $cv[ $k ] ) ) {
				$rows[] = [ 'name' => $k, 'value' => (string) $cv[ $k ] ];
			}
		}
		// Sezioni ripetibili serializzate JSON (per leggibilità).
		foreach ( [ 'experience', 'education', 'languages' ] as $k ) {
			if ( ! empty( $cv[ $k ] ) && is_array( $cv[ $k ] ) ) {
				$rows[] = [ 'name' => $k, 'value' => wp_json_encode( $cv[ $k ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ];
			}
		}

		return self::ok( [ [
			'group_id'    => 'ig-enna-cv',
			'group_label' => __( 'CV Informagiovani', 'ig-enna' ),
			'item_id'     => 'cv-' . $uid,
			'data'        => $rows,
		] ] );
	}

	public static function erase_cv( $email, $page = 1 ) {
		$uid = self::user_id_from_email( $email );
		if ( ! $uid ) { return self::ok_eraser( 0 ); }
		$existed = (bool) get_user_meta( $uid, IG_Enna_CV::META_KEY, true );
		delete_user_meta( $uid, IG_Enna_CV::META_KEY );
		return self::ok_eraser( $existed );
	}

	public static function erase_newsletter( $email, $page = 1 ) {
		global $wpdb;
		$t = $wpdb->prefix . 'ig_enna_newsletter_subs';
		$n = (int) $wpdb->query( $wpdb->prepare( "DELETE FROM {$t} WHERE email = %s", $email ) );
		return self::ok_eraser( $n > 0 );
	}
}
