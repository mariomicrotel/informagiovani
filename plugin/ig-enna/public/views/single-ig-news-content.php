<?php
/**
 * Singolo articolo news. Variabili: $ig_post (WP_Post), $ig_content (string).
 */
defined( 'ABSPATH' ) || exit;

$terms = get_the_terms( $ig_post->ID, 'ig_area' );
$area  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
$thumb = get_the_post_thumbnail( $ig_post->ID, 'large', [ 'class' => 'ig-enna-news-single__hero' ] );
?>
<article class="ig-enna ig ig-enna-news-single">

	<header class="ig-enna-news-single__head">
		<div class="ig-enna-news-single__meta">
			<?php if ( $area ) : ?>
				<a class="ig-enna-badge ig-enna-badge--area-<?php echo esc_attr( $area->slug ); ?>" href="<?php echo esc_url( add_query_arg( 'ig_area', $area->slug, home_url( '/news/' ) ) ); ?>">
					<?php echo esc_html( $area->name ); ?>
				</a>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $ig_post ) ); ?>"><?php echo esc_html( get_the_date( 'd F Y', $ig_post ) ); ?></time>
			<?php $author = get_user_by( 'id', (int) $ig_post->post_author ); if ( $author ) : ?>
				· <span><?php echo esc_html( $author->display_name ); ?></span>
			<?php endif; ?>
		</div>
		<h1 class="ig-enna-news-single__title"><?php echo esc_html( $ig_post->post_title ); ?></h1>
		<?php if ( $ig_post->post_excerpt ) : ?>
			<p class="ig-enna-news-single__lede"><?php echo esc_html( $ig_post->post_excerpt ); ?></p>
		<?php endif; ?>
	</header>

	<?php if ( $thumb ) : ?>
		<figure class="ig-enna-news-single__figure"><?php echo $thumb; ?></figure>
	<?php endif; ?>

	<div class="ig-enna-prose ig-enna-news-single__body">
		<?php echo $ig_content; ?>
	</div>

	<footer class="ig-enna-news-single__foot">
		<a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="ig-enna-btn ig-enna-btn--ghost ig-enna-btn--sm">
			← <?php esc_html_e( 'Torna a tutte le news', 'ig-enna' ); ?>
		</a>
	</footer>

</article>
