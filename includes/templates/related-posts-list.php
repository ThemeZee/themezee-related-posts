<?php
/**
 * The template for displaying related posts in a simple list
 *
 * @package ThemeZee Related Posts
 */

// Get Related Posts.
$related_posts = TZRP_Related_Posts::instance()->get_related_posts();

// Display Related Posts.
if ( is_object( $related_posts ) and $related_posts->have_posts() ) : ?>

	<ul class="related-posts-list">

	<?php while ( $related_posts->have_posts() ) : $related_posts->the_post(); ?>

		<li id="post-<?php the_ID(); ?>" class="clearfix">

			<?php tzrp_post_thumbnail(); ?>

			<header class="entry-header">

				<?php the_title( sprintf( '<h4 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h4>' ); ?>

			</header><!-- .entry-header -->

			<?php tzrp_entry_meta(); ?>

		</li>

	<?php endwhile; ?>

	</ul>

<?php else : ?>

	<p><?php esc_html_e( 'No related posts found.', 'themezee-related-posts' ); ?></p>

<?php
endif;

// Reset Postdata.
wp_reset_postdata();
