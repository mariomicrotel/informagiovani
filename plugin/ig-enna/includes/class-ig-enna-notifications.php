<?php
/**
 * Notifiche email base.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Notifications {

	public static function init() {
		add_action( 'ig_enna_ticket_created', [ __CLASS__, 'on_ticket_created' ], 10, 2 );
		add_action( 'ig_enna_ticket_updated', [ __CLASS__, 'on_ticket_updated' ], 10, 3 );
	}

	private static function org() {
		return (string) ig_enna_get_setting( 'org_name', 'Informagiovani Enna' );
	}

	private static function staff_email() {
		$e = ig_enna_get_setting( 'contact_email', '' );
		return $e ?: get_option( 'admin_email' );
	}

	public static function on_ticket_created( $ticket_id, $row ) {
		$user = ! empty( $row['user_id'] ) ? get_userdata( (int) $row['user_id'] ) : null;
		$subj = sprintf(
			/* translators: %1$s = nome org, %2$d = id ticket */
			__( '[%1$s] Nuova richiesta R-%2$d', 'ig-enna' ),
			self::org(), (int) $ticket_id
		);
		$body = sprintf(
			__( "È stata aperta una nuova richiesta R-%1\$d.\n\nUtente: %2\$s\nOggetto: %3\$s\nArea: %4\$s\nPriorità: %5\$s\n\nMessaggio:\n%6\$s", 'ig-enna' ),
			(int) $ticket_id,
			$user ? ( $user->display_name . ' · ' . $user->user_email ) : '—',
			$row['subject'] ?? '',
			$row['area_slug'] ?? '—',
			$row['priority'] ?? 'media',
			wp_strip_all_tags( (string) ( $row['message'] ?? '' ) )
		);
		wp_mail( self::staff_email(), $subj, $body );
	}

	public static function on_ticket_updated( $ticket_id, $before, $after ) {
		// Notifica utente solo se cambia status.
		if ( empty( $after['status'] ) || ( $before['status'] ?? '' ) === $after['status'] ) {
			return;
		}
		$user = ! empty( $after['user_id'] ) ? get_userdata( (int) $after['user_id'] ) : null;
		if ( ! $user || empty( $user->user_email ) ) {
			return;
		}
		$status_label = IG_Enna_Tickets::status_label( $after['status'] );
		$subj = sprintf(
			__( '[%1$s] Aggiornamento richiesta R-%2$d · %3$s', 'ig-enna' ),
			self::org(), (int) $ticket_id, $status_label
		);
		$body = sprintf(
			__( "Ciao %1\$s,\n\nlo stato della tua richiesta R-%2\$d (\"%3\$s\") è cambiato in: %4\$s.\n\nPuoi consultare la richiesta nella tua area personale.\n\n— %5\$s", 'ig-enna' ),
			$user->display_name ?: $user->user_login,
			(int) $ticket_id,
			$after['subject'] ?? '',
			$status_label,
			self::org()
		);
		wp_mail( $user->user_email, $subj, $body );
	}
}
