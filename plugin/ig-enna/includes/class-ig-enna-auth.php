<?php
/**
 * Auth pubblico: login + registrazione + handler form area personale.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Auth {

	const REGISTER_NONCE = 'ig_enna_register';
	const PROFILE_NONCE  = 'ig_enna_profile';
	const TICKET_NONCE   = 'ig_enna_ticket';

	public static function init() {
		// Handler form (POST) prima che WP scriva headers.
		add_action( 'init', [ __CLASS__, 'handle_register' ] );
		add_action( 'init', [ __CLASS__, 'handle_profile' ] );
		add_action( 'init', [ __CLASS__, 'handle_ticket' ] );

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
