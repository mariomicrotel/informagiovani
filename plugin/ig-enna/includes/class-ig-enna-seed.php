<?php
/**
 * Seed dati demo: pagine con shortcode + testi introduttivi, schede, eventi,
 * partner, percorsi impresa, news. Idempotente: salta entry già create.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Seed {

	const OPTION_KEY = 'ig_enna_seed_state';

	const NONCE = 'ig_enna_seed_run';

	public static function init() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'ig-enna seed', [ __CLASS__, 'cli_seed' ] );
		}
		add_action( 'admin_post_ig_enna_seed', [ __CLASS__, 'handle_admin_post' ] );
	}

	public static function handle_admin_post() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permesso negato.', 'ig-enna' ), 403 );
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::NONCE ) ) {
			wp_die( esc_html__( 'Nonce non valido.', 'ig-enna' ), 403 );
		}
		$report = self::run();
		$qs = http_build_query( [ 'page' => 'ig-enna', 'ig_seeded' => '1' ] + array_combine(
			array_map( fn( $k ) => 'ig_n_' . $k, array_keys( $report ) ),
			array_values( $report )
		) );
		wp_safe_redirect( admin_url( 'admin.php?' . $qs ) );
		exit;
	}

	/** WP-CLI entry: `wp ig-enna seed [--reset]`. */
	public static function cli_seed( $args, $assoc_args ) {
		if ( ! empty( $assoc_args['reset'] ) ) {
			delete_option( self::OPTION_KEY );
			\WP_CLI::log( 'Seed state reset.' );
		}
		$report = self::run();
		foreach ( $report as $section => $count ) {
			\WP_CLI::log( sprintf( '%s: %d created', $section, $count ) );
		}
		\WP_CLI::success( 'Seed completato.' );
	}

	/**
	 * Esegue tutti i seed e ritorna report.
	 *
	 * @return array<string,int>
	 */
	public static function run() {
		// Forza registrazione CPT/tassonomie se non già fatto.
		if ( ! post_type_exists( 'ig_scheda' ) ) {
			IG_Enna_CPT::register();
		}
		if ( ! taxonomy_exists( 'ig_area' ) ) {
			IG_Enna_Taxonomies::register();
			IG_Enna_Taxonomies::seed();
		}

		$report = [];
		$report['pages']     = self::seed_pages();
		$report['schede']    = self::seed_schede();
		$report['eventi']    = self::seed_eventi();
		$report['partner']   = self::seed_partner();
		$report['percorsi']  = self::seed_percorsi();
		$report['news']      = self::seed_news();

		update_option( self::OPTION_KEY, [
			'last_run' => current_time( 'mysql' ),
			'report'   => $report,
		], false );

		return $report;
	}

	/* =====================================================
	 *  PAGINE CON SHORTCODE + TESTI INTRODUTTIVI
	 * ===================================================== */

	private static function page_specs() {
		return [
			[
				'slug'    => 'home',
				'title'   => 'Home',
				'intro'   => '',
				'short'   => '[ig_enna_home]',
				'front'   => true,
			],
			[
				'slug'    => 'lista-opportunita',
				'title'   => 'Opportunità',
				'intro'   => "Tutte le opportunità per giovani tra i 15 e i 35 anni del territorio di Enna: lavoro, formazione, fare impresa, estero, servizio civile, cultura, diritti e concorsi.\n\nL'elenco è aggiornato quotidianamente dagli operatori dello sportello. Ogni scheda riporta scadenza, requisiti, contributo economico (quando previsto) e link ufficiale per la candidatura. Usa i filtri qui sotto per trovare l'opportunità giusta per te.",
				'short'   => '[ig_enna_opportunita]',
			],
			[
				'slug'    => 'lista-eventi',
				'title'   => 'Eventi',
				'intro'   => "Workshop, incontri di orientamento, laboratori, presentazioni di bandi e iniziative organizzate dall'Informagiovani di Enna e dalle realtà partner del territorio.\n\nGli eventi sono gratuiti e aperti a tutti i giovani interessati. La partecipazione richiede iscrizione: ti aspettiamo.",
				'short'   => '[ig_enna_eventi]',
			],
			[
				'slug'    => 'prenota-colloquio',
				'title'   => 'Prenota un colloquio',
				'intro'   => "Hai bisogno di un confronto personalizzato? Prenota un colloquio gratuito con un operatore dell'Informagiovani.\n\nIl colloquio dura circa 30 minuti e ti aiuta a chiarire dubbi su bandi, percorsi formativi, opportunità di lavoro, idee di impresa o esperienze all'estero. Puoi sceglierlo in presenza presso lo sportello in Piazza Garibaldi o online via videocall.",
				'short'   => '[ig_enna_prenota_colloquio]',
			],
			[
				'slug'    => 'area-personale',
				'title'   => 'Area personale',
				'intro'   => "Accedi alla tua area per salvare le opportunità preferite e ritrovarle in un click, inviare richieste allo sportello tramite ticket, completare il profilo e ricevere suggerimenti su misura, seguire i percorsi attivati con il tuo tutor.\n\nL'accesso è gratuito e i dati sono trattati secondo il GDPR. Puoi cancellare il tuo account in qualsiasi momento.",
				'short'   => '[ig_enna_area_personale]',
			],
			[
				'slug'    => 'iscriviti',
				'title'   => 'Newsletter',
				'intro'   => "Ricevi via email le nuove opportunità nelle aree di tuo interesse: una sola mail a settimana, niente spam, disiscrizione con un click.\n\nScegli i temi che ti interessano: ricevi solo le opportunità rilevanti per te.",
				'short'   => '[ig_enna_newsletter]',
			],
			[
				'slug'    => 'iscrizione-evento',
				'title'   => 'Iscriviti a un evento',
				'intro'   => "Partecipa gratuitamente ai workshop, agli incontri di orientamento e agli eventi organizzati dall'Informagiovani di Enna e dai partner.\n\nScegli l'evento dal menu a tendina qui sotto, compila i tuoi dati e conferma. Ti manderemo un'email di riepilogo con tutti i dettagli su data, luogo (o link videocall) e materiali da portare.",
				'short'   => '[ig_enna_iscrizione_evento]',
			],
			[
				'slug'    => 'partner',
				'title'   => 'Partner',
				'intro'   => "Gli enti, le associazioni e le organizzazioni che collaborano con l'Informagiovani di Enna per offrire servizi, formazione e opportunità ai giovani del territorio.\n\nSe la tua organizzazione opera nel campo dell'orientamento, della formazione, dell'inclusione o dell'imprenditorialità giovanile e vuole entrare nella rete partner, scrivi a info@informagiovani-enna.it.",
				'short'   => '[ig_enna_partner]',
			],
			[
				'slug'    => 'news-blog',
				'title'   => 'News',
				'intro'   => "Le ultime notizie, gli approfondimenti e le storie dello sportello Informagiovani di Enna: nuovi bandi, scadenze importanti, testimonianze di ragazze e ragazzi che hanno colto un'opportunità, approfondimenti sui temi di lavoro, formazione e impresa giovanile.",
				'short'   => '[ig_enna_news]',
			],
		];
	}

	/** Lookup by exact title — sostituisce get_page_by_title deprecato. */
	private static function find_by_title( $post_type, $title ) {
		$q = new WP_Query( [
			'post_type'        => $post_type,
			'post_status'      => 'any',
			'title'            => $title,
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'suppress_filters' => true,
		] );
		return $q->have_posts() ? get_post( $q->posts[0] ) : null;
	}

	private static function seed_pages() {
		$created = 0;
		foreach ( self::page_specs() as $spec ) {
			$existing = get_page_by_path( $spec['slug'] );
			if ( $existing ) { continue; }

			$content = '';
			if ( $spec['intro'] ) {
				$content .= "<!-- wp:paragraph -->\n";
				foreach ( explode( "\n\n", $spec['intro'] ) as $para ) {
					$content .= '<p>' . esc_html( $para ) . "</p>\n";
				}
				$content .= "<!-- /wp:paragraph -->\n\n";
			}
			$content .= $spec['short'];

			$pid = wp_insert_post( [
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $spec['title'],
				'post_name'    => $spec['slug'],
				'post_content' => $content,
			] );
			if ( $pid && ! is_wp_error( $pid ) ) {
				$created++;
				if ( ! empty( $spec['front'] ) ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', $pid );
				}
			}
		}
		return $created;
	}

	/* =====================================================
	 *  SCHEDE INFORMATIVE (una per area)
	 * ===================================================== */

	private static function schede_specs() {
		$today = current_time( 'timestamp' );
		$in    = function ( $days ) use ( $today ) { return gmdate( 'Y-m-d', $today + $days * DAY_IN_SECONDS ); };

		return [
			[
				'title'     => 'Garanzia Giovani Sicilia 2026 — programma operativo regionale',
				'codice'    => 'GG-SIC-26',
				'short'     => 'Percorsi di formazione, tirocinio e accompagnamento al lavoro per NEET 15-29 anni residenti in Sicilia. Indennità mensile, certificazione competenze.',
				'tipo'      => 'Programma',
				'deadline'  => $in( 21 ),
				'contributo'=> '500 € / mese',
				'durata'    => 'fino a 12 mesi',
				'source'    => 'Regione Siciliana — Assessorato Lavoro',
				'src_url'   => 'https://garanziagiovanisiciliana.it/',
				'src_class' => 'ufficiale',
				'area'      => 'lavoro',
				'target'    => [ 'neet', '18-29-anni', 'disoccupati' ],
				'territ'    => 'sicilia',
				'fonte'     => 'ufficiale',
				'content'   => "<h2>Cos'è Garanzia Giovani</h2><p>Il programma offre a chi non studia e non lavora un percorso personalizzato che parte dalla profilazione e arriva a un'offerta concreta di tirocinio, formazione o lavoro.</p><h2>Requisiti</h2><ul><li>Età 15-29 anni compresi</li><li>Residenza in Sicilia</li><li>Non occupati e non inseriti in percorsi formativi</li></ul><h2>Come candidarsi</h2><p>Registrazione sul portale regionale e successivo colloquio presso il CPI di riferimento. Lo sportello Informagiovani offre supporto gratuito nella compilazione della domanda.</p>",
			],
			[
				'title'     => 'Master in Comunicazione Digitale — Università Kore Enna',
				'codice'    => 'KORE-MCD-26',
				'short'     => 'Master annuale di II livello in comunicazione digitale, social media e content strategy. Stage curriculare di 4 mesi in agenzia.',
				'tipo'      => 'Master',
				'deadline'  => $in( 35 ),
				'contributo'=> '3.500 € (borse parziali disponibili)',
                'durata'    => '12 mesi · 60 CFU',
				'source'    => 'Università degli Studi di Enna Kore',
				'src_url'   => 'https://www.unikore.it/',
				'src_class' => 'partner',
				'area'      => 'formazione',
				'target'    => [ 'universitari', 'neolaureati' ],
				'territ'    => 'enna-citta',
				'fonte'     => 'partner',
				'content'   => "<h2>Profilo in uscita</h2><p>Il master forma figure professionali in grado di progettare e gestire la comunicazione digitale di aziende, enti pubblici e organizzazioni del terzo settore.</p><h2>Materie principali</h2><ul><li>Content strategy & copywriting</li><li>Social media management</li><li>SEO/SEM e advertising</li><li>Web analytics</li></ul>",
			],
			[
				'title'     => 'Resto al Sud 2026 — finanziamento giovani imprenditori',
				'codice'    => 'INV-RAS-26',
				'short'     => 'Fino a 200.000 € per nuove imprese e attività libero professionali nel Sud Italia. 50% contributo a fondo perduto, 50% finanziamento agevolato.',
				'tipo'      => 'Bando',
				'deadline'  => '',
				'deadline_label' => 'Sempre aperto',
				'contributo'=> 'fino a 200.000 €',
				'durata'    => 'spese ammissibili 18 mesi',
				'source'    => 'Invitalia',
				'src_url'   => 'https://www.invitalia.it/cosa-facciamo/creiamo-nuove-aziende/resto-al-sud',
				'src_class' => 'ufficiale',
				'area'      => 'impresa',
				'target'    => [ '18-35-anni', 'aspiranti-imprenditori' ],
				'territ'    => 'italia-sud',
				'fonte'     => 'ufficiale',
				'content'   => "<h2>Chi può richiederlo</h2><p>Persone fisiche tra i 18 e i 55 anni che vogliano avviare una nuova attività nelle regioni del Sud Italia.</p><h2>Spese ammissibili</h2><ul><li>Ristrutturazione immobili</li><li>Macchinari e attrezzature</li><li>Programmi informatici</li><li>Spese di gestione (max 20%)</li></ul><h2>Supporto Informagiovani</h2><p>Lo sportello affianca nella preparazione del business plan e nella compilazione della domanda online.</p>",
			],
			[
				'title'     => 'European Solidarity Corps — volontariato in Europa 2026',
				'codice'    => 'ESC-26',
				'short'     => 'Esperienze di volontariato 2-12 mesi in 30+ paesi europei. Vitto, alloggio, trasporti e tasca settimanale interamente coperti.',
				'tipo'      => 'Mobilità',
				'deadline'  => $in( 50 ),
				'contributo'=> 'Spese coperte + tasca 100-150 € / mese',
				'durata'    => '2-12 mesi',
				'source'    => 'Agenzia Nazionale Giovani',
				'src_url'   => 'https://europa.eu/youth/solidarity_it',
				'src_class' => 'ufficiale',
				'area'      => 'estero',
				'target'    => [ '18-29-anni' ],
				'territ'    => 'europa',
				'fonte'     => 'ufficiale',
				'content'   => "<h2>Cos'è il Corpo Europeo di Solidarietà</h2><p>Programma UE che permette ai giovani 18-30 anni di partecipare a progetti di volontariato all'estero su temi sociali, ambientali, culturali.</p><h2>Costi</h2><p>L'organizzazione di accoglienza copre vitto, alloggio, trasporto locale e assicurazione. Il viaggio A/R è rimborsato fino a un massimale che varia per fascia di distanza.</p>",
			],
			[
				'title'     => 'Reddito di Libertà — sostegno donne vittime di violenza',
				'codice'    => 'RDL-26',
				'short'     => 'Contributo economico fino a 500 € / mese per 12 mesi a donne in percorso di fuoriuscita dalla violenza, segnalate dai Centri Antiviolenza.',
				'tipo'      => 'Contributo',
				'deadline'  => '',
				'deadline_label' => 'Sportello aperto',
				'contributo'=> '500 € / mese',
				'durata'    => '12 mesi',
				'source'    => 'INPS · Dipartimento Pari Opportunità',
				'src_url'   => 'https://www.inps.it/',
				'src_class' => 'ufficiale',
				'area'      => 'diritti',
				'target'    => [ '18-35-anni' ],
				'territ'    => 'italia',
				'fonte'     => 'ufficiale',
				'content'   => "<h2>A chi è rivolto</h2><p>Donne, anche con figli, in condizione di povertà che hanno intrapreso un percorso di autonomia certificato dai servizi sociali e dai Centri Antiviolenza.</p><h2>Come accedere</h2><p>La domanda viene presentata dal Comune di residenza per conto della beneficiaria. L'Informagiovani può indirizzarti al servizio competente.</p>",
			],
			[
				'title'     => 'Sillumina 2026 — residenze artistiche per giovani autori',
				'codice'    => 'SIAE-SIL-26',
				'short'     => 'Bandi a sportello per residenze artistiche, produzioni musicali, editoriali e cinematografiche under 35. Finanziamento progetti fino a 50.000 €.',
				'tipo'      => 'Bando',
				'deadline'  => $in( 60 ),
				'contributo'=> 'fino a 50.000 € per progetto',
				'durata'    => 'progetti 12-18 mesi',
				'source'    => 'SIAE — Sillumina',
				'src_url'   => 'https://www.sillumina.it/',
				'src_class' => 'partner',
				'area'      => 'cultura',
				'target'    => [ '18-35-anni' ],
				'territ'    => 'italia',
				'fonte'     => 'partner',
				'content'   => "<p>Sillumina è il piano SIAE per la promozione della cultura nelle aree del Sud Italia. Finanzia residenze, produzioni e percorsi formativi per autori e interpreti under 35 nei settori della musica, dello spettacolo dal vivo, della scrittura, dell'audiovisivo.</p>",
			],
			[
				'title'     => 'Servizio Civile Universale 2026 — bando ordinario',
				'codice'    => 'SCU-26',
				'short'     => 'Esperienza di 12 mesi presso enti del territorio in ambiti come educazione, cultura, ambiente, assistenza. Indennità mensile e certificazione.',
				'tipo'      => 'Programma',
				'deadline'  => $in( 14 ),
				'contributo'=> '507,30 € / mese',
				'durata'    => '12 mesi · 25 ore settimanali',
				'source'    => 'Dipartimento Politiche Giovanili',
				'src_url'   => 'https://www.scelgoilserviziocivile.gov.it/',
				'src_class' => 'ufficiale',
				'area'      => 'civile',
				'target'    => [ '18-29-anni' ],
				'territ'    => 'italia',
				'fonte'     => 'ufficiale',
				'content'   => "<h2>Cosa offre</h2><p>Un anno di esperienza retribuita in un ente convenzionato (Comune, ONLUS, associazione, parrocchia) su progetti di utilità sociale. Riconoscimento delle competenze acquisite per il curriculum.</p><h2>Requisiti</h2><ul><li>18-28 anni e 364 giorni alla data di scadenza</li><li>Cittadinanza italiana o regolarmente soggiornante</li><li>Idoneità fisica per le mansioni richieste dal progetto</li></ul>",
			],
			[
				'title'     => 'Concorso INPS 2026 — 100 funzionari informatici',
				'codice'    => 'INPS-FI-26',
				'short'     => 'Concorso pubblico per 100 posti di funzionario informatico, area Funzionari, INPS. Sede: tutto il territorio nazionale. Stipendio iniziale ~32.000 € lordi/anno.',
				'tipo'      => 'Concorso',
				'deadline'  => $in( 10 ),
				'contributo'=> '~32.000 € / anno lordi',
				'durata'    => 'contratto indeterminato',
				'source'    => 'INPS · Gazzetta Ufficiale 4ª Serie',
				'src_url'   => 'https://www.inps.it/',
				'src_class' => 'ufficiale',
				'area'      => 'concorso',
				'target'    => [ 'universitari', 'neolaureati', '18-35-anni' ],
				'territ'    => 'italia',
				'fonte'     => 'ufficiale',
				'content'   => "<h2>Requisiti</h2><ul><li>Laurea magistrale in Informatica, Ingegneria Informatica o equivalenti</li><li>Cittadinanza italiana o UE</li><li>Idoneità fisica all'impiego</li></ul><h2>Prove</h2><p>Prova scritta unica con domande a risposta multipla su materie tecniche e ordinamento INPS. Eventuale colloquio orale per i candidati ammessi.</p>",
			],
		];
	}

	private static function seed_schede() {
		$created = 0;
		foreach ( self::schede_specs() as $s ) {
			$existing = get_posts( [
				'post_type'   => 'ig_scheda',
				'post_status' => 'any',
				'meta_key'    => '_ig_enna_codice',
				'meta_value'  => $s['codice'],
				'numberposts' => 1,
				'fields'      => 'ids',
			] );
			if ( $existing ) { continue; }

			$pid = wp_insert_post( [
				'post_type'    => 'ig_scheda',
				'post_status'  => 'publish',
				'post_title'   => $s['title'],
				'post_excerpt' => $s['short'],
				'post_content' => $s['content'],
			] );
			if ( ! $pid || is_wp_error( $pid ) ) { continue; }

			update_post_meta( $pid, '_ig_enna_codice',         $s['codice'] );
			update_post_meta( $pid, '_ig_enna_short',          $s['short'] );
			update_post_meta( $pid, '_ig_enna_tipo',           $s['tipo'] );
			update_post_meta( $pid, '_ig_enna_deadline',       $s['deadline'] );
			update_post_meta( $pid, '_ig_enna_deadline_label', $s['deadline_label'] ?? '' );
			update_post_meta( $pid, '_ig_enna_contributo',     $s['contributo'] );
			update_post_meta( $pid, '_ig_enna_durata',         $s['durata'] );
			update_post_meta( $pid, '_ig_enna_source',         $s['source'] );
			update_post_meta( $pid, '_ig_enna_source_url',     $s['src_url'] );
			update_post_meta( $pid, '_ig_enna_source_class',   $s['src_class'] );
			update_post_meta( $pid, '_ig_enna_workflow_state', 'pub' );

			wp_set_object_terms( $pid, [ $s['area']   ], 'ig_area' );
			wp_set_object_terms( $pid, $s['target']     , 'ig_target' );
			wp_set_object_terms( $pid, [ $s['territ'] ], 'ig_territorio' );
			wp_set_object_terms( $pid, [ $s['fonte']  ], 'ig_fonte' );

			$created++;
		}
		return $created;
	}

	/* =====================================================
	 *  EVENTI
	 * ===================================================== */

	private static function eventi_specs() {
		$today = current_time( 'timestamp' );
		$in    = function ( $days ) use ( $today ) { return gmdate( 'Y-m-d', $today + $days * DAY_IN_SECONDS ); };

		return [
			[
				'title'   => 'Open Day Informagiovani — bandi e opportunità 2026',
				'date'    => $in( 7 ),
				'time'    => '16:30',
				'mode'    => 'presenza',
				'place'   => 'Sportello Informagiovani · Piazza Garibaldi 1, Enna',
				'cap'     => 40,
				'url'     => '',
				'area'    => 'lavoro',
				'target'  => '18-35 anni',
				'excerpt' => 'Una panoramica delle principali opportunità di lavoro, formazione e impresa attive nel 2026: Garanzia Giovani, Resto al Sud, Servizio Civile, ESC.',
				'content' => "<p>Pomeriggio informativo aperto a tutti i giovani interessati. Gli operatori dello sportello presenteranno bandi attivi, requisiti, scadenze e modalità di candidatura. Possibilità di colloqui individuali a fine evento.</p><h3>Programma</h3><ul><li>16:30 — Accoglienza</li><li>16:45 — Bandi lavoro e formazione</li><li>17:30 — Resto al Sud e finanziamenti impresa</li><li>18:00 — Q&A e colloqui individuali</li></ul>",
			],
			[
				'title'   => 'Workshop CV e LinkedIn — come farti notare dai recruiter',
				'date'    => $in( 14 ),
				'time'    => '15:00',
				'mode'    => 'presenza',
				'place'   => 'Aula Magna Università Kore, Enna',
				'cap'     => 30,
				'url'     => '',
				'area'    => 'formazione',
				'target'  => 'Universitari · Neolaureati',
				'excerpt' => 'Laboratorio pratico per costruire un CV efficace e ottimizzare il profilo LinkedIn. Porta il tuo portatile.',
				'content' => "<p>Tre ore di lavoro intensivo. Lavoreremo sul tuo CV reale, sulla scrittura della headline LinkedIn e sulle parole chiave che attirano i recruiter del tuo settore.</p><p><strong>Cosa portare:</strong> portatile o tablet, il tuo CV attuale (anche in versione bozza).</p>",
			],
			[
				'title'   => 'Webinar Erasmus+ Mobility — studiare e lavorare in Europa',
				'date'    => $in( 21 ),
				'time'    => '18:00',
				'mode'    => 'online',
				'place'   => '',
				'cap'     => 100,
				'url'     => 'https://meet.example.org/informagiovani-enna-erasmus',
				'area'    => 'estero',
				'target'  => 'Universitari · 18-29 anni',
				'excerpt' => 'Tutto quello che devi sapere su Erasmus+ per studio, tirocinio e volontariato europeo: requisiti, scadenze, contributi.',
				'content' => "<p>Incontro online aperto con la responsabile mobilità internazionale dell'Università Kore e un'ex partecipante Erasmus che racconterà la sua esperienza a Lisbona.</p><p>Il link di accesso verrà inviato via email il giorno dell'evento agli iscritti.</p>",
			],
			[
				'title'   => 'Hackathon delle idee — startup giovani del territorio',
				'date'    => $in( 35 ),
				'time'    => '09:00',
				'mode'    => 'presenza',
				'place'   => 'Polo Tecnologico, Enna Bassa',
				'cap'     => 60,
				'url'     => '',
				'area'    => 'impresa',
				'target'  => '18-35 anni · Aspiranti imprenditori',
				'excerpt' => 'Due giorni per trasformare un\'idea in un prototipo di business. In palio mentorship gratuita per 6 mesi e un percorso di accelerazione.',
				'content' => "<p>L'hackathon riunisce 60 ragazze e ragazzi del territorio per lavorare in team su sfide proposte dalle aziende locali nei settori turismo, agrifood e servizi alla persona. Mentor: imprenditori del territorio, docenti universitari, professionisti.</p><h3>Premi</h3><ul><li>1° classificato: percorso di accelerazione di 6 mesi</li><li>2° classificato: mentorship business 1-to-1 per 3 mesi</li><li>Tutti i partecipanti: attestato e accesso al network alumni</li></ul>",
			],
		];
	}

	private static function seed_eventi() {
		$created = 0;
		foreach ( self::eventi_specs() as $e ) {
			$existing = self::find_by_title( 'ig_evento', $e['title'] );
			if ( $existing ) { continue; }

			$pid = wp_insert_post( [
				'post_type'    => 'ig_evento',
				'post_status'  => 'publish',
				'post_title'   => $e['title'],
				'post_excerpt' => $e['excerpt'],
				'post_content' => $e['content'],
			] );
			if ( ! $pid || is_wp_error( $pid ) ) { continue; }

			update_post_meta( $pid, '_ig_enna_event_date',         $e['date'] );
			update_post_meta( $pid, '_ig_enna_event_time',         $e['time'] );
			update_post_meta( $pid, '_ig_enna_event_mode',         $e['mode'] );
			update_post_meta( $pid, '_ig_enna_event_place',        $e['place'] );
			update_post_meta( $pid, '_ig_enna_event_capacity',     $e['cap'] );
			update_post_meta( $pid, '_ig_enna_event_url',          $e['url'] );
			update_post_meta( $pid, '_ig_enna_event_target_label', $e['target'] );
			update_post_meta( $pid, '_ig_enna_event_status',       'open' );

			wp_set_object_terms( $pid, [ $e['area'] ], 'ig_area' );

			$created++;
		}
		return $created;
	}

	/* =====================================================
	 *  PARTNER
	 * ===================================================== */

	private static function partner_specs() {
		return [
			[
				'title'   => 'Università degli Studi di Enna Kore',
				'type'    => 'universita',
				'area'    => 'formazione',
				'phone'   => '0935 536 111',
				'email'   => 'info@unikore.it',
				'website' => 'https://www.unikore.it',
				'address' => 'Cittadella Universitaria, 94100 Enna',
				'content' => "<p>L'unica università siciliana riconosciuta dal MUR. Offre 15 corsi di laurea triennale e magistrale e oltre 20 master. Convenzione attiva con l'Informagiovani per percorsi di tirocinio e orientamento.</p>",
			],
			[
				'title'   => 'Confcommercio Enna',
				'type'    => 'associazione',
				'area'    => 'impresa',
				'phone'   => '0935 26 011',
				'email'   => 'info@confcommercio-enna.it',
				'website' => 'https://www.confcommercio.it',
				'address' => 'Via Roma 412, 94100 Enna',
				'content' => "<p>Associazione di categoria del commercio, turismo e servizi. Offre consulenza fiscale, formativa e legale per nuovi imprenditori under 35. Partner Informagiovani per percorsi di accompagnamento all'avvio d'impresa.</p>",
			],
			[
				'title'   => 'Caritas Diocesana Piazza Armerina',
				'type'    => 'terzo-settore',
				'area'    => 'diritti',
				'phone'   => '0935 681 234',
				'email'   => 'caritas@diocesipiazza.it',
				'website' => '',
				'address' => 'Via Marconi 22, 94015 Piazza Armerina',
				'content' => "<p>Ente accreditato Servizio Civile Universale e attivo su progetti di inclusione sociale, sostegno alle famiglie e accoglienza migranti.</p>",
			],
			[
				'title'   => 'Eurodesk Enna',
				'type'    => 'pa',
				'area'    => 'estero',
				'phone'   => '0935 40 04 00',
				'email'   => 'eurodesk@informagiovani-enna.it',
				'website' => 'https://www.eurodesk.it',
				'address' => 'Piazza Garibaldi 1, 94100 Enna',
				'content' => "<p>Punto Locale Eurodesk della rete nazionale: informazione e orientamento su mobilità europea, programmi UE per la gioventù, volontariato internazionale.</p>",
			],
		];
	}

	private static function seed_partner() {
		$created = 0;
		foreach ( self::partner_specs() as $p ) {
			$existing = self::find_by_title( 'ig_partner', $p['title'] );
			if ( $existing ) { continue; }

			$pid = wp_insert_post( [
				'post_type'    => 'ig_partner',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_content' => $p['content'],
			] );
			if ( ! $pid || is_wp_error( $pid ) ) { continue; }

			update_post_meta( $pid, '_ig_enna_partner_type',    $p['type'] );
			update_post_meta( $pid, '_ig_enna_partner_area',    $p['area'] );
			update_post_meta( $pid, '_ig_enna_partner_phone',   $p['phone'] );
			update_post_meta( $pid, '_ig_enna_partner_email',   $p['email'] );
			update_post_meta( $pid, '_ig_enna_partner_website', $p['website'] );
			update_post_meta( $pid, '_ig_enna_partner_address', $p['address'] );

			$created++;
		}
		return $created;
	}

	/* =====================================================
	 *  PERCORSI IMPRESA
	 * ===================================================== */

	private static function percorsi_specs() {
		return [
			[
				'title'   => 'Business Plan in 8 settimane',
				'tipo'    => 'impresa',
				'durata'  => '8 settimane · 2 incontri/sett.',
				'ref'     => 'Marco Russo · tutor impresa',
				'fasi'    => "Idea e validazione del mercato\nAnalisi competitor\nDefinizione modello di business (BMC)\nPiano economico-finanziario triennale\nPitch deck e presentazione\nFundraising: bandi e investitori\nLegale e fiscale: forma giuridica e adempimenti\nLancio e prime metriche",
				'content' => "<p>Percorso strutturato per aspiranti imprenditori under 35 che vogliono trasformare un'idea in un progetto presentabile a bandi e investitori. Lavoro individuale con tutor e momenti di gruppo per il confronto tra partecipanti.</p>",
			],
			[
				'title'   => 'Costruisci il tuo CV professionale',
				'tipo'    => 'lavoro',
				'durata'  => '4 settimane · 1 incontro/sett.',
				'ref'     => 'Giulia Bianchi · career coach',
				'fasi'    => "Bilancio di competenze\nObiettivo professionale e mercato target\nStesura CV (versione italiana ed europea)\nLettera di presentazione personalizzabile\nProfilo LinkedIn ottimizzato\nPreparazione al colloquio (simulazioni)",
				'content' => "<p>Quattro settimane per costruire un kit candidatura completo: CV, lettera, LinkedIn e preparazione al colloquio. Pensato per chi cerca il primo lavoro o vuole cambiare settore.</p>",
			],
		];
	}

	private static function seed_percorsi() {
		$created = 0;
		foreach ( self::percorsi_specs() as $p ) {
			$existing = self::find_by_title( 'ig_percorso', $p['title'] );
			if ( $existing ) { continue; }

			$pid = wp_insert_post( [
				'post_type'    => 'ig_percorso',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_content' => $p['content'],
			] );
			if ( ! $pid || is_wp_error( $pid ) ) { continue; }

			update_post_meta( $pid, '_ig_enna_percorso_tipo',      $p['tipo'] );
			update_post_meta( $pid, '_ig_enna_percorso_durata',    $p['durata'] );
			update_post_meta( $pid, '_ig_enna_percorso_referente', $p['ref'] );
			update_post_meta( $pid, '_ig_enna_percorso_fasi',      $p['fasi'] );

			$created++;
		}
		return $created;
	}

	/* =====================================================
	 *  NEWS / BLOG
	 * ===================================================== */

	private static function news_specs() {
		$today = current_time( 'timestamp' );
		$ago   = function ( $days ) use ( $today ) { return gmdate( 'Y-m-d H:i:s', $today - $days * DAY_IN_SECONDS ); };

		return [
			[
				'title'   => 'Servizio Civile 2026: aperto il bando, 14 giorni per candidarsi',
				'date'    => $ago( 1 ),
				'area'    => 'civile',
				'excerpt' => 'Pubblicato in Gazzetta il bando ordinario del Servizio Civile Universale 2026. 50.000 posti in tutta Italia, 1.200 in Sicilia.',
				'content' => "<p>È ufficialmente aperto il bando del Servizio Civile Universale 2026: <strong>50.000 posti</strong> in tutta Italia su progetti che spaziano da educazione e cultura ad ambiente, protezione civile, sport, assistenza.</p><h2>Numeri Sicilia</h2><p>In Sicilia sono disponibili circa 1.200 posti distribuiti su oltre 200 progetti. In provincia di Enna gli enti accreditati propongono percorsi nei settori educativo, culturale e di accompagnamento sociale.</p><h2>Tempi e modalità</h2><p>La candidatura va presentata <strong>esclusivamente online</strong> attraverso la piattaforma DOL (Domande On Line) entro 14 giorni dalla pubblicazione. È possibile candidarsi a un solo progetto.</p><p>Lo sportello Informagiovani offre supporto gratuito nella scelta del progetto e nella compilazione della domanda. Prenota un colloquio.</p>",
			],
			[
				'title'   => 'Resto al Sud: cosa cambia nel 2026 — guida agli aggiornamenti',
				'date'    => $ago( 5 ),
				'area'    => 'impresa',
				'excerpt' => 'Innalzato il limite di età a 55 anni, semplificate le procedure per le società tra professionisti, nuove spese ammissibili in chiave digitale.',
				'content' => "<p>La misura Resto al Sud, gestita da Invitalia, si conferma uno dei principali strumenti di sostegno all'imprenditoria nel Sud Italia. Per il 2026 il legislatore ha introdotto alcune novità rilevanti.</p><h2>Età innalzata a 55 anni</h2><p>Il requisito anagrafico passa da 50 a 55 anni, ampliando la platea dei beneficiari.</p><h2>Società tra professionisti</h2><p>Procedure semplificate per la presentazione delle domande da parte di società tra professionisti (STP) iscritte agli albi.</p><h2>Spese digital</h2><p>Tra le spese ammissibili rientrano ora in modo esplicito software gestionali, e-commerce, attività di digital marketing e formazione tech.</p>",
			],
			[
				'title'   => 'Storia di Carla: dal NEET allo stage in agenzia grazie a Garanzia Giovani',
				'date'    => $ago( 10 ),
				'area'    => 'lavoro',
				'excerpt' => 'Carla, 24 anni, dopo la triennale era ferma da un anno. In 6 mesi: profilazione, formazione su social media, tirocinio retribuito in un\'agenzia di Catania.',
				'content' => "<p>Carla ha 24 anni e una laurea triennale in Scienze della Comunicazione presa nel 2024. Per un anno è stata ferma — niente lavoro, niente nuovo percorso di studi, niente formazione attiva. Una NEET, secondo la definizione tecnica.</p><p>Nel maggio 2025 si è presentata allo sportello Informagiovani per capire come iscriversi a Garanzia Giovani Sicilia. \"Avevo paura della burocrazia,\" racconta. \"L'operatrice mi ha guidata passo dopo passo nella profilazione online e nel primo colloquio al CPI.\"</p><h2>Il percorso</h2><p>Dopo la profilazione e il bilancio di competenze, Carla è stata indirizzata a un corso intensivo di 200 ore su social media management e copywriting, finanziato dalla Regione. A novembre è iniziato il tirocinio retribuito di 6 mesi in un'agenzia di comunicazione di Catania.</p><h2>Oggi</h2><p>Il tirocinio si è trasformato in un contratto a tempo determinato di 12 mesi. \"Sono indipendente,\" dice. \"Senza Garanzia Giovani sarei ancora a casa.\"</p>",
			],
			[
				'title'   => 'Erasmus+: aperti i bandi 2026, ecco come orientarsi',
				'date'    => $ago( 20 ),
				'area'    => 'estero',
				'excerpt' => 'La Commissione europea ha pubblicato il programma di lavoro Erasmus+ 2026. Budget complessivo in crescita del 12%: focus su inclusione e transizione verde.',
				'content' => "<p>La Commissione europea ha presentato il programma di lavoro 2026 di Erasmus+, il principale strumento UE per la mobilità di studenti, tirocinanti, docenti e operatori della formazione.</p><h2>Budget</h2><p>Il budget complessivo cresce del 12% rispetto al 2025, con risorse specifiche per progetti di inclusione (giovani con minori opportunità) e iniziative legate alla transizione ecologica.</p><h2>Le azioni chiave</h2><ul><li><strong>KA1</strong> — Mobilità individuale per studio, tirocinio e formazione</li><li><strong>KA2</strong> — Partenariati di cooperazione tra istituzioni</li><li><strong>KA3</strong> — Sostegno alle politiche giovanili</li></ul><h2>Come informarsi</h2><p>L'Informagiovani di Enna è punto Eurodesk e organizza ogni mese un incontro informativo dedicato a Erasmus+ e mobilità europea. Iscriviti al prossimo webinar.</p>",
			],
		];
	}

	private static function seed_news() {
		$created = 0;
		foreach ( self::news_specs() as $n ) {
			$existing = self::find_by_title( 'ig_news', $n['title'] );
			if ( $existing ) { continue; }

			$pid = wp_insert_post( [
				'post_type'    => 'ig_news',
				'post_status'  => 'publish',
				'post_title'   => $n['title'],
				'post_excerpt' => $n['excerpt'],
				'post_content' => $n['content'],
				'post_date'    => $n['date'],
				'post_date_gmt'=> get_gmt_from_date( $n['date'] ),
			] );
			if ( ! $pid || is_wp_error( $pid ) ) { continue; }

			wp_set_object_terms( $pid, [ $n['area'] ], 'ig_area' );
			$created++;
		}
		return $created;
	}
}
