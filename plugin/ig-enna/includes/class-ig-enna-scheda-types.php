<?php
/**
 * Registry tipologie schede: 7 built-in immutabili + N custom
 * gestibili via admin (Informagiovani → Tipologie schede).
 *
 * Ogni tipologia ha: label, prefix codice protocollo, workflow di default,
 * giorni di reminder pre-deadline, ruolo notificato, checklist operativa,
 * e la field_config che governa label/placeholder/hint dei campi
 * contestuali nell'editor scheda.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Scheda_Types {

	const OPTION = 'ig_enna_types_custom';

	/** Tipologie built-in — chiavi hardcoded, non eliminabili. */
	public static function builtin() {
		return [
			'Bando' => [
				'label'         => __( 'Bando', 'ig-enna' ),
				'prefix'        => 'BAND',
				'workflow'      => 'review',
				'reminder_days' => 7,
				'notify'        => 'ig_enna_responsabile',
				'checklist'     => [
					'Verificata fonte ufficiale (GU / portale ente)',
					'Deadline confermata dalla fonte',
					'Contributo economico/valore verificato',
					'Requisiti eleggibilità copiati integralmente',
					'Link "Vai alla fonte" testato',
				],
				'field_config' => [
					'contributo'     => [ 'label' => __( 'Contributo economico', 'ig-enna' ), 'placeholder' => __( 'es. fino a 30.000 €', 'ig-enna' ), 'hint' => __( 'Importo massimo del beneficio economico previsto.', 'ig-enna' ) ],
					'durata'         => [ 'label' => __( 'Durata progetto', 'ig-enna' ),      'placeholder' => __( 'es. 18 mesi', 'ig-enna' ),         'hint' => '' ],
					'short'          => [ 'placeholder' => __( 'Es. Bando per giovani imprenditori del Sud, contributo fino a 30k…', 'ig-enna' ) ],
					'deadline_label' => [ 'placeholder' => __( 'es. 17 marzo 2026 · 12:00', 'ig-enna' ) ],
				],
			],
			'Concorso' => [
				'label'         => __( 'Concorso', 'ig-enna' ),
				'prefix'        => 'CONC',
				'workflow'      => 'review',
				'reminder_days' => 3,
				'notify'        => 'ig_enna_responsabile',
				'checklist'     => [
					'Bando in Gazzetta Ufficiale allegato',
					'Numero posti e sede lavoro confermati',
					'Requisiti anagrafici e titoli chiari',
					'Materie prove scritta/orale elencate',
					'Compenso lordo/netto specificato',
				],
				'field_config' => [
					'contributo'     => [ 'label' => __( 'Stipendio', 'ig-enna' ),            'placeholder' => __( 'es. 32.000 €/anno lordi', 'ig-enna' ), 'hint' => __( 'Retribuzione lorda annua o mensile.', 'ig-enna' ) ],
					'durata'         => [ 'label' => __( 'Tipo contratto', 'ig-enna' ),       'placeholder' => __( 'es. contratto indeterminato', 'ig-enna' ), 'hint' => '' ],
					'short'          => [ 'placeholder' => __( 'Es. Concorso pubblico per 100 funzionari informatici…', 'ig-enna' ) ],
					'deadline_label' => [ 'placeholder' => __( 'es. 30 giorni dalla pubblicazione in GU', 'ig-enna' ) ],
				],
			],
			'Programma' => [
				'label'         => __( 'Programma', 'ig-enna' ),
				'prefix'        => 'PROG',
				'workflow'      => 'valid',
				'reminder_days' => 14,
				'notify'        => 'ig_enna_editor_schede',
				'checklist'     => [
					'Ente promotore ufficiale (INPS, ANPAL, Regione…)',
					'Requisiti anagrafici + target NEET/età',
					'Indennità mensile verificata',
					'Percorso operativo dettagliato',
				],
				'field_config' => [
					'contributo'     => [ 'label' => __( 'Indennità mensile', 'ig-enna' ),    'placeholder' => __( 'es. 500 € / mese', 'ig-enna' ),      'hint' => __( 'Rimborso o indennità di partecipazione al programma.', 'ig-enna' ) ],
					'durata'         => [ 'label' => __( 'Durata programma', 'ig-enna' ),    'placeholder' => __( 'es. fino a 12 mesi', 'ig-enna' ),   'hint' => '' ],
					'short'          => [ 'placeholder' => __( 'Es. Percorsi personalizzati per giovani NEET…', 'ig-enna' ) ],
					'deadline_label' => [ 'placeholder' => __( 'Sportello sempre aperto', 'ig-enna' ) ],
				],
			],
			'Master' => [
				'label'         => __( 'Master', 'ig-enna' ),
				'prefix'        => 'MAST',
				'workflow'      => 'review',
				'reminder_days' => 21,
				'notify'        => 'ig_enna_editor_schede',
				'checklist'     => [
					'Titolo master + CFU',
					'Ente erogatore (Università riconosciuta)',
					'Costo iscrizione + eventuali borse',
					'Durata e stage curricolare',
					'Sbocchi professionali',
				],
				'field_config' => [
					'contributo'     => [ 'label' => __( 'Costo iscrizione', 'ig-enna' ),    'placeholder' => __( 'es. 3.500 € (borse parziali disponibili)', 'ig-enna' ), 'hint' => __( 'Tassa di iscrizione al master, indicare eventuali borse.', 'ig-enna' ) ],
					'durata'         => [ 'label' => __( 'Durata master', 'ig-enna' ),       'placeholder' => __( 'es. 12 mesi · 60 CFU', 'ig-enna' ), 'hint' => '' ],
					'short'          => [ 'placeholder' => __( 'Es. Master annuale in Comunicazione Digitale…', 'ig-enna' ) ],
					'deadline_label' => [ 'placeholder' => __( 'es. 30 settembre 2026', 'ig-enna' ) ],
				],
			],
			'Mobilità' => [
				'label'         => __( 'Mobilità', 'ig-enna' ),
				'prefix'        => 'MOBI',
				'workflow'      => 'review',
				'reminder_days' => 14,
				'notify'        => 'ig_enna_editor_schede',
				'checklist'     => [
					'Paese/i di destinazione',
					'Durata min-max esperienza',
					'Copertura vitto/alloggio/viaggio',
					'Tasca mensile o pocket money',
					'Requisiti linguistici',
				],
				'field_config' => [
					'contributo'     => [ 'label' => __( 'Copertura spese', 'ig-enna' ),      'placeholder' => __( 'es. Vitto/alloggio + 100 € / mese', 'ig-enna' ), 'hint' => __( 'Cosa copre l\'esperienza all\'estero (viaggio, alloggio, pocket money).', 'ig-enna' ) ],
					'durata'         => [ 'label' => __( 'Durata esperienza', 'ig-enna' ),   'placeholder' => __( 'es. 2-12 mesi', 'ig-enna' ),        'hint' => '' ],
					'short'          => [ 'placeholder' => __( 'Es. Volontariato Corpo Europeo di Solidarietà in Portogallo…', 'ig-enna' ) ],
					'deadline_label' => [ 'placeholder' => __( 'Aperto tutto l\'anno', 'ig-enna' ) ],
				],
			],
			'Contributo' => [
				'label'         => __( 'Contributo', 'ig-enna' ),
				'prefix'        => 'CONT',
				'workflow'      => 'review',
				'reminder_days' => 30,
				'notify'        => 'ig_enna_responsabile',
				'checklist'     => [
					'ISEE massimo o soglia reddito',
					'Importo massimo erogabile',
					'Durata contributo',
					'Ente erogatore (Comune/Regione/Stato)',
					'Modulistica ufficiale allegata o linkata',
				],
				'field_config' => [
					'contributo'     => [ 'label' => __( 'Importo massimo', 'ig-enna' ),      'placeholder' => __( 'es. 500 € / mese', 'ig-enna' ),   'hint' => __( 'Contributo economico massimo erogabile.', 'ig-enna' ) ],
					'durata'         => [ 'label' => __( 'Durata erogazione', 'ig-enna' ),   'placeholder' => __( 'es. 12 mesi', 'ig-enna' ),        'hint' => '' ],
					'short'          => [ 'placeholder' => __( 'Es. Reddito di libertà per donne in percorso di autonomia…', 'ig-enna' ) ],
					'deadline_label' => [ 'placeholder' => __( 'Sportello aperto', 'ig-enna' ) ],
				],
			],
			'Altro' => [
				'label'         => __( 'Altro', 'ig-enna' ),
				'prefix'        => 'IG',
				'workflow'      => 'draft',
				'reminder_days' => 7,
				'notify'        => 'ig_enna_editor_schede',
				'checklist'     => [
					'Titolo chiaro e completo',
					'Descrizione breve (max 200 caratteri)',
					'Area tematica assegnata',
					'Fonte verificata + link',
					'Deadline (o "sempre aperta")',
				],
				'field_config' => [
					'contributo'     => [ 'label' => __( 'Contributo / valore', 'ig-enna' ),  'placeholder' => __( 'es. valore del beneficio', 'ig-enna' ), 'hint' => '' ],
					'durata'         => [ 'label' => __( 'Durata', 'ig-enna' ),                'placeholder' => __( 'es. 6 mesi', 'ig-enna' ),               'hint' => '' ],
					'short'          => [ 'placeholder' => __( 'Sintesi breve (1-2 frasi)…', 'ig-enna' ) ],
					'deadline_label' => [ 'placeholder' => __( 'es. 31 dicembre 2026', 'ig-enna' ) ],
				],
			],
		];
	}

	/** Tipologie custom aggiunte via admin. */
	public static function custom() {
		$c = get_option( self::OPTION, [] );
		return is_array( $c ) ? $c : [];
	}

	/** Tutti i tipi disponibili: built-in + custom. */
	public static function all() {
		return array_merge( self::builtin(), self::custom() );
	}

	public static function is_builtin( $key ) {
		return array_key_exists( $key, self::builtin() );
	}

	public static function get( $key ) {
		$all = self::all();
		return $all[ $key ] ?? null;
	}

	/** Elenco solo delle chiavi per performance. */
	public static function keys() {
		return array_keys( self::all() );
	}

	/**
	 * Salva/aggiorna una tipologia custom. Non permette override di built-in.
	 *
	 * @return true|WP_Error
	 */
	public static function save_custom( $key, array $data ) {
		$key = self::sanitize_key_label( $key );
		if ( ! $key ) {
			return new WP_Error( 'invalid_key', __( 'Nome tipologia mancante.', 'ig-enna' ) );
		}
		if ( self::is_builtin( $key ) ) {
			return new WP_Error( 'builtin_locked', __( 'Non puoi modificare una tipologia di sistema.', 'ig-enna' ) );
		}
		$clean = self::sanitize_data( $key, $data );
		// Verifica unicità prefix.
		foreach ( self::all() as $k => $cfg ) {
			if ( $k === $key ) { continue; }
			if ( strtoupper( $cfg['prefix'] ) === strtoupper( $clean['prefix'] ) ) {
				return new WP_Error( 'prefix_taken', sprintf(
					/* translators: %1$s: prefix, %2$s: nome tipo */
					__( 'Il prefisso "%1$s" è già usato da "%2$s".', 'ig-enna' ),
					$clean['prefix'], $k
				) );
			}
		}
		$custom = self::custom();
		$custom[ $key ] = $clean;
		update_option( self::OPTION, $custom, false );
		return true;
	}

	/** Elimina una custom. Non tocca built-in. */
	public static function delete_custom( $key ) {
		if ( self::is_builtin( $key ) ) { return false; }
		$custom = self::custom();
		if ( ! isset( $custom[ $key ] ) ) { return false; }
		unset( $custom[ $key ] );
		update_option( self::OPTION, $custom, false );
		return true;
	}

	/** Ruoli disponibili per notifica. */
	public static function notify_roles() {
		return [
			'ig_enna_responsabile'   => __( 'Responsabile IG', 'ig-enna' ),
			'ig_enna_editor_schede'  => __( 'Editor schede', 'ig-enna' ),
			'ig_enna_operator'       => __( 'Operatore sportello', 'ig-enna' ),
			'administrator'          => __( 'Amministratore', 'ig-enna' ),
		];
	}

	/** Sanitize + normalizza il nome chiave (label = key). */
	public static function sanitize_key_label( $key ) {
		$key = sanitize_text_field( $key );
		$key = trim( $key );
		return $key;
	}

	/** Sanitize completa di un config custom. */
	public static function sanitize_data( $key, array $data ) {
		$workflows = class_exists( 'IG_Enna_Scheda_Meta' ) ? IG_Enna_Scheda_Meta::workflow_states() : [ 'draft'=>'', 'review'=>'', 'valid'=>'', 'pub'=>'' ];
		$roles     = self::notify_roles();

		$prefix = isset( $data['prefix'] ) ? strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $data['prefix'] ) ) : '';
		if ( strlen( $prefix ) < 2 || strlen( $prefix ) > 6 ) {
			$prefix = strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', $key ) ?: 'IG', 0, 4 ) );
		}

		$workflow = isset( $data['workflow'] ) && array_key_exists( $data['workflow'], $workflows ) ? $data['workflow'] : 'draft';
		$reminder = isset( $data['reminder_days'] ) ? max( 1, min( 90, (int) $data['reminder_days'] ) ) : 7;
		$notify   = isset( $data['notify'] ) && array_key_exists( $data['notify'], $roles ) ? $data['notify'] : 'ig_enna_editor_schede';

		// Checklist: array o multiline string.
		$checklist_in = isset( $data['checklist'] ) ? $data['checklist'] : [];
		if ( is_string( $checklist_in ) ) {
			$lines = preg_split( '/\r\n|\r|\n/', $checklist_in );
		} else {
			$lines = (array) $checklist_in;
		}
		$checklist = [];
		foreach ( $lines as $l ) {
			$l = sanitize_text_field( $l );
			if ( $l !== '' ) { $checklist[] = $l; }
		}
		if ( ! $checklist ) {
			$checklist = [ 'Titolo chiaro', 'Descrizione breve', 'Fonte verificata' ];
		}

		// Field config: label, placeholder, hint per 4 campi.
		$fc_in = isset( $data['field_config'] ) && is_array( $data['field_config'] ) ? $data['field_config'] : [];
		$fc = [];
		foreach ( [ 'contributo', 'durata', 'short', 'deadline_label' ] as $field ) {
			$src = $fc_in[ $field ] ?? [];
			$fc[ $field ] = [
				'label'       => isset( $src['label'] )       ? sanitize_text_field( $src['label'] )       : '',
				'placeholder' => isset( $src['placeholder'] ) ? sanitize_text_field( $src['placeholder'] ) : '',
				'hint'        => isset( $src['hint'] )        ? sanitize_text_field( $src['hint'] )        : '',
			];
		}
		// Short e deadline_label non hanno label (solo placeholder).
		unset( $fc['short']['label'], $fc['short']['hint'], $fc['deadline_label']['label'], $fc['deadline_label']['hint'] );
		// Fallback label per contributo/durata se lasciato vuoto.
		if ( $fc['contributo']['label'] === '' ) { $fc['contributo']['label'] = __( 'Contributo / valore', 'ig-enna' ); }
		if ( $fc['durata']['label'] === '' )     { $fc['durata']['label']     = __( 'Durata', 'ig-enna' ); }

		return [
			'label'         => $key,
			'prefix'        => $prefix,
			'workflow'      => $workflow,
			'reminder_days' => $reminder,
			'notify'        => $notify,
			'checklist'     => $checklist,
			'field_config'  => $fc,
		];
	}
}
