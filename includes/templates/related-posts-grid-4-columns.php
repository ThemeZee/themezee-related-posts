<?php
/**
 * The template for displaying related posts in a four column grid
 *
 * @package ThemeZee Related Posts
 */
 
// Get Related Posts
$related_posts = TZRP_Related_Posts::instance()->get_related_posts();

// Display Related Posts
if( is_object( $related_posts ) and $related_posts->have_posts() ) : 
?>
	
	<div class="related-posts-grid">
		
		<div class="related-posts-columns related-posts-four-columns tzrp-clearfix">
			
		<?php while( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
		
			<div class="related-post-column tzrp-clearfix">
			
				<article id="post-<?php the_ID(); ?>">

					<?php the_post_thumbnail( 'themezee-related-posts' ); ?>
					
					<?php the_title( sprintf( '<h4><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h4>' ); ?>

				</article>
			
			</div>
		
		<?php endwhile; ?>
		
		</div>
	
	</div>
	
<?php 
else : ?>

		<p><?php esc_html_e( 'There are no related posts for this article.', 'themezee-related-posts' ); ?></p>
			
<?php 
endif;
		
// Reset Postdata
wp_reset_postdata();