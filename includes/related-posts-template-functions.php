<?php
/**
 * Related Posts Template Functions
 *
 * This file adds a basic template function and shortcode to display the Related Posts.
 * Also includes template functions for showing thumbnails and meta data of posts.
 *
 * @package ThemeZee Related Posts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shows the related posts. This is a wrapper function for the TZRP_Related_Posts class,
 * which should be used in theme templates.
 *
 * @access public
 * @param  array $args Arguments to pass to TZRP_Related_Posts.
 * @return void
 */
function themezee_related_posts( $args = array() ) {

	// Return early if it is not a single post.
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$related_posts = TZRP_Related_Posts::instance( $args );

	return $related_posts->render();
}

/**
 * Shows the featured image of posts or default thumbnail
 *
 * @access public
 * @return void
 */
function tzrp_post_thumbnail() {

	// Get Options from Database.
	$options = TZRP_Settings::instance();
	$post_content = $options->get( 'post_content' );

	if ( true == $post_content['thumbnails'] && has_post_thumbnail() ) {

		echo '<a href="' . esc_url( get_the_permalink() ) . '" rel="bookmark">';
		the_post_thumbnail( 'themezee-related-posts' );
		echo '</a>';

	}
}

/**
 * Displays the date and author of related posts
 *
 * @access public
 * @return void
 */
function tzrp_entry_meta() {

	// Get Options from Database.
	$options = TZRP_Settings::instance();
	$post_content = $options->get( 'post_content' );

	// Display Postmeta.
	if ( true == $post_content['date'] or true == $post_content['author'] ) : ?>

		<div class="entry-meta">

		<?php // Display Date unless user has deactivated it via settings.
		if ( true === $post_content['date'] ) :

			 tzrp_meta_date();

		endif;

		// Display Author unless user has deactivated it via settings.
		if ( true === $post_content['author'] ) :

			 tzrp_meta_author();

		endif; ?>

		</div>

	<?php
	endif;
}

/**
 * Displays the post date
 *
 * @access public
 * @return void
 */
function tzrp_meta_date() {

	$time_string = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date published updated" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	echo '<span class="meta-date">' . $time_string . '</span>';
}

/**
 * Displays the post author
 *
 * @access public
 * @return void
 */
function tzrp_meta_author() {

	$author_string = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( esc_html__( 'View all posts by %s', 'tzrp' ), get_the_author() ) ),
		esc_html( get_the_author() )
	);

	echo '<span class="meta-author"> ' . $author_string . '</span>';
}
