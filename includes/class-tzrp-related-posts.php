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
	 *     @type string    $container      Container HTML element. nav|div
	 *     @type string    $before         String to output before breadcrumb menu.
	 *     @type string    $after          String to output after breadcrumb menu.
	 *     @type bool      $show_on_front  Whether to show when `is_front_page()`.
	 *     @type bool      $network        Whether to link to the network main site (multisite only).
	 *     @type bool      $show_title     Whether to show the title (last item) in the trail.
	 *     @type bool      $show_browse    Whether to show the breadcrumb menu header.
	 *     @type array     $labels         Text labels. @see TZRP_Related_Posts::set_labels()
	 *     @type array     $post_taxonomy  Taxonomies to use for post types. @see TZRP_Related_Posts::set_post_taxonomy()
	 *     @type bool      $echo           Whether to print or return the breadcrumbs.
	 * }
	 * @return void
	 */
	public function __construct( $args = array() ) {
		
		// Get Related Posts Settings
		$instance = TZRP_Settings::instance();
		$options = $instance->get_all();
		
		$defaults = array(
			'container'       => 'nav',
			'before'          => '',
			'after'           => '',
			'separator'  	  => $options['separator'],
			'show_on_front'   => $options['front_page'],
			'network'         => false,
			'show_title'      => true,
			'show_browse'     => true,
			'browse_text'	  => $options['browse_text'],
			'labels'          => array(),
			'post_taxonomy'   => array(),
			'echo'            => true
		);

		// Parse the arguments with the defaults.
		$this->args = apply_filters( 'themezee_related_posts_args', wp_parse_args( $args, $defaults ) );

	}

	/* ====== Public Methods ====== */

	/**
	 * Formats the HTML output for the related posts list.
	 *
	 * @since  0.6.0
	 * @access public
	 * @return string
	 */
	public function render() {

		// Set up variables that we'll need.
		$related_posts = '';

		// Render

		// Allow developers to filter the related posts HTML.
		$related_posts = apply_filters( 'themezee_related_posts', $related_posts, $this->args );

		if ( false === $this->args['echo'] )
			return $related_posts;

		echo $related_posts;
	}

	/* ====== Protected Methods ====== */

}