<?php
/**
 * REST API namespace ig-enna/v1.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_REST {

	const NS = 'ig-enna/v1';

	public static function init() {
		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	public static function register_routes() {
		register_rest_route( self::NS, '/saves/(?P<id>\d+)', [
			[
				'methods'             => 'POST',
				'callback'            => [ __CLASS__, 'save_scheda' ],
				'permission_callback' => [ __CLASS__, 'logged_in' ],
				'args'                => [
					'id' => [ 'type' => 'integer', 'required' => true ],
				],
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ __CLASS__, 'unsave_scheda' ],
				'permission_callback' => [ __CLASS__, 'logged_in' ],
				'args'                => [
					'id' => [ 'type' => 'integer', 'required' => true ],
				],
			],
		] );

		register_rest_route( self::NS, '/saves', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'list_saves' ],
			'permission_callback' => [ __CLASS__, 'logged_in' ],
		] );

		register_rest_route( self::NS, '/tickets', [
			[
				'methods'             => 'GET',
				'callback'            => [ __CLASS__, 'list_my_tickets' ],
				'permission_callback' => [ __CLASS__, 'logged_in' ],
			],
			[
				'methods'             => 'POST',
				'callback'            => [ __CLASS__, 'create_ticket' ],
				'permission_callback' => [ __CLASS__, 'logged_in' ],
				'args'                => [
					'subject'   => [ 'type' => 'string', 'required' => true ],
					'message'   => [ 'type' => 'string', 'required' => true ],
					'area_slug' => [ 'type' => 'string' ],
					'priority'  => [ 'type' => 'string' ],
				],
			],
		] );
	}

	public static function logged_in() {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'ig_enna_auth', __( 'Devi accedere per usare questa funzione.', 'ig-enna' ), [ 'status' => 401 ] );
		}
		return true;
	}

	public static function save_scheda( WP_REST_Request $req ) {
		$id   = (int) $req['id'];
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'ig_scheda' || $post->post_status !== 'publish' ) {
			return new WP_Error( 'ig_enna_not_found', __( 'Scheda non trovata.', 'ig-enna' ), [ 'status' => 404 ] );
		}
		IG_Enna_User_Saves::save( get_current_user_id(), $id, 'scheda' );
		return rest_ensure_response( [ 'saved' => true, 'id' => $id ] );
	}

	public static function unsave_scheda( WP_REST_Request $req ) {
		$id = (int) $req['id'];
		IG_Enna_User_Saves::unsave( get_current_user_id(), $id, 'scheda' );
		return rest_ensure_response( [ 'saved' => false, 'id' => $id ] );
	}

	public static function list_saves() {
		$ids = IG_Enna_User_Saves::ids_for_user( get_current_user_id(), 'scheda' );
		return rest_ensure_response( [ 'ids' => $ids, 'count' => count( $ids ) ] );
	}

	public static function list_my_tickets() {
		$res = IG_Enna_Tickets::query( [
			'user_id' => get_current_user_id(),
			'limit'   => 50,
		] );
		return rest_ensure_response( $res );
	}

	public static function create_ticket( WP_REST_Request $req ) {
		$id = IG_Enna_Tickets::create( [
			'user_id'   => get_current_user_id(),
			'subject'   => (string) $req->get_param( 'subject' ),
			'message'   => (string) $req->get_param( 'message' ),
			'area_slug' => (string) $req->get_param( 'area_slug' ),
			'priority'  => (string) ( $req->get_param( 'priority' ) ?: 'media' ),
		] );
		if ( ! $id ) {
			return new WP_Error( 'ig_enna_invalid', __( 'Dati non validi.', 'ig-enna' ), [ 'status' => 400 ] );
		}
		return rest_ensure_response( IG_Enna_Tickets::get( $id ) );
	}
}
