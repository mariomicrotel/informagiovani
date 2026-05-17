<?php
/**
 * Rendering pubblico: liste opportunità, eventi, card, template detail.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Frontend {

	public static function init() {
		add_filter( 'the_content', [ __CLASS__, 'inject_single_content' ] );
	}

	/**
	 * Sui single CPT del plugin sostituisce il_content con la vista plugin
	 * (mantenendo l'integrazione con header/footer del tema).
	 */
	public static function inject_single_content( $content ) {
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		if ( is_singular( 'ig_scheda' ) ) {
			IG_Enna_Assets::enqueue_public();
			return self::render_single_scheda( get_post(), $content );
		}
		if ( is_singular( 'ig_evento' ) ) {
			IG_Enna_Assets::enqueue_public();
			return self::render_single_evento( get_post(), $content );
		}
		return $content;
	}

	private static function render_single_scheda( $post, $original_content ) {
		ob_start();
		$ig_post = $post;
		$ig_content = $original_content;
		include IG_ENNA_DIR . 'public/views/single-ig-scheda-content.php';
		return ob_get_clean();
	}

	private static function render_single_evento( $post, $original_content ) {
		ob_start();
		$ig_post = $post;
		$ig_content = $original_content;
		include IG_ENNA_DIR . 'public/views/single-ig-evento-content.php';
		return ob_get_clean();
	}

	/* =====================================================
	 *  LISTA OPPORTUNITÀ (schede)
	 * ===================================================== */

	public static function render_opportunita_list( $atts = [] ) {
		$atts = shortcode_atts( [
			'per_page' => 9,
			'show_filters' => '1',
		], $atts, 'ig_enna_opportunita' );

		$paged = max( 1, isset( $_GET['ig_page'] ) ? (int) $_GET['ig_page'] : 1 );
		$args  = [
			'post_type'      => 'ig_scheda',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $atts['per_page'] ),
			'paged'          => $paged,
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		// Search.
		$q = isset( $_GET['ig_q'] ) ? sanitize_text_field( wp_unslash( $_GET['ig_q'] ) ) : '';
		if ( $q !== '' ) {
			$args['s'] = $q;
		}

		// Tax filters.
		$tax_query = [];
		foreach ( [ 'ig_area', 'ig_target', 'ig_territorio' ] as $tx ) {
			if ( ! empty( $_GET[ $tx ] ) ) {
				$slug = sanitize_title( wp_unslash( $_GET[ $tx ] ) );
				$tax_query[] = [ 'taxonomy' => $tx, 'field' => 'slug', 'terms' => [ $slug ] ];
			}
		}
		if ( $tax_query ) {
			$args['tax_query'] = $tax_query;
		}

		// Urgency filter (via deadline meta).
		$urg = isset( $_GET['ig_urg'] ) ? sanitize_key( $_GET['ig_urg'] ) : '';
		if ( $urg ) {
			$today = current_time( 'Y-m-d' );
			if ( $urg === 'urgent' ) {
				$max = gmdate( 'Y-m-d', current_time( 'timestamp' ) + 7 * DAY_IN_SECONDS );
				$args['meta_query'] = [ [ 'key' => '_ig_enna_deadline', 'value' => [ $today, $max ], 'compare' => 'BETWEEN', 'type' => 'DATE' ] ];
				$args['orderby']    = 'meta_value';
				$args['meta_key']   = '_ig_enna_deadline';
				$args['order']      = 'ASC';
			} elseif ( $urg === 'soon' ) {
				$max = gmdate( 'Y-m-d', current_time( 'timestamp' ) + 21 * DAY_IN_SECONDS );
				$args['meta_query'] = [ [ 'key' => '_ig_enna_deadline', 'value' => [ $today, $max ], 'compare' => 'BETWEEN', 'type' => 'DATE' ] ];
				$args['orderby']    = 'meta_value';
				$args['meta_key']   = '_ig_enna_deadline';
				$args['order']      = 'ASC';
			} elseif ( $urg === 'open' ) {
				$args['meta_query'] = [
					'relation' => 'OR',
					[ 'key' => '_ig_enna_deadline', 'compare' => 'NOT EXISTS' ],
					[ 'key' => '_ig_enna_deadline', 'value' => '', 'compare' => '=' ],
				];
			}
		}

		$query = new WP_Query( $args );

		IG_Enna_Assets::enqueue_public();
		ob_start();
		$show_filters = ! empty( $atts['show_filters'] ) && $atts['show_filters'] !== '0';
		include IG_ENNA_DIR . 'public/views/list-opportunita.php';
		wp_reset_postdata();
		return ob_get_clean();
	}

	/* =====================================================
	 *  LISTA EVENTI
	 * ===================================================== */

	public static function render_partner_list( $atts = [] ) {
		$atts = shortcode_atts( [
			'tipo'     => '',
			'per_page' => -1,
		], $atts, 'ig_enna_partner' );

		$args = [
			'post_type'      => 'ig_partner',
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['per_page'],
			'orderby'        => 'title',
			'order'          => 'ASC',
		];
		if ( $atts['tipo'] ) {
			$args['meta_query'] = [ [ 'key' => '_ig_enna_partner_type', 'value' => sanitize_key( $atts['tipo'] ) ] ];
		}
		$query = new WP_Query( $args );

		IG_Enna_Assets::enqueue_public();
		ob_start();
		include IG_ENNA_DIR . 'public/views/list-partner.php';
		wp_reset_postdata();
		return ob_get_clean();
	}

	public static function render_eventi_list( $atts = [] ) {
		$atts = shortcode_atts( [
			'per_page' => 6,
		], $atts, 'ig_enna_eventi' );

		$paged = max( 1, isset( $_GET['ig_page'] ) ? (int) $_GET['ig_page'] : 1 );
		$args  = [
			'post_type'      => 'ig_evento',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $atts['per_page'] ),
			'paged'          => $paged,
			'orderby'        => 'meta_value',
			'meta_key'       => '_ig_enna_event_date',
			'order'          => 'ASC',
			'meta_query'     => [
				[
					'key'     => '_ig_enna_event_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				],
			],
		];

		if ( ! empty( $_GET['ig_area'] ) ) {
			$args['tax_query'] = [ [
				'taxonomy' => 'ig_area',
				'field'    => 'slug',
				'terms'    => [ sanitize_title( wp_unslash( $_GET['ig_area'] ) ) ],
			] ];
		}

		$query = new WP_Query( $args );

		IG_Enna_Assets::enqueue_public();
		ob_start();
		include IG_ENNA_DIR . 'public/views/list-eventi.php';
		wp_reset_postdata();
		return ob_get_clean();
	}

	/* =====================================================
	 *  HELPERS DI PRESENTAZIONE
	 * ===================================================== */

	public static function get_scheda_meta( $post_id ) {
		$d   = get_post_meta( $post_id, '_ig_enna_deadline', true );
		$urg = IG_Enna_Scheda_Meta::compute_urgency( $d );
		return [
			'codice'         => get_post_meta( $post_id, '_ig_enna_codice', true ),
			'short'          => get_post_meta( $post_id, '_ig_enna_short', true ),
			'tipo'           => get_post_meta( $post_id, '_ig_enna_tipo', true ),
			'deadline'       => $d,
			'deadline_label' => get_post_meta( $post_id, '_ig_enna_deadline_label', true ),
			'contributo'     => get_post_meta( $post_id, '_ig_enna_contributo', true ),
			'durata'         => get_post_meta( $post_id, '_ig_enna_durata', true ),
			'source'         => get_post_meta( $post_id, '_ig_enna_source', true ),
			'source_url'     => get_post_meta( $post_id, '_ig_enna_source_url', true ),
			'source_class'   => get_post_meta( $post_id, '_ig_enna_source_class', true ),
			'workflow_state' => get_post_meta( $post_id, '_ig_enna_workflow_state', true ),
			'urgency'        => $urg,
			'days_left'      => self::days_left( $d ),
		];
	}

	public static function days_left( $deadline ) {
		if ( empty( $deadline ) ) {
			return null;
		}
		$ts = strtotime( $deadline . ' 23:59:59' );
		if ( ! $ts ) {
			return null;
		}
		return (int) floor( ( $ts - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
	}

	public static function format_days_left( $days ) {
		if ( $days === null ) {
			return __( 'Sempre aperta', 'ig-enna' );
		}
		if ( $days < 0 ) {
			return __( 'Scaduta', 'ig-enna' );
		}
		if ( $days === 0 ) {
			return __( 'Oggi', 'ig-enna' );
		}
		return sprintf( _n( '%d giorno', '%d giorni', $days, 'ig-enna' ), $days );
	}

	public static function area_slug( $post_id ) {
		$terms = get_the_terms( $post_id, 'ig_area' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->slug;
		}
		return '';
	}

	public static function area_label( $post_id ) {
		$terms = get_the_terms( $post_id, 'ig_area' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
		return '';
	}

	public static function targets( $post_id ) {
		$terms = get_the_terms( $post_id, 'ig_target' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return wp_list_pluck( $terms, 'name' );
		}
		return [];
	}

	public static function territorio( $post_id ) {
		$terms = get_the_terms( $post_id, 'ig_territorio' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
		return '';
	}

	/** Build url for current request preserving filters, swapping one param. */
	public static function filter_url( $params = [] ) {
		$current = $_GET;
		foreach ( $params as $k => $v ) {
			if ( $v === null || $v === '' ) {
				unset( $current[ $k ] );
			} else {
				$current[ $k ] = $v;
			}
		}
		unset( $current['ig_page'] );
		return esc_url( add_query_arg( $current, get_permalink() ) );
	}
}
