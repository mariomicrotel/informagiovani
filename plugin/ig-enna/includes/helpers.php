<?php
/**
 * Helper functions condivise. Prefisso ig_enna_.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lista capability custom del plugin.
 *
 * @return string[]
 */
function ig_enna_capabilities() {
	return [
		'ig_enna_manage',
		'ig_enna_edit_schede',
		'ig_enna_publish_schede',
		'ig_enna_manage_tickets',
		'ig_enna_manage_events',
		'ig_enna_manage_partners',
		'ig_enna_view_reports',
		'ig_enna_export_data',
	];
}

/**
 * Aree tematiche di base (slug => label).
 *
 * @return array<string,string>
 */
function ig_enna_default_areas() {
	return [
		'lavoro'     => __( 'Lavoro', 'ig-enna' ),
		'formazione' => __( 'Formazione', 'ig-enna' ),
		'impresa'    => __( 'Fare Impresa', 'ig-enna' ),
		'estero'     => __( 'Estero & Mobilità', 'ig-enna' ),
		'diritti'    => __( 'Diritti', 'ig-enna' ),
		'cultura'    => __( 'Cultura', 'ig-enna' ),
		'civile'     => __( 'Servizio Civile', 'ig-enna' ),
		'concorso'   => __( 'Concorsi', 'ig-enna' ),
	];
}

/**
 * Target di base.
 *
 * @return string[]
 */
function ig_enna_default_targets() {
	return [
		'Universitari',
		'Neolaureati',
		'NEET',
		'18–29 anni',
		'18–35 anni',
		'Disoccupati',
		'Aspiranti imprenditori',
		'Studenti',
		'Famiglie ISEE',
	];
}

/**
 * Territori di base.
 *
 * @return string[]
 */
function ig_enna_default_territories() {
	return [
		'Enna città',
		'Provincia di Enna',
		'Sicilia',
		'Italia',
		'Italia · Sud',
		'Europa',
	];
}

/**
 * Fonti di base.
 *
 * @return string[]
 */
function ig_enna_default_sources() {
	return [ 'Ufficiale', 'Partner', 'Verificata' ];
}

/**
 * Settings default.
 *
 * @return array<string,mixed>
 */
function ig_enna_default_settings() {
	return [
		'org_name'                  => 'Informagiovani Enna',
		'contact_email'             => get_option( 'admin_email' ),
		'enable_public_registration'=> 1,
		'default_sla_hours'         => 48,
		'enable_audit_log'          => 1,
		'delete_data_on_uninstall'  => 0,
	];
}

/**
 * Sanitize callback per ig_enna_settings.
 *
 * @param mixed $input
 * @return array<string,mixed>
 */
function ig_enna_sanitize_settings( $input ) {
	$defaults = ig_enna_default_settings();
	$input    = is_array( $input ) ? $input : [];
	$out      = [];

	$out['org_name']                   = isset( $input['org_name'] ) ? sanitize_text_field( $input['org_name'] ) : $defaults['org_name'];
	$out['contact_email']              = isset( $input['contact_email'] ) ? sanitize_email( $input['contact_email'] ) : $defaults['contact_email'];
	$out['enable_public_registration'] = ! empty( $input['enable_public_registration'] ) ? 1 : 0;
	$out['default_sla_hours']          = isset( $input['default_sla_hours'] ) ? max( 1, (int) $input['default_sla_hours'] ) : $defaults['default_sla_hours'];
	$out['enable_audit_log']           = ! empty( $input['enable_audit_log'] ) ? 1 : 0;
	$out['delete_data_on_uninstall']   = ! empty( $input['delete_data_on_uninstall'] ) ? 1 : 0;

	return $out;
}

/**
 * Helper centrale per recuperare una setting.
 *
 * @param string $key
 * @param mixed  $fallback
 * @return mixed
 */
function ig_enna_get_setting( $key, $fallback = null ) {
	$opts = wp_parse_args( get_option( 'ig_enna_settings', [] ), ig_enna_default_settings() );
	return array_key_exists( $key, $opts ) ? $opts[ $key ] : $fallback;
}

/**
 * Contenuti default della home page (corrispondenti agli hardcoded
 * originali). Usati dalla pagina admin "Home page" e da home.php
 * come fallback se l'opzione non è ancora salvata.
 *
 * @return array<string,mixed>
 */
function ig_enna_default_home() {
	return [
		'hero' => [
			'chip'               => __( 'Servizio del Comune di Enna', 'ig-enna' ),
			'title_html'         => __( 'Il punto di accesso alle <em>opportunità</em> per i giovani di Enna', 'ig-enna' ),
			'lead'               => __( 'Lavoro, formazione, impresa, estero, concorsi, eventi e servizi utili — verificati dagli operatori dello sportello, in un\'unica piattaforma.', 'ig-enna' ),
			'search_placeholder' => __( 'Cerca lavoro, corsi, concorsi, bonus, eventi…', 'ig-enna' ),
			'popular'            => [
				__( 'Servizio civile', 'ig-enna' ),
				__( 'Erasmus+', 'ig-enna' ),
				__( 'Resto al Sud', 'ig-enna' ),
				__( 'Concorsi Enna', 'ig-enna' ),
				__( 'Bonus cultura', 'ig-enna' ),
			],
		],
		'quickpaths_bg_image_id' => 0,
		'quickpaths' => [
			[ 'area' => 'lavoro',     'image_id' => 0, 'title' => __( 'Cerco lavoro', 'ig-enna' ),              'desc' => __( 'Offerte, CPI, tirocini retribuiti e accompagnamento alla candidatura.', 'ig-enna' ) ],
			[ 'area' => 'formazione', 'image_id' => 0, 'title' => __( 'Voglio formarmi', 'ig-enna' ),           'desc' => __( 'Corsi, borse di studio, ITS, dottorati e percorsi professionalizzanti.', 'ig-enna' ) ],
			[ 'area' => 'impresa',    'image_id' => 0, 'title' => __( 'Voglio aprire un\'impresa', 'ig-enna' ), 'desc' => __( 'Business plan, microcredito, bandi giovani e accompagnamento dedicato.', 'ig-enna' ) ],
			[ 'area' => 'estero',     'image_id' => 0, 'title' => __( 'Voglio andare all\'estero', 'ig-enna' ), 'desc' => __( 'Erasmus+, Corpo Europeo di Solidarietà, work&travel e mobilità internazionale.', 'ig-enna' ) ],
			[ 'area' => 'concorso',   'image_id' => 0, 'title' => __( 'Cerco un concorso', 'ig-enna' ),         'desc' => __( 'Concorsi di Comune, Regione, Stato ed enti partecipati a misura di NEET.', 'ig-enna' ) ],
			[ 'area' => 'diritti',    'image_id' => 0, 'title' => __( 'Ho bisogno di un servizio', 'ig-enna' ), 'desc' => __( 'Salute, casa, diritti, supporto psicologico, residenza, ISEE e bonus.', 'ig-enna' ) ],
		],
		'howit' => [
			[ 'title' => __( 'Cerca un\'opportunità', 'ig-enna' ),     'desc' => __( 'Esplora schede informative su lavoro, corsi, concorsi e bandi filtrate per età, interessi e territorio.', 'ig-enna' ) ],
			[ 'title' => __( 'Salva ciò che ti interessa', 'ig-enna' ),'desc' => __( 'Tieni traccia di scadenze, requisiti e documenti. Ricevi promemoria automatici prima della chiusura.', 'ig-enna' ) ],
			[ 'title' => __( 'Prenota un colloquio', 'ig-enna' ),      'desc' => __( 'Parla con un operatore in presenza, in videochiamata o al telefono. Lo sportello è gratuito.', 'ig-enna' ) ],
			[ 'title' => __( 'Costruisci il tuo percorso', 'ig-enna' ),'desc' => __( 'Insieme agli operatori definisci obiettivi, azioni e tappe. Tieni tutto nella tua area personale.', 'ig-enna' ) ],
		],
		'howit_intro' => [
			'eyebrow' => __( 'In 4 passi', 'ig-enna' ),
			'title_html' => __( 'Come funziona il <em>servizio</em>', 'ig-enna' ),
			'lead'   => __( 'Da un\'idea generica a un percorso costruito su misura con i tuoi operatori dello sportello. Tutto in un\'unica piattaforma, gratuita e pubblica.', 'ig-enna' ),
			'cta_label' => __( 'Crea il tuo profilo', 'ig-enna' ),
		],
		'cta' => [
			'eyebrow' => __( 'Orientamento personalizzato', 'ig-enna' ),
			'title'   => __( 'Hai bisogno di un confronto?', 'ig-enna' ),
			'lead'    => __( 'Parla con uno dei nostri operatori. Insieme analizziamo la tua situazione, definiamo un percorso e ti accompagniamo nelle candidature.', 'ig-enna' ),
			'modes_label' => __( 'Modalità colloquio', 'ig-enna' ),
			'modes' => [
				[ 'icon' => '👤', 'label' => __( 'In presenza',   'ig-enna' ), 'detail' => __( 'Sportello in Piazza Garibaldi, 1', 'ig-enna' ) ],
				[ 'icon' => '🎥', 'label' => __( 'Videochiamata', 'ig-enna' ), 'detail' => __( 'Google Meet o Zoom · 30 min',    'ig-enna' ) ],
				[ 'icon' => '📞', 'label' => __( 'Telefono',      'ig-enna' ), 'detail' => '0935 40 04 00' ],
				[ 'icon' => '✉️', 'label' => __( 'Email',         'ig-enna' ), 'detail' => __( 'Risposta entro 48 ore', 'ig-enna' ) ],
			],
		],
	];
}

/**
 * Restituisce i contenuti home, mergiando i valori salvati con i default.
 *
 * @return array<string,mixed>
 */
/**
 * URL dell'immagine per un box "Percorso rapido". Se l'utente ha
 * caricato un'immagine via media library la usa; altrimenti restituisce
 * il placeholder SVG dell'area.
 *
 * @param array<string,mixed> $row Riga del quickpath ('image_id', 'area').
 * @return string URL assoluto.
 */
/**
 * URL dell'immagine di sfondo unica per la griglia "Percorsi rapidi".
 * Se non è stato caricato nulla, ritorna il placeholder wide.
 *
 * @return string URL assoluto.
 */
function ig_enna_quickpaths_bg_image() {
	$home = ig_enna_get_home();
	$id   = isset( $home['quickpaths_bg_image_id'] ) ? (int) $home['quickpaths_bg_image_id'] : 0;
	if ( $id > 0 ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) { return $url; }
	}
	return IG_ENNA_URL . 'assets/images/quickpaths-bg.svg';
}

function ig_enna_quickpath_image( $row ) {
	$id = isset( $row['image_id'] ) ? (int) $row['image_id'] : 0;
	if ( $id > 0 ) {
		$url = wp_get_attachment_image_url( $id, 'large' );
		if ( $url ) { return $url; }
	}
	$area    = isset( $row['area'] ) ? sanitize_title( $row['area'] ) : '';
	$allowed = [ 'lavoro', 'formazione', 'impresa', 'estero', 'diritti', 'cultura', 'civile', 'concorso' ];
	$slug    = in_array( $area, $allowed, true ) ? $area : 'lavoro';
	return IG_ENNA_URL . 'assets/images/quickpaths/' . $slug . '.svg';
}

function ig_enna_get_home() {
	$saved   = get_option( 'ig_enna_home', [] );
	$default = ig_enna_default_home();
	if ( ! is_array( $saved ) ) { return $default; }
	// Merge profondo per ogni sezione, preservando struttura.
	$out = $default;
	foreach ( $default as $section => $value ) {
		if ( ! isset( $saved[ $section ] ) ) { continue; }
		// Array di item ripetuti (quickpaths, howit, popular, cta.modes): sovrascrivi se l'utente ha salvato qualcosa.
		if ( in_array( $section, [ 'quickpaths', 'howit' ], true ) && is_array( $saved[ $section ] ) ) {
			$out[ $section ] = $saved[ $section ];
			continue;
		}
		// Sezioni associative: merge field-per-field.
		if ( is_array( $value ) && is_array( $saved[ $section ] ) ) {
			$out[ $section ] = array_merge( $value, $saved[ $section ] );
		}
	}
	return $out;
}

/**
 * Sanitize per option ig_enna_home. Mantiene la struttura del default.
 *
 * @param mixed $input
 * @return array<string,mixed>
 */
function ig_enna_sanitize_home( $input ) {
	$default = ig_enna_default_home();
	if ( ! is_array( $input ) ) { return $default; }
	$out = [];

	// Immagine di sfondo unica della griglia.
	$out['quickpaths_bg_image_id'] = isset( $input['quickpaths_bg_image_id'] )
		? max( 0, (int) $input['quickpaths_bg_image_id'] )
		: 0;

	// Hero.
	$hero = isset( $input['hero'] ) && is_array( $input['hero'] ) ? $input['hero'] : [];
	$out['hero'] = [
		'chip'               => isset( $hero['chip'] )               ? sanitize_text_field( $hero['chip'] )               : $default['hero']['chip'],
		'title_html'         => isset( $hero['title_html'] )         ? wp_kses_post( $hero['title_html'] )                 : $default['hero']['title_html'],
		'lead'               => isset( $hero['lead'] )               ? sanitize_textarea_field( $hero['lead'] )            : $default['hero']['lead'],
		'search_placeholder' => isset( $hero['search_placeholder'] ) ? sanitize_text_field( $hero['search_placeholder'] ) : $default['hero']['search_placeholder'],
		'popular'            => [],
	];
	if ( isset( $hero['popular'] ) ) {
		$lines = is_array( $hero['popular'] )
			? $hero['popular']
			: preg_split( '/\r\n|\r|\n/', (string) $hero['popular'] );
		foreach ( (array) $lines as $line ) {
			$v = sanitize_text_field( $line );
			if ( $v !== '' ) { $out['hero']['popular'][] = $v; }
		}
	}
	if ( ! $out['hero']['popular'] ) { $out['hero']['popular'] = $default['hero']['popular']; }

	// Quickpaths.
	$out['quickpaths'] = [];
	$qp_in = isset( $input['quickpaths'] ) && is_array( $input['quickpaths'] ) ? $input['quickpaths'] : [];
	foreach ( $qp_in as $row ) {
		if ( ! is_array( $row ) ) { continue; }
		$title = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
		if ( $title === '' ) { continue; }
		$out['quickpaths'][] = [
			'area'     => isset( $row['area'] )     ? sanitize_title( $row['area'] )         : '',
			'image_id' => isset( $row['image_id'] ) ? max( 0, (int) $row['image_id'] )       : 0,
			'title'    => $title,
			'desc'     => isset( $row['desc'] )     ? sanitize_textarea_field( $row['desc'] ) : '',
		];
	}
	if ( ! $out['quickpaths'] ) { $out['quickpaths'] = $default['quickpaths']; }

	// Howit steps.
	$out['howit'] = [];
	$ho_in = isset( $input['howit'] ) && is_array( $input['howit'] ) ? $input['howit'] : [];
	foreach ( $ho_in as $row ) {
		if ( ! is_array( $row ) ) { continue; }
		$title = isset( $row['title'] ) ? sanitize_text_field( $row['title'] ) : '';
		if ( $title === '' ) { continue; }
		$out['howit'][] = [
			'title' => $title,
			'desc'  => isset( $row['desc'] ) ? sanitize_textarea_field( $row['desc'] ) : '',
		];
	}
	if ( ! $out['howit'] ) { $out['howit'] = $default['howit']; }

	// Howit intro.
	$hi = isset( $input['howit_intro'] ) && is_array( $input['howit_intro'] ) ? $input['howit_intro'] : [];
	$out['howit_intro'] = [
		'eyebrow'    => isset( $hi['eyebrow'] )    ? sanitize_text_field( $hi['eyebrow'] )    : $default['howit_intro']['eyebrow'],
		'title_html' => isset( $hi['title_html'] ) ? wp_kses_post( $hi['title_html'] )        : $default['howit_intro']['title_html'],
		'lead'       => isset( $hi['lead'] )       ? sanitize_textarea_field( $hi['lead'] )   : $default['howit_intro']['lead'],
		'cta_label'  => isset( $hi['cta_label'] )  ? sanitize_text_field( $hi['cta_label'] )  : $default['howit_intro']['cta_label'],
	];

	// CTA.
	$cta = isset( $input['cta'] ) && is_array( $input['cta'] ) ? $input['cta'] : [];
	$out['cta'] = [
		'eyebrow'     => isset( $cta['eyebrow'] )     ? sanitize_text_field( $cta['eyebrow'] )     : $default['cta']['eyebrow'],
		'title'       => isset( $cta['title'] )       ? sanitize_text_field( $cta['title'] )       : $default['cta']['title'],
		'lead'        => isset( $cta['lead'] )        ? sanitize_textarea_field( $cta['lead'] )    : $default['cta']['lead'],
		'modes_label' => isset( $cta['modes_label'] ) ? sanitize_text_field( $cta['modes_label'] ) : $default['cta']['modes_label'],
		'modes'       => [],
	];
	$modes_in = isset( $cta['modes'] ) && is_array( $cta['modes'] ) ? $cta['modes'] : [];
	foreach ( $modes_in as $row ) {
		if ( ! is_array( $row ) ) { continue; }
		$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
		if ( $label === '' ) { continue; }
		$out['cta']['modes'][] = [
			'icon'   => isset( $row['icon'] )   ? sanitize_text_field( $row['icon'] )   : '',
			'label'  => $label,
			'detail' => isset( $row['detail'] ) ? sanitize_text_field( $row['detail'] ) : '',
		];
	}
	if ( ! $out['cta']['modes'] ) { $out['cta']['modes'] = $default['cta']['modes']; }

	return $out;
}
