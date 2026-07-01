<?php
/**
 * Foto profilo utente: gestione upload/delete + rendering.
 * Attachment_id salvato in user_meta _ig_enna_avatar_id.
 * Condiviso tra profilo utente e CV Europass.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Avatar {

	const META_KEY  = '_ig_enna_avatar_id';
	const MAX_BYTES = 5 * 1024 * 1024; // 5 MB
	const MIMES     = [
		'image/jpeg' => 'jpg',
		'image/png'  => 'png',
		'image/webp' => 'webp',
	];

	public static function get_id( $user_id ) {
		return (int) get_user_meta( (int) $user_id, self::META_KEY, true );
	}

	public static function get_url( $user_id, $size = 'medium' ) {
		$id = self::get_id( $user_id );
		if ( ! $id ) { return ''; }
		$url = wp_get_attachment_image_url( $id, $size );
		return $url ?: '';
	}

	public static function set( $user_id, $attachment_id ) {
		update_user_meta( (int) $user_id, self::META_KEY, (int) $attachment_id );
	}

	/**
	 * Elimina il collegamento (e opzionalmente il file). Di default
	 * elimina anche l'allegato per non lasciare foto orfane.
	 */
	public static function delete( $user_id, $delete_file = true ) {
		$id = self::get_id( $user_id );
		delete_user_meta( (int) $user_id, self::META_KEY );
		if ( $delete_file && $id ) {
			wp_delete_attachment( $id, true );
		}
	}

	/**
	 * Carica un file da $_FILES per l'utente indicato. Ritorna
	 * attachment_id oppure WP_Error.
	 *
	 * @return int|WP_Error
	 */
	public static function handle_upload( $user_id, $file ) {
		if ( empty( $file ) || ! is_array( $file ) ) {
			return new WP_Error( 'no_file', __( 'Nessun file ricevuto.', 'ig-enna' ) );
		}
		if ( ! empty( $file['error'] ) ) {
			return new WP_Error( 'upload_error', __( 'Errore durante il caricamento.', 'ig-enna' ) );
		}
		if ( (int) $file['size'] > self::MAX_BYTES ) {
			return new WP_Error( 'too_big', sprintf(
				/* translators: %s = max MB */
				__( 'Il file supera %s MB.', 'ig-enna' ),
				(int) ( self::MAX_BYTES / 1024 / 1024 )
			) );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		$mime  = $check['type'] ?? '';
		if ( ! $mime || ! array_key_exists( $mime, self::MIMES ) ) {
			return new WP_Error( 'bad_type', __( 'Formato non supportato. Usa JPG, PNG o WEBP.', 'ig-enna' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		// media_handle_upload accetta anche user_id per author dell'allegato.
		$overrides = [ 'test_form' => false, 'test_size' => true ];

		// Rinomina file per privacy (evita che user_uploaded_holidays.jpg finisca in URL).
		$ext = self::MIMES[ $mime ];
		$file['name'] = 'avatar-' . (int) $user_id . '-' . wp_generate_password( 8, false, false ) . '.' . $ext;

		$_FILES['ig_enna_avatar_tmp'] = $file;
		$attachment_id = media_handle_upload( 'ig_enna_avatar_tmp', 0, [
			'post_title' => sprintf( __( 'Avatar utente #%d', 'ig-enna' ), (int) $user_id ),
		], $overrides );
		unset( $_FILES['ig_enna_avatar_tmp'] );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Sostituisce l'avatar precedente (elimina il vecchio file).
		$prev = self::get_id( $user_id );
		if ( $prev && $prev !== $attachment_id ) {
			wp_delete_attachment( $prev, true );
		}
		self::set( $user_id, $attachment_id );
		return (int) $attachment_id;
	}

	/**
	 * Filtra l'avatar WP nativo per usare il nostro se presente.
	 * Cosi' get_avatar_url() e get_avatar() ritornano la foto caricata.
	 */
	public static function init() {
		add_filter( 'get_avatar_url', [ __CLASS__, 'filter_avatar_url' ], 10, 3 );
		add_filter( 'get_avatar_data', [ __CLASS__, 'filter_avatar_data' ], 10, 2 );
	}

	public static function filter_avatar_url( $url, $id_or_email, $args ) {
		$uid = self::resolve_user_id( $id_or_email );
		if ( ! $uid ) { return $url; }
		$custom = self::get_url( $uid, 'thumbnail' );
		return $custom ?: $url;
	}

	public static function filter_avatar_data( $args, $id_or_email ) {
		$uid = self::resolve_user_id( $id_or_email );
		if ( ! $uid ) { return $args; }
		$custom = self::get_url( $uid, 'thumbnail' );
		if ( $custom ) {
			$args['url']          = $custom;
			$args['found_avatar'] = true;
		}
		return $args;
	}

	private static function resolve_user_id( $id_or_email ) {
		if ( is_numeric( $id_or_email ) )        { return (int) $id_or_email; }
		if ( $id_or_email instanceof WP_User )   { return (int) $id_or_email->ID; }
		if ( is_string( $id_or_email ) && strpos( $id_or_email, '@' ) !== false ) {
			$u = get_user_by( 'email', $id_or_email );
			return $u ? (int) $u->ID : 0;
		}
		if ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) ) {
			return (int) $id_or_email->user_id;
		}
		return 0;
	}
}
