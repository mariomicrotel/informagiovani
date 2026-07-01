<?php
/**
 * Auth pubblico: login + registrazione + handler form area personale.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Auth {

	const REGISTER_NONCE = 'ig_enna_register';
	const PROFILE_NONCE  = 'ig_enna_profile';
	const TICKET_NONCE   = 'ig_enna_ticket';
	const BOOKING_NONCE  = 'ig_enna_booking';
	const CV_NONCE        = 'ig_enna_cv';
	const EVENT_REG_NONCE = 'ig_enna_event_reg';
	const AVATAR_NONCE    = 'ig_enna_avatar';

	public static function init() {
		// Handler form (POST) prima che WP scriva headers.
		add_action( 'init', [ __CLASS__, 'handle_register' ] );
		add_action( 'init', [ __CLASS__, 'handle_profile' ] );
		add_action( 'init', [ __CLASS__, 'handle_ticket' ] );
		add_action( 'init', [ __CLASS__, 'handle_booking' ] );
		add_action( 'init', [ __CLASS__, 'handle_cv' ] );
		add_action( 'init', [ __CLASS__, 'handle_event_registration' ] );
		add_action( 'init', [ __CLASS__, 'handle_avatar' ] );

		// Blocca l'accesso a /wp-admin/ per gli iscritti pubblici e
		// nasconde la admin bar sul frontend.
		add_action( 'admin_init',       [ __CLASS__, 'block_admin_for_public' ] );
		add_filter( 'show_admin_bar',   [ __CLASS__, 'hide_admin_bar_for_public' ] );
		add_filter( 'login_redirect',   [ __CLASS__, 'login_redirect_public' ], 10, 3 );

		// Assegna ruolo di default ai nuovi utenti registrati.
		add_action( 'register_form', [ __CLASS__, 'add_consent_to_default_form' ] );
	}

	public static function add_consent_to_default_form() {
		echo '<p><label><input type="checkbox" name="ig_consent_priv" required /> ' . esc_html__( 'Ho letto l\'informativa sulla privacy', 'ig-enna' ) . '</label></p>';
	}

	/**
	 * Gestisce POST del form di registrazione [ig_enna_registrazione].
	 */
	public static function handle_register() {
		if ( empty( $_POST['ig_enna_action'] ) || $_POST['ig_enna_action'] !== 'register' ) {
			return;
		}
		if ( is_user_logged_in() ) {
			return;
		}
		if ( ! ig_enna_get_setting( 'enable_public_registration', 1 ) ) {
			self::add_notice( 'error', __( 'La registrazione pubblica non è attiva.', 'ig-enna' ) );
			return;
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::REGISTER_NONCE ) ) {
			self::add_notice( 'error', __( 'Sessione scaduta. Riprova.', 'ig-enna' ) );
			return;
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$pass  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$first = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last  = isset( $_POST['last_name'] )  ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) )  : '';
		$consent = ! empty( $_POST['ig_consent_priv'] );

		if ( ! is_email( $email ) ) { self::add_notice( 'error', __( 'Email non valida.', 'ig-enna' ) ); return; }
		if ( strlen( $pass ) < 8 )  { self::add_notice( 'error', __( 'La password deve essere di almeno 8 caratteri.', 'ig-enna' ) ); return; }
		if ( ! $consent )           { self::add_notice( 'error', __( 'È necessario accettare l\'informativa.', 'ig-enna' ) ); return; }
		if ( email_exists( $email ) || username_exists( $email ) ) {
			self::add_notice( 'error', __( 'Esiste già un account con questa email.', 'ig-enna' ) );
			return;
		}

		$user_id = wp_create_user( $email, $pass, $email );
		if ( is_wp_error( $user_id ) ) {
			self::add_notice( 'error', $user_id->get_error_message() );
			return;
		}

		wp_update_user( [
			'ID'           => $user_id,
			'first_name'   => $first,
			'last_name'    => $last,
			'display_name' => trim( $first . ' ' . $last ) ?: $email,
			'role'         => 'subscriber',
		] );
		update_user_meta( $user_id, 'ig_consent_priv', 1 );
		update_user_meta( $user_id, 'ig_consent_priv_at', current_time( 'mysql' ) );

		// Login automatico.
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
		if ( ! $redirect ) {
			$redirect = get_permalink();
		}
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Gestisce POST del form profilo (da [ig_enna_area_personale]).
	 */
	public static function handle_profile() {
		if ( empty( $_POST['ig_enna_action'] ) || $_POST['ig_enna_action'] !== 'profile' ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::PROFILE_NONCE ) ) {
			self::add_notice( 'error', __( 'Sessione scaduta. Riprova.', 'ig-enna' ) );
			return;
		}

		$user_id = get_current_user_id();
		$input   = isset( $_POST['ig_profile'] ) && is_array( $_POST['ig_profile'] ) ? $_POST['ig_profile'] : [];

		IG_Enna_User_Profile::save( $user_id, $input );
		self::add_notice( 'success', __( 'Profilo aggiornato.', 'ig-enna' ) );

		wp_safe_redirect( add_query_arg( 'ig_tab', 'profilo', get_permalink() ) );
		exit;
	}

	/**
	 * Handler POST per creazione nuovo ticket dall'area personale.
	 */
	public static function handle_ticket() {
		if ( empty( $_POST['ig_enna_action'] ) || $_POST['ig_enna_action'] !== 'ticket_create' ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::TICKET_NONCE ) ) {
			self::add_notice( 'error', __( 'Sessione scaduta. Riprova.', 'ig-enna' ) );
			return;
		}

		$id = IG_Enna_Tickets::create( [
			'user_id'   => get_current_user_id(),
			'subject'   => isset( $_POST['subject'] )  ? wp_unslash( $_POST['subject'] )  : '',
			'message'   => isset( $_POST['message'] )  ? wp_unslash( $_POST['message'] )  : '',
			'area_slug' => isset( $_POST['area_slug'] )? wp_unslash( $_POST['area_slug'] ): '',
			'priority'  => isset( $_POST['priority'] ) ? wp_unslash( $_POST['priority'] ) : 'media',
		] );

		if ( $id ) {
			self::add_notice( 'success', __( 'Richiesta inviata. Ti risponderemo a breve.', 'ig-enna' ) );
		} else {
			self::add_notice( 'error', __( 'Compila oggetto e messaggio.', 'ig-enna' ) );
		}
		wp_safe_redirect( add_query_arg( 'ig_tab', 'richieste', get_permalink() ) );
		exit;
	}

	/**
	 * Handler POST per prenotazione colloquio dal frontend pubblico.
	 */
	public static function handle_booking() {
		if ( empty( $_POST['ig_enna_action'] ) || $_POST['ig_enna_action'] !== 'booking_create' ) {
			return;
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::BOOKING_NONCE ) ) {
			self::add_notice( 'error', __( 'Sessione scaduta. Riprova.', 'ig-enna' ) );
			return;
		}

		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$time = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
			self::add_notice( 'error', __( 'Data o ora non valide.', 'ig-enna' ) );
			return;
		}

		$mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'presenza';
		if ( ! array_key_exists( $mode, IG_Enna_Appointments::modes() ) ) { $mode = 'presenza'; }

		$user_id = get_current_user_id();
		$guest_name  = isset( $_POST['guest_name'] )  ? sanitize_text_field( wp_unslash( $_POST['guest_name'] ) )  : '';
		$guest_email = isset( $_POST['guest_email'] ) ? sanitize_email( wp_unslash( $_POST['guest_email'] ) )     : '';
		$guest_phone = isset( $_POST['guest_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['guest_phone'] ) ) : '';
		$topic       = isset( $_POST['topic'] )       ? sanitize_textarea_field( wp_unslash( $_POST['topic'] ) )   : '';
		$consent     = ! empty( $_POST['ig_consent_priv'] );

		if ( ! $user_id ) {
			if ( ! $guest_name || ! is_email( $guest_email ) ) {
				self::add_notice( 'error', __( 'Inserisci nome ed email validi.', 'ig-enna' ) );
				return;
			}
		}
		if ( ! $consent ) {
			self::add_notice( 'error', __( 'È necessario accettare l\'informativa.', 'ig-enna' ) );
			return;
		}

		$notes_lines = [];
		if ( ! $user_id ) {
			$notes_lines[] = sprintf( '%s · %s', $guest_name, $guest_email );
			if ( $guest_phone ) {
				$notes_lines[] = $guest_phone;
			}
		}
		if ( $topic ) {
			$notes_lines[] = $topic;
		}

		$start_ts = strtotime( $date . ' ' . $time . ':00' );
		$end_ts   = $start_ts + 30 * MINUTE_IN_SECONDS;
		$id = IG_Enna_Appointments::create( [
			'user_id'     => $user_id,
			'operator_id' => 0,
			'slot_start'  => gmdate( 'Y-m-d H:i:s', $start_ts ),
			'slot_end'    => gmdate( 'Y-m-d H:i:s', $end_ts ),
			'mode'        => $mode,
			'status'      => 'requested',
			'notes'       => implode( "\n", $notes_lines ),
		] );

		if ( $id ) {
			IG_Enna_Audit::log( 'appointment_request', 'appointment', $id, [
				'mode'  => $mode,
				'guest' => $user_id ? null : $guest_email,
			] );

			// Notifica staff.
			$org   = ig_enna_get_setting( 'org_name', 'Informagiovani Enna' );
			$staff = ig_enna_get_setting( 'contact_email', '' ) ?: get_option( 'admin_email' );
			wp_mail(
				$staff,
				sprintf( '[%s] Nuova richiesta colloquio · %s', $org, $date . ' ' . $time ),
				sprintf(
					"Nuova richiesta di colloquio.\n\nQuando: %s alle %s\nModalità: %s\nUtente: %s\n\nNote:\n%s",
					$date, $time, $mode,
					$user_id ? wp_get_current_user()->display_name . ' · ' . wp_get_current_user()->user_email : $guest_name . ' · ' . $guest_email,
					implode( "\n", $notes_lines )
				)
			);

			self::add_notice( 'success', __( 'Richiesta inviata. Ti contatteremo per confermare lo slot.', 'ig-enna' ) );
		} else {
			self::add_notice( 'error', __( 'Impossibile salvare la richiesta. Riprova.', 'ig-enna' ) );
		}
		wp_safe_redirect( remove_query_arg( [ 'ig_enna_action', '_ig_nonce' ] ) );
		exit;
	}

	/**
	 * True se l'utente è un iscritto pubblico (subscriber senza
	 * capability operative del plugin). Gli admin/editor e i ruoli
	 * ig_enna_* (staff dello sportello) restano esclusi da questo check.
	 *
	 * @param int|WP_User|null $user
	 * @return bool
	 */
	public static function is_public_subscriber( $user = null ) {
		if ( null === $user ) { $user = wp_get_current_user(); }
		elseif ( is_numeric( $user ) ) { $user = get_userdata( (int) $user ); }
		if ( ! $user || ! ( $user instanceof WP_User ) || 0 === $user->ID ) { return false; }
		// Se ha capability di edit posts o management admin, è staff.
		if ( user_can( $user, 'edit_posts' ) || user_can( $user, 'manage_options' ) ) { return false; }
		// Se ha una capability del plugin (staff), non è pubblico.
		foreach ( ig_enna_capabilities() as $cap ) {
			if ( user_can( $user, $cap ) ) { return false; }
		}
		return true;
	}

	/**
	 * Redirect verso l'area personale del sito se un utente pubblico
	 * (subscriber) apre /wp-admin/. Bypassato per AJAX/REST/cron per non
	 * rompere admin-ajax.php lecito (es. Heartbeat da area personale).
	 */
	public static function block_admin_for_public() {
		if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		if ( ! is_user_logged_in() ) { return; }
		if ( ! self::is_public_subscriber() ) { return; }

		$area = get_page_by_path( 'area-personale' );
		$dest = $area ? get_permalink( $area ) : home_url( '/' );
		wp_safe_redirect( $dest );
		exit;
	}

	/** Nasconde la admin bar in frontend per iscritti pubblici. */
	public static function hide_admin_bar_for_public( $show ) {
		if ( is_user_logged_in() && self::is_public_subscriber() ) {
			return false;
		}
		return $show;
	}

	/**
	 * Al login riuscito, se l'utente è un iscritto pubblico portalo
	 * direttamente all'area personale invece che al profilo admin.
	 */
	public static function login_redirect_public( $redirect_to, $requested_redirect_to, $user ) {
		if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) ) {
			return $redirect_to;
		}
		if ( ! self::is_public_subscriber( $user ) ) {
			return $redirect_to;
		}
		$area = get_page_by_path( 'area-personale' );
		return $area ? get_permalink( $area ) : home_url( '/' );
	}

	/**
	 * Handler POST upload/rimozione foto profilo.
	 */
	public static function handle_avatar() {
		if ( empty( $_POST['ig_enna_action'] ) ) { return; }
		$action = $_POST['ig_enna_action'];
		if ( ! in_array( $action, [ 'avatar_upload', 'avatar_delete' ], true ) ) { return; }
		if ( ! is_user_logged_in() ) { return; }
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::AVATAR_NONCE ) ) {
			self::add_notice( 'error', __( 'Sessione scaduta. Riprova.', 'ig-enna' ) );
			return;
		}
		$uid = get_current_user_id();

		if ( $action === 'avatar_delete' ) {
			IG_Enna_Avatar::delete( $uid );
			self::add_notice( 'success', __( 'Foto profilo rimossa.', 'ig-enna' ) );
		} else {
			if ( empty( $_FILES['avatar'] ) || empty( $_FILES['avatar']['tmp_name'] ) ) {
				self::add_notice( 'error', __( 'Seleziona un file immagine.', 'ig-enna' ) );
			} else {
				$res = IG_Enna_Avatar::handle_upload( $uid, $_FILES['avatar'] );
				if ( is_wp_error( $res ) ) {
					self::add_notice( 'error', $res->get_error_message() );
				} else {
					self::add_notice( 'success', __( 'Foto profilo aggiornata.', 'ig-enna' ) );
				}
			}
		}

		$redir = add_query_arg( 'ig_tab', isset( $_POST['redirect_tab'] ) ? sanitize_key( $_POST['redirect_tab'] ) : 'profilo',
			remove_query_arg( [ 'ig_enna_action', '_ig_nonce' ] ) );
		wp_safe_redirect( $redir );
		exit;
	}

	/**
	 * Handler POST form iscrizione a un evento.
	 */
	public static function handle_event_registration() {
		if ( empty( $_POST['ig_enna_action'] ) || $_POST['ig_enna_action'] !== 'event_register' ) {
			return;
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::EVENT_REG_NONCE ) ) {
			self::add_notice( 'error', __( 'Sessione scaduta. Riprova.', 'ig-enna' ) );
			return;
		}
		if ( empty( $_POST['ig_consent_priv'] ) ) {
			self::add_notice( 'error', __( 'Serve il consenso al trattamento dei dati.', 'ig-enna' ) );
			return;
		}

		$data = [
			'event_id' => isset( $_POST['event_id'] ) ? (int) $_POST['event_id']                            : 0,
			'user_id'  => is_user_logged_in() ? get_current_user_id() : 0,
			'name'     => isset( $_POST['name'] )     ? sanitize_text_field( wp_unslash( $_POST['name'] ) )    : '',
			'email'    => isset( $_POST['email'] )    ? sanitize_email( wp_unslash( $_POST['email'] ) )        : '',
			'phone'    => isset( $_POST['phone'] )    ? sanitize_text_field( wp_unslash( $_POST['phone'] ) )   : '',
			'notes'    => isset( $_POST['notes'] )    ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
		];

		$res = IG_Enna_Event_Registrations::create( $data );
		if ( is_wp_error( $res ) ) {
			self::add_notice( 'error', $res->get_error_message() );
		} elseif ( $res === false ) {
			self::add_notice( 'error', __( 'Dati incompleti o non validi. Verifica evento, nome ed email.', 'ig-enna' ) );
		} else {
			$row = IG_Enna_Event_Registrations::get( $res );
			$evt = get_post( $data['event_id'] );
			$title = $evt ? $evt->post_title : '';
			$msg = ( $row && $row['status'] === 'waitlist' )
				? sprintf(
					/* translators: %s = titolo evento */
					__( 'Iscrizione ricevuta a "%s". L\'evento e\' pieno: sei in lista d\'attesa, ti scriveremo se si libera un posto.', 'ig-enna' ),
					$title
				)
				: sprintf(
					/* translators: %s = titolo evento */
					__( 'Iscrizione confermata a "%s". Riceverai un\'email di riepilogo.', 'ig-enna' ),
					$title
				);
			self::add_notice( 'success', $msg );
		}

		wp_safe_redirect( remove_query_arg( [ 'ig_enna_action', '_ig_nonce' ] ) );
		exit;
	}

	/**
	 * Handler POST del form CV (Europass) in area personale.
	 */
	public static function handle_cv() {
		if ( empty( $_POST['ig_enna_action'] ) || $_POST['ig_enna_action'] !== 'cv_save' ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			return;
		}
		if ( ! isset( $_POST['_ig_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_ig_nonce'] ) ), self::CV_NONCE ) ) {
			self::add_notice( 'error', __( 'Sessione scaduta. Riprova.', 'ig-enna' ) );
			return;
		}
		$payload = isset( $_POST['ig_cv'] ) && is_array( $_POST['ig_cv'] )
			? wp_unslash( $_POST['ig_cv'] )
			: [];
		$ok = IG_Enna_CV::save( get_current_user_id(), $payload );
		self::add_notice( $ok ? 'success' : 'error',
			$ok ? __( 'CV salvato.', 'ig-enna' ) : __( 'Errore salvando il CV.', 'ig-enna' )
		);
		$redir = add_query_arg( 'ig_tab', 'cv', remove_query_arg( [ 'ig_enna_action', '_ig_nonce' ] ) );
		wp_safe_redirect( $redir );
		exit;
	}

	public static function add_notice( $type, $msg ) {
		$notices   = self::get_notices();
		$notices[] = [ 'type' => $type, 'msg' => $msg ];
		// Memorizza via transient legato alla sessione (cookie ID).
		set_transient( 'ig_enna_notices_' . self::session_key(), $notices, 60 );
	}

	public static function get_notices() {
		$n = get_transient( 'ig_enna_notices_' . self::session_key() );
		return is_array( $n ) ? $n : [];
	}

	public static function pop_notices() {
		$key = 'ig_enna_notices_' . self::session_key();
		$n   = get_transient( $key );
		delete_transient( $key );
		return is_array( $n ) ? $n : [];
	}

	private static function session_key() {
		if ( is_user_logged_in() ) {
			return 'u' . get_current_user_id();
		}
		if ( empty( $_COOKIE['ig_enna_sk'] ) ) {
			$sk = wp_generate_password( 12, false );
			// Don't try to set cookie now if headers may be sent.
			if ( ! headers_sent() ) {
				setcookie( 'ig_enna_sk', $sk, 0, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), true );
				$_COOKIE['ig_enna_sk'] = $sk;
			}
			return 'a' . $sk;
		}
		return 'a' . preg_replace( '/[^A-Za-z0-9]/', '', $_COOKIE['ig_enna_sk'] );
	}
}
