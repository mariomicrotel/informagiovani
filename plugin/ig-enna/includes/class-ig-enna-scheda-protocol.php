<?php
/**
 * Protocollo automatico per schede informative.
 *
 * Per ogni tipologia (Bando, Concorso, Programma, Master, Mobilità,
 * Contributo, ecc.) definisce:
 * - prefisso codice protocollo (es. BAND-2026-001)
 * - stato workflow di default alla creazione
 * - checklist operativa mostrata in un metabox
 * - reminder giornaliero N giorni prima della deadline
 * - opzionale: ruolo notificato in caso di transizione stato
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Scheda_Protocol {

	const CODE_META    = '_ig_enna_codice';
	const STATE_META   = '_ig_enna_workflow_state';
	const TIPO_META    = '_ig_enna_tipo';
	const DEAD_META    = '_ig_enna_deadline';
	const COUNTER_OPT  = 'ig_enna_protocol_counter';           // array: {YEAR}{PREFIX} => next int
	const CRON_HOOK    = 'ig_enna_scheda_deadline_reminders';
	const NOTIFIED_META = '_ig_enna_reminder_sent';            // valore = data reminder inviato

	/**
	 * Configurazione per tipologia. La chiave e' il valore case-insensitive
	 * di _ig_enna_tipo (matching normalizzato). Fallback su 'default'.
	 *
	 * @return array<string,array{prefix:string,workflow:string,reminder_days:int,notify:string,checklist:string[]}>
	 */
	public static function protocols() {
		return [
			'bando' => [
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
			],
			'concorso' => [
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
			],
			'programma' => [
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
			],
			'master' => [
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
			],
			'mobilità' => [
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
			],
			'contributo' => [
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
			],
			'default' => [
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
			],
		];
	}

	/** Normalizza il valore tipologia in una chiave della protocols(). */
	public static function normalize_tipo( $tipo ) {
		$t = strtolower( trim( (string) $tipo ) );
		$t = str_replace( [ 'à', 'á' ], 'à', $t );
		$map = self::protocols();
		if ( isset( $map[ $t ] ) ) { return $t; }
		// Match "parziale": "bando ordinario" → "bando".
		foreach ( array_keys( $map ) as $k ) {
			if ( $k !== 'default' && strpos( $t, $k ) === 0 ) { return $k; }
		}
		return 'default';
	}

	public static function protocol_for( $tipo ) {
		$key = self::normalize_tipo( $tipo );
		$all = self::protocols();
		return $all[ $key ];
	}

	public static function init() {
		add_action( 'save_post_ig_scheda',     [ __CLASS__, 'apply_protocol_on_save' ], 20, 3 );
		add_action( 'add_meta_boxes_ig_scheda',[ __CLASS__, 'register_metabox' ] );

		// Cron reminder deadline (quotidiano).
		add_action( self::CRON_HOOK, [ __CLASS__, 'run_deadline_reminders' ] );
		add_action( 'init',           [ __CLASS__, 'ensure_cron' ] );
	}

	/** Assicura che il cron giornaliero sia schedulato. */
	public static function ensure_cron() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 300, 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * Al primo save di una scheda, assegna:
	 * - _ig_enna_codice progressivo se mancante
	 * - _ig_enna_workflow_state default della tipologia se mancante
	 */
	public static function apply_protocol_on_save( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) { return; }
		if ( $post->post_status === 'auto-draft' ) { return; }

		$tipo    = get_post_meta( $post_id, self::TIPO_META, true );
		$config  = self::protocol_for( $tipo );

		// Codice progressivo se mancante o placeholder.
		$code = trim( (string) get_post_meta( $post_id, self::CODE_META, true ) );
		if ( $code === '' ) {
			$code = self::next_code( $config['prefix'] );
			update_post_meta( $post_id, self::CODE_META, $code );
		}

		// Stato workflow di default se mancante.
		$state = get_post_meta( $post_id, self::STATE_META, true );
		if ( ! $state ) {
			update_post_meta( $post_id, self::STATE_META, $config['workflow'] );
		}

		if ( class_exists( 'IG_Enna_Audit' ) ) {
			IG_Enna_Audit::log(
				$update ? 'scheda_update' : 'scheda_create',
				'scheda',
				(int) $post_id,
				[ 'code' => $code, 'tipo' => $tipo, 'workflow' => $state ?: $config['workflow'] ]
			);
		}
	}

	/**
	 * Restituisce il prossimo codice progressivo per un prefisso, nella
	 * forma PREFIX-YYYY-NNN. Counter salvato in wp_options come array
	 * ig_enna_protocol_counter[YYYY][PREFIX] => int.
	 */
	public static function next_code( $prefix ) {
		$year   = (int) current_time( 'Y' );
		$data   = get_option( self::COUNTER_OPT, [] );
		if ( ! is_array( $data ) ) { $data = []; }
		if ( ! isset( $data[ $year ] ) )              { $data[ $year ] = []; }
		if ( ! isset( $data[ $year ][ $prefix ] ) )   { $data[ $year ][ $prefix ] = 0; }
		$data[ $year ][ $prefix ]++;
		update_option( self::COUNTER_OPT, $data, false );
		return sprintf( '%s-%d-%03d', $prefix, $year, $data[ $year ][ $prefix ] );
	}

	/* =========================================================
	 *  METABOX "Protocollo"
	 * ========================================================= */

	public static function register_metabox() {
		add_meta_box(
			'ig_enna_scheda_protocol',
			__( 'Protocollo automatico', 'ig-enna' ),
			[ __CLASS__, 'render_metabox' ],
			'ig_scheda',
			'side',
			'high'
		);
	}

	public static function render_metabox( $post ) {
		$tipo   = get_post_meta( $post->ID, self::TIPO_META, true );
		$code   = get_post_meta( $post->ID, self::CODE_META, true );
		$state  = get_post_meta( $post->ID, self::STATE_META, true );
		$dead   = get_post_meta( $post->ID, self::DEAD_META, true );
		$config = self::protocol_for( $tipo );
		$states = class_exists( 'IG_Enna_Scheda_Meta' ) ? IG_Enna_Scheda_Meta::workflow_states() : [];
		?>
		<p style="margin-top:0;">
			<strong><?php esc_html_e( 'Codice', 'ig-enna' ); ?>:</strong>
			<?php if ( $code ) : ?>
				<code style="background:#f0f6fc;padding:2px 8px;border-radius:4px;color:#143f7a;font-weight:600;"><?php echo esc_html( $code ); ?></code>
			<?php else : ?>
				<em style="color:#94a3b8;"><?php esc_html_e( 'assegnato al primo salvataggio', 'ig-enna' ); ?></em>
			<?php endif; ?>
		</p>

		<p>
			<strong><?php esc_html_e( 'Tipologia rilevata', 'ig-enna' ); ?>:</strong>
			<?php echo $tipo ? esc_html( $tipo ) : '<em style="color:#94a3b8;">' . esc_html__( 'imposta nel metabox "Dettagli scheda"', 'ig-enna' ) . '</em>'; ?>
			<br>
			<small style="color:#6b7280;">
				<?php
				/* translators: %s = key protocollo */
				printf( esc_html__( 'protocollo applicato: %s', 'ig-enna' ), '<code>' . esc_html( self::normalize_tipo( $tipo ) ) . '</code>' );
				?>
			</small>
		</p>

		<p>
			<strong><?php esc_html_e( 'Stato workflow', 'ig-enna' ); ?>:</strong>
			<?php
			if ( $state && isset( $states[ $state ] ) ) {
				echo '<span class="ig-enna-badge ig-enna-badge--state-' . esc_attr( $state ) . '">' . esc_html( $states[ $state ] ) . '</span>';
			} else {
				echo '<em style="color:#94a3b8;">' . esc_html__( 'default: ', 'ig-enna' ) . esc_html( $config['workflow'] ) . '</em>';
			}
			?>
		</p>

		<p>
			<strong><?php esc_html_e( 'Reminder deadline', 'ig-enna' ); ?>:</strong>
			<?php
			printf(
				/* translators: %d = giorni */
				esc_html( _n( '%d giorno prima', '%d giorni prima', $config['reminder_days'], 'ig-enna' ) ),
				(int) $config['reminder_days']
			);
			?>
			<?php if ( ! $dead ) : ?>
				<br><small style="color:#d97706;"><?php esc_html_e( 'Nessuna deadline impostata → reminder non attivo.', 'ig-enna' ); ?></small>
			<?php endif; ?>
		</p>

		<hr>

		<p style="margin-bottom:6px;"><strong><?php esc_html_e( 'Checklist operativa', 'ig-enna' ); ?></strong></p>
		<ul style="margin:0 0 0 4px; padding-left: 18px; font-size:12px; color:#374151;">
			<?php foreach ( $config['checklist'] as $line ) : ?>
				<li style="margin-bottom:4px;"><?php echo esc_html( $line ); ?></li>
			<?php endforeach; ?>
		</ul>

		<?php if ( ! empty( $config['notify'] ) ) : ?>
			<hr>
			<p style="font-size:12px; color:#6b7280; margin: 0;">
				<?php
				/* translators: %s = ruolo */
				printf( esc_html__( 'Ruolo notificato per approvazione: %s', 'ig-enna' ), '<code>' . esc_html( $config['notify'] ) . '</code>' );
				?>
			</p>
		<?php endif; ?>
		<?php
	}

	/* =========================================================
	 *  CRON REMINDER DEADLINE
	 * ========================================================= */

	/**
	 * Cerca le schede pubblicate con deadline in arrivo (secondo
	 * reminder_days del protocollo) e non ancora notificate. Invia email
	 * al ruolo di notify della tipologia e salva il timestamp del
	 * reminder inviato per evitare duplicati.
	 */
	public static function run_deadline_reminders() {
		$today = current_time( 'Y-m-d' );
		$max_days = 30;
		$posts = get_posts( [
			'post_type'      => 'ig_scheda',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'meta_key'       => self::DEAD_META,
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'     => self::DEAD_META,
					'value'   => [ $today, gmdate( 'Y-m-d', current_time( 'timestamp' ) + $max_days * DAY_IN_SECONDS ) ],
					'compare' => 'BETWEEN',
					'type'    => 'DATE',
				],
			],
		] );

		foreach ( $posts as $post ) {
			$tipo   = get_post_meta( $post->ID, self::TIPO_META, true );
			$config = self::protocol_for( $tipo );
			$dead   = get_post_meta( $post->ID, self::DEAD_META, true );
			$days_left = (int) floor( ( strtotime( $dead . ' 23:59:59' ) - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
			if ( $days_left < 0 || $days_left > $config['reminder_days'] ) {
				continue;
			}
			$already = get_post_meta( $post->ID, self::NOTIFIED_META, true );
			// Evita reinvio se già inviato oggi (o comunque per questa deadline).
			if ( $already && $already === $dead ) { continue; }

			self::send_deadline_email( $post, $days_left, $config );
			update_post_meta( $post->ID, self::NOTIFIED_META, $dead );

			if ( class_exists( 'IG_Enna_Audit' ) ) {
				IG_Enna_Audit::log( 'scheda_deadline_reminder', 'scheda', (int) $post->ID, [ 'days_left' => $days_left, 'deadline' => $dead ] );
			}
		}
	}

	private static function send_deadline_email( $post, $days_left, $config ) {
		$recipients = self::role_emails( $config['notify'] );
		if ( ! $recipients ) {
			// Fallback: admin_email
			$recipients = [ get_option( 'admin_email' ) ];
		}
		$code = get_post_meta( $post->ID, self::CODE_META, true );
		$edit = get_edit_post_link( $post->ID, '' );

		$subject = sprintf(
			/* translators: 1: codice, 2: giorni */
			__( '[IG Enna] Scadenza %1$s tra %2$d giorni', 'ig-enna' ),
			$code ?: '#' . $post->ID,
			$days_left
		);
		$body = sprintf(
			/* translators: 1: titolo, 2: codice, 3: giorni, 4: link edit */
			__( "Promemoria automatico protocollo scheda.\n\nTitolo: %1\$s\nCodice: %2\$s\nScadenza tra %3\$d giorni.\n\nModifica: %4\$s\n\n— Informagiovani Enna", 'ig-enna' ),
			$post->post_title,
			$code,
			$days_left,
			$edit
		);
		foreach ( $recipients as $to ) {
			wp_mail( $to, $subject, $body );
		}
	}

	private static function role_emails( $role ) {
		$users = get_users( [ 'role' => $role, 'fields' => [ 'user_email' ] ] );
		return array_filter( wp_list_pluck( $users, 'user_email' ) );
	}
}
