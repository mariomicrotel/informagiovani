<?php
/**
 * Lista opportunità con filtri.
 * Variabili attese: $query (WP_Query), $show_filters (bool), $q, $atts.
 */
defined( 'ABSPATH' ) || exit;

$areas       = get_terms( [ 'taxonomy' => 'ig_area',       'hide_empty' => false ] );
$territori   = get_terms( [ 'taxonomy' => 'ig_territorio', 'hide_empty' => false ] );
$targets     = get_terms( [ 'taxonomy' => 'ig_target',     'hide_empty' => false ] );
$current_area     = isset( $_GET['ig_area'] )       ? sanitize_title( wp_unslash( $_GET['ig_area'] ) )       : '';
$current_target   = isset( $_GET['ig_target'] )     ? sanitize_title( wp_unslash( $_GET['ig_target'] ) )     : '';
$current_territ   = isset( $_GET['ig_territorio'] ) ? sanitize_title( wp_unslash( $_GET['ig_territorio'] ) ) : '';
$current_urg      = isset( $_GET['ig_urg'] )        ? sanitize_key( $_GET['ig_urg'] )                        : '';
$current_q        = isset( $_GET['ig_q'] )          ? sanitize_text_field( wp_unslash( $_GET['ig_q'] ) )     : '';

$base = get_permalink();
?>
<div class="ig-enna ig ig-enna-list-wrap">

	<header class="ig-enna-list-header">
		<h1 class="ig-enna-list-header__title"><?php esc_html_e( 'Opportunità e servizi', 'ig-enna' ); ?></h1>
		<p class="ig-enna-list-header__sub">
			<?php
			printf(
				/* translators: %d = number of results */
				esc_html( _n( '%d risultato', '%d risultati', (int) $query->found_posts, 'ig-enna' ) ),
				(int) $query->found_posts
			);
			?>
		</p>
	</header>

	<?php if ( $show_filters ) : ?>
	<form class="ig-enna-filters" method="get" action="<?php echo esc_url( $base ); ?>">
		<div class="ig-enna-filters__search">
			<input type="search" name="ig_q" value="<?php echo esc_attr( $current_q ); ?>"
				placeholder="<?php esc_attr_e( 'Cerca per titolo o contenuto…', 'ig-enna' ); ?>"
				class="ig-enna-input ig-enna-input--search" />
		</div>

		<select name="ig_area" class="ig-enna-select" onchange="this.form.submit()">
			<option value=""><?php esc_html_e( 'Tutte le aree', 'ig-enna' ); ?></option>
			<?php foreach ( $areas as $t ) : ?>
				<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $current_area, $t->slug ); ?>>
					<?php echo esc_html( $t->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<select name="ig_target" class="ig-enna-select" onchange="this.form.submit()">
			<option value=""><?php esc_html_e( 'Tutti i target', 'ig-enna' ); ?></option>
			<?php foreach ( $targets as $t ) : ?>
				<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $current_target, $t->slug ); ?>>
					<?php echo esc_html( $t->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<select name="ig_territorio" class="ig-enna-select" onchange="this.form.submit()">
			<option value=""><?php esc_html_e( 'Tutti i territori', 'ig-enna' ); ?></option>
			<?php foreach ( $territori as $t ) : ?>
				<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $current_territ, $t->slug ); ?>>
					<?php echo esc_html( $t->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<select name="ig_urg" class="ig-enna-select" onchange="this.form.submit()">
			<option value=""><?php esc_html_e( 'Qualsiasi scadenza', 'ig-enna' ); ?></option>
			<option value="urgent" <?php selected( $current_urg, 'urgent' ); ?>><?php esc_html_e( 'Urgenti (≤ 7gg)', 'ig-enna' ); ?></option>
			<option value="soon"   <?php selected( $current_urg, 'soon' );   ?>><?php esc_html_e( 'In scadenza (≤ 21gg)', 'ig-enna' ); ?></option>
			<option value="open"   <?php selected( $current_urg, 'open' );   ?>><?php esc_html_e( 'Sempre aperte', 'ig-enna' ); ?></option>
		</select>

		<button type="submit" class="ig-enna-btn ig-enna-btn--primary ig-enna-btn--sm"><?php esc_html_e( 'Filtra', 'ig-enna' ); ?></button>

		<?php if ( $current_area || $current_target || $current_territ || $current_urg || $current_q ) : ?>
			<a href="<?php echo esc_url( $base ); ?>" class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm">
				<?php esc_html_e( 'Reset', 'ig-enna' ); ?>
			</a>
		<?php endif; ?>
	</form>
	<?php endif; ?>

	<?php if ( $query->have_posts() ) : ?>
		<div class="ig-enna-list">
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>
				<?php include IG_ENNA_DIR . 'public/views/card-scheda.php'; ?>
			<?php endwhile; ?>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
			<nav class="ig-enna-pagination" aria-label="<?php esc_attr_e( 'Paginazione', 'ig-enna' ); ?>">
				<?php
				$current_page = max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( isset( $_GET['ig_page'] ) ? (int) $_GET['ig_page'] : 1 ) );
				$base_args    = $_GET;
				for ( $i = 1; $i <= (int) $query->max_num_pages; $i++ ) {
					$base_args['ig_page'] = $i;
					$url   = esc_url( add_query_arg( $base_args, $base ) );
					$class = $i === $current_page ? 'ig-enna-pagination__item is-active' : 'ig-enna-pagination__item';
					printf( '<a class="%1$s" href="%2$s">%3$d</a>', esc_attr( $class ), $url, $i );
				}
				?>
			</nav>
		<?php endif; ?>

	<?php else : ?>
		<div class="ig-enna-empty">
			<h3><?php esc_html_e( 'Nessuna opportunità trovata', 'ig-enna' ); ?></h3>
			<p><?php esc_html_e( 'Prova a modificare i filtri o ad ampliare il periodo di scadenza.', 'ig-enna' ); ?></p>
		</div>
	<?php endif; ?>

</div>
