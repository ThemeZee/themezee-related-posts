<?php
/*
Plugin Name: ThemeZee Related Posts
Plugin URI: http://themezee.com/add-ons/related-posts/
Description: Quickly increase your readers' engagement with your posts by adding Related Posts in the footer of your content. Automatically added Related Posts can increase your internal traffic up to 10%. Just install and activate. 
Author: ThemeZee
Author URI: http://themezee.com/
Version: 1.0
Text Domain: themezee-related-posts
Domain Path: /languages/
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Copyright(C) 2015, ThemeZee.com - contact@themezee.com

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Use class to avoid namespace collisions
if ( ! class_exists('ThemeZee_Related_Posts') ) :

/**
 * Main ThemeZee_Related_Posts Class
 *
 * @since 1.0
 */
class ThemeZee_Related_Posts {

	/**
	 * ThemeZee Related Posts Setup
	 *
	 * Calls all Functions to setup the Plugin
	 *
	 * @since 1.0
	 * @static
	 * @uses ThemeZee_Related_Posts::constants() Setup the constants needed
	 * @uses ThemeZee_Related_Posts::includes() Include the required files
	 * @uses ThemeZee_Related_Posts::setup_actions() Setup the hooks and actions
	 * @uses ThemeZee_Related_Posts::updater() Setup the plugin updater
	 */
	static function setup() {
	
		// Setup Constants
		self::constants();
		
		// Include Files
		self::includes();
		
		// Setup Action Hooks
		self::setup_actions();
		
		// Load Translation File
		load_plugin_textdomain( 'themezee-related-posts', false, dirname(plugin_basename(__FILE__)) );
		
	}
	
	
	/**
	 * Setup plugin constants
	 *
	 * @since 1.0
	 * @return void
	 */
	static function constants() {
		
		// Define Plugin Name
		define( 'TZRP_NAME', 'ThemeZee Related Posts');

		// Define Version Number
		define( 'TZRP_VERSION', '1.0' );
		
		// Define Plugin Name
		define( 'TZRP_PRODUCT_ID', 0);

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
	 * Include required files
	 *
	 * @since 1.0
	 * @return void
	 */
	static function includes() {

		// Include Admin Classes
		require_once TZRP_PLUGIN_DIR . '/includes/class-themezee-addons-page.php';
		require_once TZRP_PLUGIN_DIR . '/includes/class-tzrp-plugin-updater.php';
		
		// Include Settings Classes
		require_once TZRP_PLUGIN_DIR . '/includes/settings/class-tzrp-settings.php';
		require_once TZRP_PLUGIN_DIR . '/includes/settings/class-tzrp-settings-page.php';
		
	}
	
	
	/**
	 * Setup Action Hooks
	 *
	 * @since 1.0
	 * @return void
	 */
	static function setup_actions() {

		// Enqueue Frontend Widget Styles
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );
		
		// Add Widget Bundle Box to Add-on Overview Page
		add_action('themezee_addons_overview_page', array( __CLASS__, 'addon_overview_page' ) );
		
		// Add automatic plugin updater from ThemeZee Store API
		add_action( 'admin_init', array( __CLASS__, 'plugin_updater' ), 0 );
		
		$options = TZRP_Settings::instance();
                if($options->get('automatic_display')) {
                    add_filter('the_content', array(__CLASS__, 'related_posts_filter'));
                }
        }

        /**** RELATED POSTS ALGORITHMS ***/
        
        static function related_posts_filter($content) {
            if (is_single()) {
                $content .= '<p>New content</p>';
            }
            return $content;
        }

        static function related_posts_query($settings) {

            $match_fields = array('post_title');
            
            if (!empty($settings['fulltext_search'])) {
                $match_fields[] = 'post_content';
            }

        }


        /******************/
	
	/* Enqueue Widget Styles */
	static function enqueue_styles() {
	
		// Enqueue BCW Plugin Stylesheet
		wp_enqueue_style('themezee-related-posts', self::get_stylesheet() );
		
	}
	
	/* Get Stylesheet URL */
	static function get_stylesheet() {
		
		if ( file_exists( get_stylesheet_directory() . '/css/themezee-related-posts.css' ) )
			$stylesheet = get_stylesheet_directory() . '/css/themezee-related-posts.css';
		elseif ( file_exists( get_template_directory() . '/css/themezee-related-posts.css' ) )
			$stylesheet = get_template_directory() . '/css/themezee-related-posts.css';
		else 
			$stylesheet = TZRP_PLUGIN_URL . '/assets/css/themezee-related-posts.css';
		
		return $stylesheet;
	}
	
	static function addon_overview_page() { 
	
		$plugin_data = get_plugin_data( __FILE__ );
		
		?>

		<dl>
			<dt>
				<h4><?php echo esc_html( $plugin_data['Name'] ); ?></h4>
				<span><?php printf( __( 'Version %s', 'themezee-related-posts'),  esc_html( $plugin_data['Version'] ) ); ?></span>
			</dt>
			<dd>
				<p><?php echo wp_kses_post( $plugin_data['Description'] ); ?><br/></p>
				<a href="<?php echo admin_url( 'admin.php?page=themezee-addons&tab=relatedposts' ); ?>" class="button button-primary"><?php _e('Plugin Settings', 'themezee-related-posts'); ?></a> 
				<a href="<?php echo esc_url( 'http://themezee.com/docs/related-posts/'); ?>" class="button button-secondary" target="_blank"><?php _e('View Documentation', 'themezee-related-posts'); ?></a>
			</dd>
		</dl>
		
		<?php
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

		if( $options->get('license_key') <> '') :
			
			$license_key = $options->get('license_key');
			
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

/* Run Plugin */
ThemeZee_Related_Posts::setup();

endif;
