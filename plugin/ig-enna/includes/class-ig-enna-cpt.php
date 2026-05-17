<?php
/**
 * Custom Post Types del plugin.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_CPT {

	public static function register() {
		self::register_scheda();
		self::register_evento();
		self::register_partner();
		self::register_percorso();
	}

	private static function register_scheda() {
		$labels = [
			'name'          => __( 'Schede informative', 'ig-enna' ),
			'singular_name' => __( 'Scheda informativa', 'ig-enna' ),
			'menu_name'     => __( 'Schede', 'ig-enna' ),
			'add_new_item'  => __( 'Aggiungi scheda', 'ig-enna' ),
			'edit_item'     => __( 'Modifica scheda', 'ig-enna' ),
			'search_items'  => __( 'Cerca schede', 'ig-enna' ),
		];

		register_post_type( 'ig_scheda', [
			'labels'        => $labels,
			'public'        => true,
			'show_ui'       => true,
			'show_in_menu'  => false, // mostrato dal nostro menu top-level
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-clipboard',
			'has_archive'   => 'opportunita',
			'rewrite'       => [ 'slug' => 'opportunita', 'with_front' => false ],
			'supports'      => [ 'title', 'editor', 'excerpt', 'custom-fields', 'revisions', 'author' ],
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		] );
	}

	private static function register_evento() {
		$labels = [
			'name'          => __( 'Eventi', 'ig-enna' ),
			'singular_name' => __( 'Evento', 'ig-enna' ),
			'menu_name'     => __( 'Eventi', 'ig-enna' ),
			'add_new_item'  => __( 'Aggiungi evento', 'ig-enna' ),
			'edit_item'     => __( 'Modifica evento', 'ig-enna' ),
		];

		register_post_type( 'ig_evento', [
			'labels'        => $labels,
			'public'        => true,
			'show_ui'       => true,
			'show_in_menu'  => false,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-calendar-alt',
			'has_archive'   => 'eventi',
			'rewrite'       => [ 'slug' => 'eventi', 'with_front' => false ],
			'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		] );
	}

	private static function register_partner() {
		$labels = [
			'name'          => __( 'Partner', 'ig-enna' ),
			'singular_name' => __( 'Partner', 'ig-enna' ),
			'menu_name'     => __( 'Partner', 'ig-enna' ),
		];

		register_post_type( 'ig_partner', [
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => false,
			'show_in_rest'  => false,
			'menu_icon'     => 'dashicons-networking',
			'supports'      => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		] );
	}

	private static function register_percorso() {
		$labels = [
			'name'          => __( 'Percorsi Impresa', 'ig-enna' ),
			'singular_name' => __( 'Percorso', 'ig-enna' ),
			'menu_name'     => __( 'Percorsi', 'ig-enna' ),
		];

		register_post_type( 'ig_percorso', [
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => false,
			'show_in_rest'  => false,
			'menu_icon'     => 'dashicons-chart-line',
			'supports'      => [ 'title', 'editor', 'custom-fields' ],
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		] );
	}
}
