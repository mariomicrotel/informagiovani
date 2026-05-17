<?php
/**
 * Custom columns, filtri e sorting per le liste admin di ig_scheda e ig_evento.
 */

defined( 'ABSPATH' ) || exit;

class IG_Enna_Admin_List {

	public static function init() {
		// SCHEDA.
		add_filter( 'manage_ig_scheda_posts_columns',       [ __CLASS__, 'scheda_columns' ] );
		add_action( 'manage_ig_scheda_posts_custom_column', [ __CLASS__, 'scheda_column_value' ], 10, 2 );
		add_filter( 'manage_edit-ig_scheda_sortable_columns', [ __CLASS__, 'scheda_sortable' ] );
		add_action( 'restrict_manage_posts',                [ __CLASS__, 'scheda_filters' ] );
		add_action( 'pre_get_posts',                        [ __CLASS__, 'apply_query_filters' ] );

		// EVENTO.
		add_filter( 'manage_ig_evento_posts_columns',       [ __CLASS__, 'evento_columns' ] );
		add_action( 'manage_ig_evento_posts_custom_column', [ __CLASS__, 'evento_column_value' ], 10, 2 );
		add_filter( 'manage_edit-ig_evento_sortable_columns', [ __CLASS__, 'evento_sortable' ] );
	}

	/* =====================================================
	 *  SCHEDA
	 * ===================================================== */

	public static function scheda_columns( $cols ) {
		$new = [];
		$new['cb']        = $cols['cb'] ?? '';
		$new['title']     = __( 'Titolo', 'ig-enna' );
		$new['ig_codice'] = __( 'Codice', 'ig-enna' );
		$new['ig_area']   = __( 'Area', 'ig-enna' );
		$new['ig_state']  = __( 'Stato', 'ig-enna' );
		$new['ig_dead']   = __( 'Scadenza', 'ig-enna' );
		$new['ig_source'] = __( 'Fonte', 'ig-enna' );
		$new['author']    = $cols['author'] ?? __( 'Autore', 'ig-enna' );
		$new['date']      = $cols['date']   ?? __( 'Data', 'ig-enna' );
		return $new;
	}

	public static function scheda_column_value( $col, $post_id ) {
		switch ( $col ) {
			case 'ig_codice':
				$code = get_post_meta( $post_id, '_ig_enna_codice', true );
				echo $code ? '<code class="ig-enna-code">' . esc_html( $code ) . '</code>' : '<span aria-hidden="true">—</span>';
				break;

			case 'ig_area':
				$terms = get_the_terms( $post_id, 'ig_area' );
				if ( $terms && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $t ) {
						printf(
							'<span class="ig-enna-badge ig-enna-badge--area-%1$s">%2$s</span> ',
							esc_attr( $t->slug ),
							esc_html( $t->name )
						);
					}
				} else {
					echo '—';
				}
				break;

			case 'ig_state':
				$state  = get_post_meta( $post_id, '_ig_enna_workflow_state', true );
				$states = IG_Enna_Scheda_Meta::workflow_states();
				if ( $state && isset( $states[ $state ] ) ) {
					printf(
						'<span class="ig-enna-badge ig-enna-badge--state-%1$s">%2$s</span>',
						esc_attr( $state ),
						esc_html( $states[ $state ] )
					);
				} else {
					echo '<span class="ig-enna-badge ig-enna-badge--state-draft">' . esc_html__( 'Bozza', 'ig-enna' ) . '</span>';
				}
				break;

			case 'ig_dead':
				$d     = get_post_meta( $post_id, '_ig_enna_deadline', true );
				$label = get_post_meta( $post_id, '_ig_enna_deadline_label', true );
				if ( empty( $d ) && empty( $label ) ) {
					echo '<em>' . esc_html__( 'Sempre aperta', 'ig-enna' ) . '</em>';
					break;
				}
				$urg = IG_Enna_Scheda_Meta::compute_urgency( $d );
				$cls = $urg ? 'ig-enna-badge ig-enna-badge--urg-' . $urg : '';
				$text = $label ?: $d;
				printf( '<span class="%1$s">%2$s</span>', esc_attr( $cls ), esc_html( $text ) );
				break;

			case 'ig_source':
				$src    = get_post_meta( $post_id, '_ig_enna_source', true );
				$class  = get_post_meta( $post_id, '_ig_enna_source_class', true );
				$url    = get_post_meta( $post_id, '_ig_enna_source_url', true );
				if ( empty( $src ) ) {
					echo '—';
					break;
				}
				$text = esc_html( $src );
				if ( $url ) {
					$text = '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . $text . '</a>';
				}
				if ( $class ) {
					$labels = IG_Enna_Scheda_Meta::source_classes();
					$badge  = isset( $labels[ $class ] ) ? $labels[ $class ] : $class;
					$text  .= sprintf(
						' <span class="ig-enna-badge ig-enna-badge--src-%1$s">%2$s</span>',
						esc_attr( $class ),
						esc_html( $badge )
					);
				}
				echo $text; // Già escapato sopra.
				break;
		}
	}

	public static function scheda_sortable( $cols ) {
		$cols['ig_dead']  = 'ig_dead';
		$cols['ig_state'] = 'ig_state';
		return $cols;
	}

	public static function scheda_filters() {
		global $typenow;
		if ( $typenow !== 'ig_scheda' && $typenow !== 'ig_evento' ) {
			return;
		}

		if ( $typenow === 'ig_scheda' ) {
			// Filtro Area.
			self::render_taxonomy_filter( 'ig_area', __( 'Tutte le aree', 'ig-enna' ) );
			self::render_taxonomy_filter( 'ig_target', __( 'Tutti i target', 'ig-enna' ) );
			self::render_taxonomy_filter( 'ig_territorio', __( 'Tutti i territori', 'ig-enna' ) );

			// Filtro stato workflow.
			$current = isset( $_GET['ig_state'] ) ? sanitize_key( $_GET['ig_state'] ) : '';
			echo '<select name="ig_state"><option value="">' . esc_html__( 'Tutti gli stati', 'ig-enna' ) . '</option>';
			foreach ( IG_Enna_Scheda_Meta::workflow_states() as $k => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $current, $k, false ), esc_html( $label ) );
			}
			echo '</select>';

			// Filtro urgenza.
			$current = isset( $_GET['ig_urg'] ) ? sanitize_key( $_GET['ig_urg'] ) : '';
			$urgs    = [
				'urgent' => __( 'Scadenza urgente (≤ 7gg)', 'ig-enna' ),
				'soon'   => __( 'In scadenza (≤ 21gg)',     'ig-enna' ),
				'exp'    => __( 'Scadute',                  'ig-enna' ),
			];
			echo '<select name="ig_urg"><option value="">' . esc_html__( 'Qualsiasi scadenza', 'ig-enna' ) . '</option>';
			foreach ( $urgs as $k => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $current, $k, false ), esc_html( $label ) );
			}
			echo '</select>';
		}

		if ( $typenow === 'ig_evento' ) {
			self::render_taxonomy_filter( 'ig_area', __( 'Tutte le aree', 'ig-enna' ) );

			$current = isset( $_GET['ig_event_mode'] ) ? sanitize_key( $_GET['ig_event_mode'] ) : '';
			echo '<select name="ig_event_mode"><option value="">' . esc_html__( 'Tutte le modalità', 'ig-enna' ) . '</option>';
			foreach ( IG_Enna_Evento_Meta::modes() as $k => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $current, $k, false ), esc_html( $label ) );
			}
			echo '</select>';

			$current = isset( $_GET['ig_event_status'] ) ? sanitize_key( $_GET['ig_event_status'] ) : '';
			echo '<select name="ig_event_status"><option value="">' . esc_html__( 'Qualsiasi stato', 'ig-enna' ) . '</option>';
			foreach ( IG_Enna_Evento_Meta::statuses() as $k => $label ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $current, $k, false ), esc_html( $label ) );
			}
			echo '</select>';
		}
	}

	private static function render_taxonomy_filter( $taxonomy, $all_label ) {
		$current = isset( $_GET[ $taxonomy ] ) ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) ) : '';
		$terms   = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => false ] );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}
		printf( '<select name="%s"><option value="">%s</option>', esc_attr( $taxonomy ), esc_html( $all_label ) );
		foreach ( $terms as $t ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $t->slug ),
				selected( $current, $t->slug, false ),
				esc_html( $t->name )
			);
		}
		echo '</select>';
	}

	public static function apply_query_filters( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return;
		}

		if ( $screen->id === 'edit-ig_scheda' ) {
			$meta_query = (array) $query->get( 'meta_query' );

			if ( ! empty( $_GET['ig_state'] ) ) {
				$state = sanitize_key( $_GET['ig_state'] );
				if ( array_key_exists( $state, IG_Enna_Scheda_Meta::workflow_states() ) ) {
					$meta_query[] = [
						'key'   => '_ig_enna_workflow_state',
						'value' => $state,
					];
				}
			}

			if ( ! empty( $_GET['ig_urg'] ) ) {
				$urg   = sanitize_key( $_GET['ig_urg'] );
				$today = current_time( 'Y-m-d' );
				if ( $urg === 'urgent' ) {
					$max = gmdate( 'Y-m-d', current_time( 'timestamp' ) + 7 * DAY_IN_SECONDS );
					$meta_query[] = [
						'key'     => '_ig_enna_deadline',
						'value'   => [ $today, $max ],
						'compare' => 'BETWEEN',
						'type'    => 'DATE',
					];
				} elseif ( $urg === 'soon' ) {
					$max = gmdate( 'Y-m-d', current_time( 'timestamp' ) + 21 * DAY_IN_SECONDS );
					$meta_query[] = [
						'key'     => '_ig_enna_deadline',
						'value'   => [ $today, $max ],
						'compare' => 'BETWEEN',
						'type'    => 'DATE',
					];
				} elseif ( $urg === 'exp' ) {
					$meta_query[] = [
						'key'     => '_ig_enna_deadline',
						'value'   => $today,
						'compare' => '<',
						'type'    => 'DATE',
					];
				}
			}

			if ( ! empty( $meta_query ) ) {
				$query->set( 'meta_query', $meta_query );
			}

			// Sorting.
			$orderby = $query->get( 'orderby' );
			if ( $orderby === 'ig_dead' ) {
				$query->set( 'meta_key', '_ig_enna_deadline' );
				$query->set( 'orderby', 'meta_value' );
			} elseif ( $orderby === 'ig_state' ) {
				$query->set( 'meta_key', '_ig_enna_workflow_state' );
				$query->set( 'orderby', 'meta_value' );
			}
		}

		if ( $screen->id === 'edit-ig_evento' ) {
			$meta_query = (array) $query->get( 'meta_query' );

			if ( ! empty( $_GET['ig_event_mode'] ) ) {
				$v = sanitize_key( $_GET['ig_event_mode'] );
				if ( array_key_exists( $v, IG_Enna_Evento_Meta::modes() ) ) {
					$meta_query[] = [ 'key' => '_ig_enna_event_mode', 'value' => $v ];
				}
			}
			if ( ! empty( $_GET['ig_event_status'] ) ) {
				$v = sanitize_key( $_GET['ig_event_status'] );
				if ( array_key_exists( $v, IG_Enna_Evento_Meta::statuses() ) ) {
					$meta_query[] = [ 'key' => '_ig_enna_event_status', 'value' => $v ];
				}
			}
			if ( ! empty( $meta_query ) ) {
				$query->set( 'meta_query', $meta_query );
			}

			$orderby = $query->get( 'orderby' );
			if ( $orderby === 'ig_event_date' ) {
				$query->set( 'meta_key', '_ig_enna_event_date' );
				$query->set( 'orderby', 'meta_value' );
			}
		}
	}

	/* =====================================================
	 *  EVENTO
	 * ===================================================== */

	public static function evento_columns( $cols ) {
		$new = [];
		$new['cb']             = $cols['cb'] ?? '';
		$new['title']          = __( 'Titolo', 'ig-enna' );
		$new['ig_event_date']  = __( 'Quando', 'ig-enna' );
		$new['ig_event_mode']  = __( 'Modalità', 'ig-enna' );
		$new['ig_event_place'] = __( 'Luogo', 'ig-enna' );
		$new['ig_area']        = __( 'Area', 'ig-enna' );
		$new['ig_event_cap']   = __( 'Capienza', 'ig-enna' );
		$new['ig_event_state'] = __( 'Stato', 'ig-enna' );
		$new['date']           = $cols['date'] ?? __( 'Pubblicato', 'ig-enna' );
		return $new;
	}

	public static function evento_column_value( $col, $post_id ) {
		switch ( $col ) {
			case 'ig_event_date':
				$d = get_post_meta( $post_id, '_ig_enna_event_date', true );
				$t = get_post_meta( $post_id, '_ig_enna_event_time', true );
				if ( ! $d ) { echo '—'; break; }
				$ts = strtotime( $d . ' ' . ( $t ?: '00:00' ) );
				echo $ts ? esc_html( date_i18n( 'd M Y', $ts ) ) : esc_html( $d );
				if ( $t ) {
					echo '<br><small>' . esc_html( $t ) . '</small>';
				}
				break;

			case 'ig_event_mode':
				$m = get_post_meta( $post_id, '_ig_enna_event_mode', true );
				$modes = IG_Enna_Evento_Meta::modes();
				echo isset( $modes[ $m ] ) ? '<span class="ig-enna-badge ig-enna-badge--mode-' . esc_attr( $m ) . '">' . esc_html( $modes[ $m ] ) . '</span>' : '—';
				break;

			case 'ig_event_place':
				$place = get_post_meta( $post_id, '_ig_enna_event_place', true );
				$url   = get_post_meta( $post_id, '_ig_enna_event_url', true );
				if ( $place ) {
					echo esc_html( $place );
				} elseif ( $url ) {
					echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( wp_parse_url( $url, PHP_URL_HOST ) ?: $url ) . '</a>';
				} else {
					echo '—';
				}
				break;

			case 'ig_area':
				$terms = get_the_terms( $post_id, 'ig_area' );
				if ( $terms && ! is_wp_error( $terms ) ) {
					foreach ( $terms as $t ) {
						printf(
							'<span class="ig-enna-badge ig-enna-badge--area-%1$s">%2$s</span> ',
							esc_attr( $t->slug ),
							esc_html( $t->name )
						);
					}
				} else {
					echo '—';
				}
				break;

			case 'ig_event_cap':
				$cap = get_post_meta( $post_id, '_ig_enna_event_capacity', true );
				if ( $cap === '' || $cap === null ) { echo '—'; break; }
				global $wpdb;
				$table = $wpdb->prefix . 'ig_enna_event_registrations';
				$reg = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE event_id = %d AND status = %s",
					$post_id, 'registered'
				) );
				printf( '<span class="ig-enna-num">%d / %d</span>', (int) $reg, (int) $cap );
				break;

			case 'ig_event_state':
				$s = get_post_meta( $post_id, '_ig_enna_event_status', true );
				$statuses = IG_Enna_Evento_Meta::statuses();
				if ( ! $s || ! isset( $statuses[ $s ] ) ) { $s = 'open'; }
				printf(
					'<span class="ig-enna-badge ig-enna-badge--evstate-%1$s">%2$s</span>',
					esc_attr( $s ),
					esc_html( $statuses[ $s ] )
				);
				break;
		}
	}

	public static function evento_sortable( $cols ) {
		$cols['ig_event_date'] = 'ig_event_date';
		return $cols;
	}
}
