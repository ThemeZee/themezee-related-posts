<?php
/*
Plugin Name: ThemeZee Related Posts
Plugin URI: http://themezee.com/addons/replated-posts/
Description: Quickly increase your readers' engagement with your posts by adding Related Posts in the footer of your content. Automatically added Related Posts can increase your internal traffic up to 10%. Just install and activate. 
Author: ThemeZee
Author URI: http://themezee.com/
Version: 1.0
Text Domain: themezee-related-posts
Domain Path: /languages/
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

ThemeZee Related Posts
Copyright(C) 2015, ThemeZee.com - support@themezee.com

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Use class to avoid namespace collisions
if ( ! class_exists( 'ThemeZee_Related_Posts' ) ) :


/**
 * Main ThemeZee_Related_Posts Class
 *
 * @package ThemeZee Related Posts
 */
class ThemeZee_Related_Posts {

	/**
	 * Call all Functions to setup the Plugin
	 *
	 * @uses ThemeZee_Related_Posts::constants() Setup the constants needed
	 * @uses ThemeZee_Related_Posts::includes() Include the required files
	 * @uses ThemeZee_Related_Posts::setup_actions() Setup the hooks and actions
	 * @return void
	 */
	static function setup() {
	
		// Setup Constants
		self::constants();
		
		// Setup Translation
		add_action( 'plugins_loaded', array( __CLASS__, 'translation' ) );
	
		// Include Files
		self::includes();
		
		// Setup Action Hooks
		self::setup_actions();
		
	}
	
	
	/**
	 * Setup plugin constants
	 *
	 * @return void
	 */
	static function constants() {
		
		// Define Plugin Name
		define( 'TZRP_NAME', 'ThemeZee Related Posts' );

		// Define Version Number
		define( 'TZRP_VERSION', '1.0' );
		
		// Define Plugin Name
		define( 'TZRP_PRODUCT_ID', 0 );

		// Define Update API URL
		define( 'TZRP_STORE_API_URL', 'https://themezee.com' ); 

		// Plugin Folder Path
		define( 'TZRP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

		// Plugin Folder URL
		define( 'TZRP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Plugin Root File
		define( 'TZRP_PLUGIN_FILE', __FILE__ );
		
	}
	
	
	/**
	 * Load Translation File
	 *
	 * @return void
	 */
	static function translation() {

		load_plugin_textdomain( 'themezee-related-posts', false, dirname( plugin_basename( TZRP_PLUGIN_FILE ) ) . '/languages/' );
		
	}
	
	
	/**
	 * Include required files
	 *
	 * @return void
	 */
	static function includes() {

		// Include Admin Classes
		require_once TZRP_PLUGIN_DIR . '/includes/admin/class-themezee-addons-page.php';
		require_once TZRP_PLUGIN_DIR . '/includes/admin/class-tzrp-plugin-updater.php';
		
		// Include Settings Classes
		require_once TZRP_PLUGIN_DIR . '/includes/settings/class-tzrp-settings.php';
		require_once TZRP_PLUGIN_DIR . '/includes/settings/class-tzrp-settings-page.php';
		
		// Include Related Posts Files
		require_once TZRP_PLUGIN_DIR . '/includes/class-tzrp-related-posts.php';
		require_once TZRP_PLUGIN_DIR . '/includes/related-posts-template-functions.php';
		
	}
	
	
	/**
	 * Setup Action Hooks
	 *
	 * @see https://codex.wordpress.org/Function_Reference/add_action WordPress Codex
	 * @return void
	 */
	static function setup_actions() {

		// Enqueue Frontend Widget Styles
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
		
		// Register Image Sizes
		add_action( 'init',  array( __CLASS__, 'add_image_size' ) );
		
		// Add related posts to content
		add_filter( 'the_content', array( __CLASS__, 'related_posts_content_filter' ) );
		
		// Add Settings link to Plugin actions
		add_filter( 'plugin_action_links_' . plugin_basename( TZRP_PLUGIN_FILE ), array( __CLASS__, 'plugin_action_links' ) );
		
		// Add Related Posts Box to Add-on Overview Page
		add_action( 'themezee_addons_overview_page', array( __CLASS__, 'addon_overview_page' ) );
		
		// Add License Key admin notice
		add_action( 'admin_notices', array( __CLASS__, 'license_key_admin_notice' ) );
		
		// Add automatic plugin updater from ThemeZee Store API
		add_action( 'admin_init', array( __CLASS__, 'plugin_updater' ), 0 );
		
	}

   	/**
	 * Enqueue Styles
	 *
	 * @return void
	 */
	static function enqueue_styles() {
	
		// Return early if theme handles styling
		if ( current_theme_supports( 'themezee-related-posts' ) ) {
			return;
		}
		
		// Enqueue Plugin Stylesheet
		wp_enqueue_style( 'themezee-related-posts', TZRP_PLUGIN_URL . 'assets/css/themezee-related-posts.css', array(), TZRP_VERSION );
		
	}
	
	/**
	 * Add custom image size for post thumbnails in related posts
	 *
	 * @return void
	 */
	static function add_image_size() {
		
		// Return early if theme handles image sizes
		if ( current_theme_supports( 'themezee-related-posts' ) ) :
			return;
		endif;
		
		add_image_size( 'themezee-related-posts', 480, 300, true );
		
	}
	
	/**
	 * Add related posts to single posts content unless theme handles output
	 *
	 * @uses the_content filter hook
	 * @return void
	 */
	static function related_posts_content_filter( $content ) {
	
		// Return early if theme handles plugin output
		if ( current_theme_supports( 'themezee-related-posts' ) ) {
			return $content;
		}
		
		// Return early if it is not a single post
		if( ! is_singular( 'post' ) ) {
			return $content;
		}
		
		$content .= themezee_related_posts( array( 'echo' => false ) );
		
		return $content;
		
	}
	
	/**
	 * Add Settings link to the plugin actions
	 *
	 * @return array $actions Plugin action links
	 */
	static function plugin_action_links( $actions ) {

		$settings_link = array( 'settings' => sprintf( '<a href="%s">%s</a>', admin_url( 'themes.php?page=themezee-addons&tab=relatedposts' ), __( 'Settings', 'themezee-related-posts' ) ) );
		
		return array_merge( $settings_link, $actions );
	}
	
	/**
	 * Add widget bundle box to addon overview admin page
	 *
	 * @return void
	 */
	static function addon_overview_page() { 
	
		$plugin_data = get_plugin_data( __FILE__ );
		
		?>

		<dl>
			<dt>
				<h4><?php echo esc_html( $plugin_data['Name'] ); ?></h4>
				<span><?php printf( esc_html__( 'Version %s', 'themezee-related-posts' ),  esc_html( $plugin_data['Version'] ) ); ?></span>
			</dt>
			<dd>
				<p><?php echo wp_kses_post( $plugin_data['Description'] ); ?><br/></p>
				<a href="<?php echo admin_url( 'admin.php?page=themezee-addons&tab=relatedposts' ); ?>" class="button button-primary"><?php esc_html_e( 'Plugin Settings', 'themezee-related-posts' ); ?></a>&nbsp;
				<a href="<?php echo esc_url( 'http://themezee.com/docs/replated-posts/' ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'View Documentation', 'themezee-related-posts' ); ?></a>
			</dd>
		</dl>
		
		<?php
	}
	
	/**
	 * Add license key admin notice
	 *
	 * @return void
	 */
	static function license_key_admin_notice() { 
	
		global $pagenow;
	
		// Display only on Plugins page
		if ( 'plugins.php' !== $pagenow  ) {
			return;
		}
		
		// Get Settings
		$options = TZRP_Settings::instance();
		
		if( '' == $options->get( 'license_key' ) ) : ?>
			
			<div class="updated">
				<p>
					<?php printf( __( 'Please enter your license key for the %1$s add-on in order to receive updates and support. <a href="%2$s">Enter License Key</a>', 'themezee-related-posts' ),
						TZRP_NAME,
						admin_url( 'themes.php?page=themezee-addons&tab=relatedposts' ) ); 
					?>
				</p>
			</div>
			
		<?php
		endif;
	
	}
	
	/**
	 * Plugin Updater
	 *
	 * @return void
	 */
	static function plugin_updater() {

		if( ! is_admin() ) :
			return;
		endif;
		
		$options = TZRP_Settings::instance();

		if( $options->get( 'license_key' ) <> '' ) :
			
			$license_key = $options->get( 'license_key' );
			
			// setup the updater
			$tzrp_updater = new TZRP_Plugin_Updater( TZRP_STORE_API_URL, __FILE__, array(
					'version' 	=> TZRP_VERSION,
					'license' 	=> $license_key,
					'item_name' => TZRP_NAME,
					'item_id'   => TZRP_PRODUCT_ID,
					'author' 	=> 'ThemeZee'
				)
			);
			
		endif;
		
	}
	
}

// Run Plugin
ThemeZee_Related_Posts::setup();

endif;