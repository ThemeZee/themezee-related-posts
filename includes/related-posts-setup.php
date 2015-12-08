<?php
/***
 * Related Posts Setup
 *
 * This file adds a basic template function and shortcode to display the Related Posts.
 * Also hooks into themes to display related posts if automatic display is activated.
 *
 * @package ThemeZee Related Posts
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Shows the related posts. This is a wrapper function for the TZRP_Related_Posts class,
 * which should be used in theme templates.
 *
 * @access public
 * @param  array $args Arguments to pass to TZRP_Related_Posts.
 * @return void
 */
function themezee_related_posts( $args = array() ) {

	$related_posts = new TZRP_Related_Posts( $args );

	return $related_posts->render();
}