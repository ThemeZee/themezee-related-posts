<?php
/***
 * Related Posts Query Class
 *
 * The main script to find related posts
 *
 * @package ThemeZee Related Posts
 */
 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Finds and displays related posts based on the current post that's being viewed by the user.
 *
 * @access public
 */
class TZRP_Related_Posts {

	/**
	 * Arguments to find and display related posts
	 *
	 * @access public
	 * @var    array
	 */
	public $args = array();

	/**
	 * Sets up the related posts properties based on function parameters and user options.
	 *
	 * @access public
	 * @param  array   $args  {
	 *     @type string    $before         String to output before related posts.
	 *     @type string    $after          String to output after related posts.
	 *     @type string    $container      Container HTML element. section|div
	 *     @type string    $class          Class for the container HTML element.
	 *     @type string    $post_match     Matching method used to find related posts. categories|tags
	 *     @type string    $order          Order type of related posts. date|comment_count|rand
	 *     @type string    $title          Title displayed above related posts.
	 *     @type string    $layout     	   Layout Style of Related posts. list|three-columns|four-columns
	 *     @type string    $post_count     Maximum Number of related posts.
	 *     @type bool      $echo           Whether to print or return the related posts.
	 * }
	 * @return void
	 */
	public function __construct( $args = array() ) {
		
		// Get Related Posts Settings
		$instance = TZRP_Settings::instance();
		$options = $instance->get_all();
		
		$defaults = array(
			'before'          => '',
			'after'           => '',
			'container'       => 'section',
			'class'           => '',
			'post_match'  	  => $options['post_match'],
			'order'           => $options['order'],
			'before_title'    => '<h3>',
			'title'	          => $options['title'],
			'after_title'     => '</h3>',
			'layout'	      => $options['layout'],
			'post_count'      => $options['post_count'],
			'echo'            => true
		);

		// Parse the arguments with the defaults.
		$this->args = apply_filters( 'themezee_related_posts_args', wp_parse_args( $args, $defaults ) );

	}

	/* ====== Public Methods ====== */

	/**
	 * Formats the HTML output for the related posts list.
	 *
	 * @access public
	 * @return string
	 */
	public function render() {

		// Set up variables that we'll need.
		$related_posts = '';

		// Display title if one was entered in plugin options
		if ( $this->args['title'] <> '' ) {
			$related_posts .= sprintf( '%1$s%2$s%3$s', 
				$this->args['before_title'],
				wp_kses_post( $this->args['title'] ),
				$this->args['after_title']
			);
		}
			
		// Add related posts list
		$related_posts .= $this->posts();
		
		// Wrap the related posts list.
		$related_posts = sprintf(
			'%1$s<%2$s class="themezee-related-posts related-posts %3$s">%4$s</%2$s>%5$s',
			$this->args['before'],
			tag_escape( $this->args['container'] ),
			esc_attr( $this->args['class'] ), 
			$related_posts,
			$this->args['after']
		);

		// Allow developers to filter the related posts HTML.
		$related_posts = apply_filters( 'themezee_related_posts', $related_posts, $this->args );

		if ( false === $this->args['echo'] )
			return $related_posts;

		echo $related_posts;
	}

	/* ====== Protected Methods ====== */
	
	/**
	 * Returns the HTML output of all related posts
	 *
	 * @return string
	 */
	private function posts() {
	
		// Get Related Posts
		$related_posts = $this->get_related_posts();

		/***** Alternate Query ( better if we add caching )
		*	
		*   // Get Related Post IDs
		*	$post_ids = $this->get_related_post_ids();
		*
		*	// No need to query if there is are no featured posts.
		*	if ( ! empty( $post_ids ) ) {
		*
		*		// Get Related Posts from database
		*		$related_posts = new WP_Query( array(
		*			'post__in' => $post_ids,
		*			'ignore_sticky_posts' => true, 
		*			'posts_per_page' => -1
		*			)
		*		);
		*		
		*	}
		*
		************************************** */

		// Display Related Posts
		if( is_object( $related_posts ) and $related_posts->have_posts() ) { 
			
			// Start Output Buffering
			ob_start(); ?>
			
			<ul class="related-posts-list">
			
			<?php while( $related_posts->have_posts() ) : $related_posts->the_post(); ?>
			
				<li id="post-<?php the_ID(); ?>">

					<a href="<?php the_permalink() ?>" rel="bookmark"><?php the_post_thumbnail('category_posts_wide_thumb'); ?></a>

					<?php the_title( sprintf( '<h1 class="entry-title post-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' ); ?>

				</li>
			
			<?php endwhile; ?>
			
			</ul>
		
		<?php
			// Write Post Output
			$post_output = ob_get_contents();
		
			// Clean Output Buffer
			ob_end_clean();
		
		} else {
		
			$post_output = __( 'There are no related posts for this article.', 'themezee-related-posts' );
			
		}
		
		// Reset Postdata
		wp_reset_postdata();

		return $post_output;
	}
	
	/**
	 * Get related post IDs
	 *
	 * This function will return an array containing the post IDs of all related posts.
	 *
	 * @return array Array of post IDs.
	 */
	private function get_related_post_ids() {
	
		// Get Related Posts
		$related_posts = $this->get_related_posts();
		
		// Ensure correct format before return.
		$related_posts_ids = wp_list_pluck( $related_posts->posts, 'ID' );
		$related_posts_ids = array_map( 'absint', $related_posts_ids );
		
		return $related_posts_ids;
	}
	
	/**
	 * Get related posts
	 *
	 * @return array
	 */
	private function get_related_posts() {
	
		// Check if single post is viewed
		if( ! is_singular( 'post' ) ) {
			return array();
		}
		
		// Set Post ID
		$post_id = get_the_ID();
		
		// Choose Post Matching Method
		if ( 'tags' == $this->args['post_match'] ) {
			
			$related_posts = $this->get_related_posts_by_tags( $post_id );
			
		} elseif ( 'categories_tags' == $this->args['post_match'] ) {
			
			$related_posts = $this->get_related_posts_by_categories_and_tags( $post_id );
			
		} else {
		
			$related_posts = $this->get_related_posts_by_categories( $post_id );
			
		}
		
		return $related_posts;
		
	}
	
	/**
	 * Get related posts by categories
	 *
	 * This function will find all posts using the same categories
	 *
	 * @return array Array of post IDs.
	 */
	private function get_related_posts_by_categories( $post_id ) {
	
		// Get post categories from single post
		$categories = get_the_terms( $post_id, 'category' );
		
		// Get Category IDs
		$category_ids = wp_list_pluck( $categories, 'term_id' );

		// Get related posts from database
		$related_posts = new WP_Query( array(
			'category__in' => $category_ids,
			'ignore_sticky_posts' => true, 
			'post__not_in' => array( $post_id ), // Exclude current viewed post
			'posts_per_page' => (int)$this->args['post_count'],
			'orderby' => $this->args['order']
			)
		);

		return $related_posts;
	}
	
	/**
	 * Get related posts by tags
	 *
	 * This function will find all posts using the same tags
	 *
	 * @return array Array of post IDs.
	 */
	private function get_related_posts_by_tags( $post_id ) {
	
		// Get post tags from single post
		$tags = get_the_terms( $post_id, 'post_tag' );
		
		// Get Tag IDs
		$tag_ids = wp_list_pluck( $tags, 'term_id' );

		// Get related posts from database
		$related_posts = new WP_Query( array(
			'tag__in' => $tag_ids,
			'ignore_sticky_posts' => true, 
			'post__not_in' => array( $post_id ), // Exclude current viewed post
			'posts_per_page' => (int)$this->args['post_count'],
			'orderby' => $this->args['order']
			)
		);

		return $related_posts;
	}
	
	/**
	 * Get related posts by categories and tags
	 *
	 * This function will find all posts using the same categories and tags
	 *
	 * @return array Array of post IDs.
	 */
	private function get_related_posts_by_categories_and_tags( $post_id ) {
	
		// Get post categories and tags from single post
		$tags = get_the_terms( $post_id, 'post_tag' );
		$categories = get_the_terms( $post_id, 'category' );
		
		// Get Category and Tag IDs
		$tag_ids = wp_list_pluck( $tags, 'term_id' );
		$category_ids = wp_list_pluck( $categories, 'term_id' );

		// Get related posts from database
		$related_posts = new WP_Query( array(
			'ignore_sticky_posts' => true, 
			'post__not_in' => array( $post_id ), // Exclude current viewed post
			'posts_per_page' => (int)$this->args['post_count'],
			'orderby' => $this->args['order'],
			'tax_query' => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $category_ids
					),
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'term_id',
					'terms'    => $tag_ids
					)
				)
			)
		);

		return $related_posts;
	}

}