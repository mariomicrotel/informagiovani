<?php
/**
 * Tassonomie del plugin.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Taxonomies {

	public static function register() {
		$post_types = [ 'ig_scheda', 'ig_evento', 'ig_news' ];

		register_taxonomy( 'ig_area', $post_types, [
			'labels'            => [
				'name'          => __( 'Aree tematiche', 'ig-enna' ),
				'singular_name' => __( 'Area', 'ig-enna' ),
			],
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'area' ],
		] );

		register_taxonomy( 'ig_target', $post_types, [
			'labels'            => [
				'name'          => __( 'Target', 'ig-enna' ),
				'singular_name' => __( 'Target', 'ig-enna' ),
			],
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'target' ],
		] );

		register_taxonomy( 'ig_territorio', $post_types, [
			'labels'            => [
				'name'          => __( 'Territori', 'ig-enna' ),
				'singular_name' => __( 'Territorio', 'ig-enna' ),
			],
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => [ 'slug' => 'territorio' ],
		] );

		register_taxonomy( 'ig_fonte', [ 'ig_scheda' ], [
			'labels'            => [
				'name'          => __( 'Fonti', 'ig-enna' ),
				'singular_name' => __( 'Fonte', 'ig-enna' ),
			],
			'public'            => false,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		] );
	}

	/**
	 * Seed delle tassonomie con i valori di default (eseguito in activation).
	 */
	public static function seed() {
		self::seed_terms( 'ig_area', ig_enna_default_areas() );

		$pairs = [];
		foreach ( ig_enna_default_targets() as $t )      { $pairs[ sanitize_title( $t ) ] = $t; }
		self::seed_terms( 'ig_target', $pairs );

		$pairs = [];
		foreach ( ig_enna_default_territories() as $t )  { $pairs[ sanitize_title( $t ) ] = $t; }
		self::seed_terms( 'ig_territorio', $pairs );

		$pairs = [];
		foreach ( ig_enna_default_sources() as $t )      { $pairs[ sanitize_title( $t ) ] = $t; }
		self::seed_terms( 'ig_fonte', $pairs );
	}

	/**
	 * @param string                $taxonomy
	 * @param array<string,string>  $terms slug => label
	 */
	private static function seed_terms( $taxonomy, $terms ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}
		foreach ( $terms as $slug => $label ) {
			if ( ! term_exists( $slug, $taxonomy ) ) {
				wp_insert_term( $label, $taxonomy, [ 'slug' => $slug ] );
			}
		}
	}
}
