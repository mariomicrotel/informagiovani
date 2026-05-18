<?php
/**
 * Lista news/blog. Variabili: $query (WP_Query).
 */
defined( 'ABSPATH' ) || exit;

$areas        = get_terms( [ 'taxonomy' => 'ig_area', 'hide_empty' => false ] );
$current_area = isset( $_GET['ig_area'] ) ? sanitize_title( wp_unslash( $_GET['ig_area'] ) ) : '';
$base         = get_permalink();
?>
<div class="ig-enna ig ig-enna-list-wrap">

	<header class="ig-enna-list-header">
		<h1 class="ig-enna-list-header__title"><?php esc_html_e( 'News e aggiornamenti', 'ig-enna' ); ?></h1>
		<p class="ig-enna-list-header__sub">
			<?php
			printf(
				/* translators: %d = numero risultati */
				esc_html( _n( '%d articolo', '%d articoli', (int) $query->found_posts, 'ig-enna' ) ),
				(int) $query->found_posts
			);
			?>
		</p>
	</header>

	<form class="ig-enna-filters" method="get" action="<?php echo esc_url( $base ); ?>" role="search" aria-label="<?php esc_attr_e( 'Filtri news', 'ig-enna' ); ?>">
		<label class="screen-reader-text" for="ig-enna-news-area"><?php esc_html_e( 'Area', 'ig-enna' ); ?></label>
		<select id="ig-enna-news-area" name="ig_area" class="ig-enna-select" aria-label="<?php esc_attr_e( 'Filtra per area', 'ig-enna' ); ?>" onchange="this.form.submit()">
			<option value=""><?php esc_html_e( 'Tutte le aree', 'ig-enna' ); ?></option>
			<?php foreach ( $areas as $t ) : ?>
				<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $current_area, $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php if ( $current_area ) : ?>
			<a href="<?php echo esc_url( $base ); ?>" class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm"><?php esc_html_e( 'Reset', 'ig-enna' ); ?></a>
		<?php endif; ?>
	</form>

	<?php if ( $query->have_posts() ) : ?>
		<div class="ig-enna-list ig-enna-news-list">
			<?php while ( $query->have_posts() ) : $query->the_post();
				$thumb = has_post_thumbnail() ? get_the_post_thumbnail( null, 'medium', [ 'class' => 'ig-enna-news-card__img' ] ) : '';
				$terms = get_the_terms( get_the_ID(), 'ig_area' );
				$area  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
			?>
				<article class="ig-enna-news-card">
					<?php if ( $thumb ) : ?>
						<a class="ig-enna-news-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"><?php echo $thumb; ?></a>
					<?php endif; ?>
					<div class="ig-enna-news-card__body">
						<div class="ig-enna-news-card__meta">
							<?php if ( $area ) : ?>
								<span class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area->slug ); ?>"><?php echo esc_html( $area->name ); ?></span>
							<?php endif; ?>
							<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></time>
						</div>
						<h2 class="ig-enna-news-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p class="ig-enna-news-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
						<a class="ig-enna-news-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leggi l\'articolo →', 'ig-enna' ); ?></a>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php if ( $query->max_num_pages > 1 ) : ?>
			<nav class="ig-enna-pagination" aria-label="<?php esc_attr_e( 'Paginazione', 'ig-enna' ); ?>">
				<?php
				$current_page = max( 1, isset( $_GET['ig_page'] ) ? (int) $_GET['ig_page'] : 1 );
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
			<h3><?php esc_html_e( 'Nessuna news pubblicata', 'ig-enna' ); ?></h3>
			<p><?php esc_html_e( 'Torna a trovarci: gli operatori pubblicano regolarmente novità su bandi e iniziative.', 'ig-enna' ); ?></p>
		</div>
	<?php endif; ?>

</div>
