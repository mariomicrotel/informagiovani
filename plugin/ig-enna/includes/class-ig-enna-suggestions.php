<?php
/**
 * Suggerimenti personalizzati per utente basati sul profilo.
 * Algoritmo di scoring semplice, deterministic, senza dipendenze esterne.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Suggestions {

	/** Numero max item per sezione (schede/eventi/news). */
	const LIMIT_SCHEDE = 6;
	const LIMIT_EVENTI = 4;
	const LIMIT_NEWS   = 3;

	/**
	 * Restituisce suggerimenti + spiegazioni per un utente.
	 *
	 * @return array{
	 *   schede: array<int,array{post:WP_Post, score:int, reasons:string[]}>,
	 *   eventi: array<int,array{post:WP_Post, score:int, reasons:string[]}>,
	 *   news: array<int,array{post:WP_Post, score:int, reasons:string[]}>,
	 *   profile_completion: int,
	 *   has_profile: bool,
	 *   interests: string[],
	 *   age: int|null,
	 *   city: string
	 * }
	 */
	public static function for_user( $user_id ) {
		$user_id = (int) $user_id;
		$profile = class_exists( 'IG_Enna_User_Profile' ) ? IG_Enna_User_Profile::get( $user_id ) : [];
		$completion = class_exists( 'IG_Enna_User_Profile' ) ? (int) IG_Enna_User_Profile::completion( $user_id ) : 0;

		$interests_raw = isset( $profile['ig_interests'] ) && is_array( $profile['ig_interests'] )
			? $profile['ig_interests']
			: [];
		$interests = array_map( 'trim', array_map( 'strval', $interests_raw ) );
		$interests = array_filter( $interests, 'strlen' );

		$age  = isset( $profile['ig_age'] )  ? (int) $profile['ig_age'] : null;
		$city = isset( $profile['ig_city'] ) ? (string) $profile['ig_city'] : '';

		// Escludi le schede già salvate dall'utente.
		$saved = class_exists( 'IG_Enna_User_Saves' )
			? IG_Enna_User_Saves::ids_for_user( $user_id, 'scheda' )
			: [];

		$has_profile = $completion >= 30 || $interests || $age;

		return [
			'schede'             => self::rank_schede( $interests, $age, $city, $saved ),
			'eventi'             => self::rank_eventi( $interests, $age, $city ),
			'news'               => self::rank_news( $interests ),
			'profile_completion' => $completion,
			'has_profile'        => $has_profile,
			'interests'          => $interests,
			'age'                => $age,
			'city'               => $city,
		];
	}

	/** Mappa lowercase → slug termine tassonomia. */
	private static function taxonomy_slug_map( $taxonomy ) {
		$terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
		if ( ! is_array( $terms ) || is_wp_error( $terms ) ) { return []; }
		$out = [];
		foreach ( $terms as $t ) {
			$out[ strtolower( $t->name ) ] = $t->slug;
			$out[ $t->slug ]                = $t->slug;
		}
		return $out;
	}

	/** Converte interessi utente (label/slug) in slug ig_area validi. */
	private static function interests_to_area_slugs( array $interests ) {
		$map = self::taxonomy_slug_map( 'ig_area' );
		$out = [];
		foreach ( $interests as $it ) {
			$key = strtolower( trim( $it ) );
			if ( isset( $map[ $key ] ) ) { $out[] = $map[ $key ]; }
		}
		return array_values( array_unique( $out ) );
	}

	/**
	 * Rank schede per utente. Fattori:
	 *  - area match (+5)
	 *  - target age match (+3)
	 *  - territorio contiene città o "Enna" (+2)
	 *  - deadline entro 30gg (+2)
	 *  - pubblicata negli ultimi 30gg (+1)
	 *
	 * @return array<int,array{post:WP_Post, score:int, reasons:string[]}>
	 */
	private static function rank_schede( array $interests, $age, $city, array $exclude_ids ) {
		$area_slugs = self::interests_to_area_slugs( $interests );

		// Base query: schede pubblicate, non scadute, non già salvate.
		$today = current_time( 'Y-m-d' );
		$args = [
			'post_type'      => 'ig_scheda',
			'post_status'    => 'publish',
			'posts_per_page' => 40,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post__not_in'   => $exclude_ids ?: [ 0 ],
			'meta_query'     => [
				'relation' => 'OR',
				[ 'key' => '_ig_enna_deadline', 'compare' => 'NOT EXISTS' ],
				[ 'key' => '_ig_enna_deadline', 'value' => '', 'compare' => '=' ],
				[ 'key' => '_ig_enna_deadline', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' ],
			],
		];
		$q = new WP_Query( $args );
		$scored = [];
		foreach ( $q->posts as $post ) {
			$reasons = [];
			$score   = 0;

			// Area match.
			if ( $area_slugs ) {
				$scheda_areas = wp_get_post_terms( $post->ID, 'ig_area', [ 'fields' => 'all' ] );
				$scheda_slugs = wp_list_pluck( is_array( $scheda_areas ) ? $scheda_areas : [], 'slug' );
				$overlap = array_intersect( $area_slugs, $scheda_slugs );
				if ( $overlap ) {
					$score += 5;
					$labels = [];
					foreach ( $scheda_areas as $t ) {
						if ( in_array( $t->slug, $overlap, true ) ) { $labels[] = $t->name; }
					}
					$reasons[] = sprintf(
						/* translators: %s = lista aree */
						__( 'nelle tue aree: %s', 'ig-enna' ),
						implode( ', ', $labels )
					);
				}
			}

			// Target age match.
			if ( $age ) {
				$targets = wp_get_post_terms( $post->ID, 'ig_target', [ 'fields' => 'names' ] );
				$targets = is_array( $targets ) ? $targets : [];
				foreach ( $targets as $t ) {
					if ( preg_match( '/(\d+)\s*[-–]\s*(\d+)/u', $t, $m ) ) {
						$min = (int) $m[1]; $max = (int) $m[2];
						if ( $age >= $min && $age <= $max ) {
							$score += 3;
							$reasons[] = sprintf(
								/* translators: %s = fascia target */
								__( 'per la tua età (%s)', 'ig-enna' ),
								$t
							);
							break;
						}
					}
				}
			}

			// Territorio match.
			if ( $city ) {
				$terr = wp_get_post_terms( $post->ID, 'ig_territorio', [ 'fields' => 'names' ] );
				$terr = is_array( $terr ) ? $terr : [];
				$city_low = strtolower( trim( $city ) );
				foreach ( $terr as $t ) {
					$t_low = strtolower( $t );
					if ( strpos( $t_low, $city_low ) !== false
						|| strpos( $t_low, 'enna' ) !== false
						|| $t_low === 'italia' || $t_low === 'italia · sud' ) {
						$score += 2;
						$reasons[] = sprintf(
							/* translators: %s = territorio */
							__( 'valida per %s', 'ig-enna' ),
							$t
						);
						break;
					}
				}
			}

			// Deadline vicina.
			$deadline = get_post_meta( $post->ID, '_ig_enna_deadline', true );
			if ( $deadline ) {
				$days_left = (int) floor( ( strtotime( $deadline . ' 23:59:59' ) - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
				if ( $days_left >= 0 && $days_left <= 30 ) {
					$score += 2;
					$reasons[] = sprintf(
						/* translators: %d = giorni */
						_n( 'scade tra %d giorno', 'scade tra %d giorni', max( 1, $days_left ), 'ig-enna' ),
						max( 1, $days_left )
					);
				}
			}

			// Fresco (ultimi 30gg).
			$pub_ts = strtotime( $post->post_date );
			if ( $pub_ts && ( current_time( 'timestamp' ) - $pub_ts ) < 30 * DAY_IN_SECONDS ) {
				$score += 1;
				$reasons[] = __( 'novità', 'ig-enna' );
			}

			if ( $score > 0 ) {
				$scored[] = [ 'post' => $post, 'score' => $score, 'reasons' => $reasons ];
			}
		}
		wp_reset_postdata();

		// Sort by score DESC (stable by post date).
		usort( $scored, function ( $a, $b ) {
			return $b['score'] <=> $a['score'];
		} );
		return array_slice( $scored, 0, self::LIMIT_SCHEDE );
	}

	/** Eventi ranking: solo area + prossimità data. */
	private static function rank_eventi( array $interests, $age, $city ) {
		$area_slugs = self::interests_to_area_slugs( $interests );
		$today = current_time( 'Y-m-d' );
		$args = [
			'post_type'      => 'ig_evento',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'meta_key'       => '_ig_enna_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => [ [ 'key' => '_ig_enna_event_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE' ] ],
		];
		$q = new WP_Query( $args );
		$scored = [];
		foreach ( $q->posts as $post ) {
			$reasons = [];
			$score   = 1; // baseline: è un evento futuro
			if ( $area_slugs ) {
				$evt_areas = wp_get_post_terms( $post->ID, 'ig_area', [ 'fields' => 'all' ] );
				$evt_slugs = wp_list_pluck( is_array( $evt_areas ) ? $evt_areas : [], 'slug' );
				$overlap = array_intersect( $area_slugs, $evt_slugs );
				if ( $overlap ) {
					$score += 4;
					$labels = [];
					foreach ( $evt_areas as $t ) {
						if ( in_array( $t->slug, $overlap, true ) ) { $labels[] = $t->name; }
					}
					$reasons[] = sprintf( __( 'sui temi che segui: %s', 'ig-enna' ), implode( ', ', $labels ) );
				}
			}
			$d = get_post_meta( $post->ID, '_ig_enna_event_date', true );
			if ( $d ) {
				$days = (int) floor( ( strtotime( $d ) - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
				if ( $days >= 0 && $days <= 14 ) {
					$score += 2;
					$reasons[] = ( $days === 0 ) ? __( 'oggi', 'ig-enna' ) : sprintf( _n( 'tra %d giorno', 'tra %d giorni', $days, 'ig-enna' ), $days );
				}
			}
			$scored[] = [ 'post' => $post, 'score' => $score, 'reasons' => $reasons ];
		}
		wp_reset_postdata();
		usort( $scored, function ( $a, $b ) { return $b['score'] <=> $a['score']; } );
		return array_slice( $scored, 0, self::LIMIT_EVENTI );
	}

	/** News ranking: match area interessi + freschezza. */
	private static function rank_news( array $interests ) {
		$area_slugs = self::interests_to_area_slugs( $interests );
		$args = [
			'post_type'      => 'ig_news',
			'post_status'    => 'publish',
			'posts_per_page' => 15,
			'orderby'        => 'date',
			'order'          => 'DESC',
		];
		if ( $area_slugs ) {
			$args['tax_query'] = [ [
				'taxonomy' => 'ig_area',
				'field'    => 'slug',
				'terms'    => $area_slugs,
				'operator' => 'IN',
			] ];
		}
		$q = new WP_Query( $args );
		$scored = [];
		foreach ( $q->posts as $post ) {
			$reasons = [];
			$score = 1;
			if ( $area_slugs ) {
				$areas = wp_get_post_terms( $post->ID, 'ig_area', [ 'fields' => 'names' ] );
				if ( is_array( $areas ) && $areas ) {
					$score += 2;
					$reasons[] = sprintf( __( 'area: %s', 'ig-enna' ), implode( ', ', $areas ) );
				}
			}
			$scored[] = [ 'post' => $post, 'score' => $score, 'reasons' => $reasons ];
		}
		wp_reset_postdata();
		return array_slice( $scored, 0, self::LIMIT_NEWS );
	}
}
