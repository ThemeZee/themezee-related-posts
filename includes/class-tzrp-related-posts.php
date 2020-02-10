<?php
/**
 * Related Posts Query Class
 *
 * The main script to find related posts
 *
 * @package ThemeZee Related Posts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Finds and displays related posts based on the current post that's being viewed by the user.
 *
 * @access public
 */
class TZRP_Related_Posts {
	/** Singleton *************************************************************/

	/**
	 * The one true TZRP_Related_Posts instance
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Arguments for related posts
	 *
	 * @access public
	 * @var    array
	 */
	public $args = array();

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return TZRP_Related_Posts A single instance of this class.
	 */
	public static function instance( $args = array() ) {

		if ( null == self::$instance ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}

	/**
	 * Sets up the related posts properties based on function parameters and user options.
	 *
	 * @access public
	 * @param  array $args {
	 *     @type string    $before         String to output before related posts.
	 *     @type string    $after          String to output after related posts.
	 *     @type string    $container      Container HTML element. section|div
	 *     @type string    $class          Class for the container HTML element.
	 *     @type string    $post_match     Matching method used to find related posts. categories|tags
	 *     @type string    $order          Order type of related posts. date|comment_count|rand
	 *     @type string    $title          Title displayed above related posts.
	 *     @type string    $layout         Layout Style of Related posts. list|three-columns|four-columns
	 *     @type string    $post_count     Maximum Number of related posts.
	 *     @type bool      $echo           Whether to print or return the related posts.
	 * }
	 * @return void
	 */
	public function __construct( $args = array() ) {

		// Get Related Posts Settings.
		$instance = TZRP_Settings::instance();
		$options  = $instance->get_all();

		$defaults = array(
			'before'       => '',
			'after'        => '',
			'container'    => 'section',
			'class'        => '',
			'post_match'   => $options['post_match'],
			'order'        => $options['order'],
			'before_title' => '<h3>',
			'title'        => $options['title'],
			'after_title'  => '</h3>',
			'layout'       => $options['layout'],
			'post_count'   => $options['post_count'],
			'echo'         => true,
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

		// Display title if one was entered in plugin options.
		if ( '' !== $this->args['title'] ) {
			$related_posts .= sprintf( '%1$s%2$s%3$s',
				$this->args['before_title'],
				wp_kses_post( $this->args['title'] ),
				$this->args['after_title']
			);
		}

		// Add related posts list.
		$related_posts .= $this->related_posts_template();

		// Wrap the related posts list.
		$related_posts = sprintf(
			'%1$s<%2$s class="themezee-related-posts %3$s">%4$s</%2$s>%5$s',
			$this->args['before'],
			tag_escape( $this->args['container'] ),
			esc_attr( $this->args['class'] ),
			$related_posts,
			$this->args['after']
		);

		// Allow developers to filter the related posts HTML.
		$related_posts = apply_filters( 'themezee_related_posts_html', $related_posts, $this->args );

		if ( false === $this->args['echo'] ) {
			return $related_posts;
		}

		echo $related_posts;
	}

	/**
	 * Get related posts
	 *
	 * @access public
	 * @return array
	 */
	public function get_related_posts() {

		// Get Related Posts.
		return $this->find_related_posts();

	}

	/* ====== Protected Methods ====== */

	/**
	 * Returns the HTML output of all related posts
	 *
	 * @return string
	 */
	private function related_posts_template() {

		// Start Output Buffering.
		ob_start();

		// Template File.
		$file = 'related-posts-' . esc_attr( $this->args['layout'] ) . '.php';

		// Check if the theme defines own template files for related posts.
		if ( current_theme_supports( 'themezee-related-posts' ) and locate_template( 'template-parts/' . $file ) <> '' ) {

			locate_template( 'template-parts/' . $file, true, true );

		} else {

			load_template( TZRP_PLUGIN_DIR . 'includes/templates/' . $file, true );

		}

		// Write Output Buffer.
		$post_output = ob_get_contents();

		// Delete Output Buffer.
		ob_end_clean();

		return $post_output;
	}

	/**
	 * Find related posts based on matching method
	 *
	 * @return array
	 */
	private function find_related_posts() {

		// Check if single post is viewed.
		if ( ! is_singular( 'post' ) ) {
			return array();
		}

		// Set Post ID.
		$post_id = get_the_ID();

		// Choose Post Matching Method.
		if ( 'tags' == $this->args['post_match'] ) {

			$related_posts = $this->find_related_posts_by_tags( $post_id );

		} elseif ( 'categories_tags' == $this->args['post_match'] ) {

			$related_posts = $this->find_related_posts_by_categories_and_tags( $post_id );

		} else {

			$related_posts = $this->find_related_posts_by_categories( $post_id );

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
	private function find_related_posts_by_categories( $post_id ) {

		// Get post categories from single post.
		$categories = get_the_terms( $post_id, 'category' );

		// Get Category IDs.
		$category_ids = wp_list_pluck( $categories, 'term_id' );

		// Get related posts from database.
		$related_posts = new WP_Query( array(
			'post_type'           => 'post',
			'category__in'        => $category_ids,
			'ignore_sticky_posts' => true,
			'post__not_in'        => array( $post_id ), // Exclude current viewed post.
			'posts_per_page'      => (int) $this->args['post_count'],
			'orderby'             => $this->args['order'],
		) );

		return $related_posts;
	}

	/**
	 * Get related posts by tags
	 *
	 * This function will find all posts using the same tags
	 *
	 * @return array Array of post IDs.
	 */
	private function find_related_posts_by_tags( $post_id ) {

		// Get post tags from single post.
		$tags = get_the_terms( $post_id, 'post_tag' );

		// Return related posts by category if post has no tags.
		if ( empty( $tags ) ) {
			return $this->find_related_posts_by_categories( $post_id );
		}

		// Get Tag IDs.
		$tag_ids = wp_list_pluck( $tags, 'term_id' );

		// Get related posts from database.
		$related_posts = new WP_Query( array(
			'post_type'           => 'post',
			'tag__in'             => $tag_ids,
			'ignore_sticky_posts' => true,
			'post__not_in'        => array( $post_id ), // Exclude current viewed post.
			'posts_per_page'      => (int) $this->args['post_count'],
			'orderby'             => $this->args['order'],
		) );

		return $related_posts;
	}

	/**
	 * Get related posts by categories and tags
	 *
	 * This function will find all posts using the same categories and tags
	 *
	 * @return array Array of post IDs.
	 */
	private function find_related_posts_by_categories_and_tags( $post_id ) {

		// Get post categories and tags from single post.
		$tags = get_the_terms( $post_id, 'post_tag' );
		$categories = get_the_terms( $post_id, 'category' );

		// Return related posts by category if post has no tags.
		if ( empty( $tags ) ) {
			return $this->find_related_posts_by_categories( $post_id );
		}

		// Get Category and Tag IDs.
		$tag_ids = wp_list_pluck( $tags, 'term_id' );
		$category_ids = wp_list_pluck( $categories, 'term_id' );

		// Get related posts from database.
		$related_posts = new WP_Query( array(
			'post_type'           => 'post',
			'ignore_sticky_posts' => true,
			'post__not_in'        => array( $post_id ), // Exclude current viewed post.
			'posts_per_page'      => (int) $this->args['post_count'],
			'orderby'             => $this->args['order'],
			'tax_query'           => array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $category_ids,
				),
				array(
					'taxonomy' => 'post_tag',
					'field'    => 'term_id',
					'terms'    => $tag_ids,
				),
			),
		) );

		return $related_posts;
	}
}
