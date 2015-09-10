<?php
/***
 * TZRP Settings Page Class
 *
 * Adds a new tab on the themezee addons page and displays the settings page.
 *
 * @package ThemeZee Related Posts
 */
 

// Use class to avoid namespace collisions
if ( ! class_exists('TZRP_Settings_Page') ) :

class TZRP_Settings_Page {

	/**
	 * Setup the Settings Page class
	 *
	 * @return void
	*/
	static function setup() {
		
		// Add settings page to addon tabs
		add_filter( 'themezee_addons_settings_tabs', array( __CLASS__, 'add_settings_page' ) );
		
		// Hook settings page to addon page
		add_action( 'themezee_addons_page_relatedposts', array( __CLASS__, 'display_settings_page' ) );
		
		// Enqueue Admin Page Styles
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add settings page to tabs list on themezee add-on page
	 *
	 * @return void
	*/
	static function add_settings_page($tabs) {
			
		// Add Related Posts Settings Page to Tabs List
		$tabs['relatedposts']      = __( 'Related Posts', 'themezee-related-posts' );
		
		return $tabs;
		
	}
	
	/**
	 * Display settings page
	 *
	 * @return void
	*/
	static function display_settings_page() { 
	
		ob_start();
	?>
		
		<div id="tzrp-settings" class="tzrp-settings-wrap">
			
			<h2><?php _e( 'Related Posts', 'themezee-related-posts' ); ?></h2>
			<?php settings_errors(); ?>
			
			<form class="tzrp-settings-form" method="post" action="options.php">
				<?php
					settings_fields('tzrp_settings');
					do_settings_sections('tzrp_settings');
					submit_button();
				?>
			</form>
			
		</div>
<?php
		echo ob_get_clean();
	}
	
	/**
	 * Enqueue file upload js on settings page
	 *
	 * @return void
	*/
	static function enqueue_admin_scripts( $hook ) {

		// Embed stylesheet only on admin settings page
		if( 'appearance_page_themezee-add-ons' != $hook )
			return;
				
		// Enqueue Admin CSS
		wp_enqueue_script( 'tzwb-settings-file-upload', TZRP_PLUGIN_URL . '/assets/js/upload-setting.js', array(), TZRP_VERSION );
		
	}
	
}

// Run TZRP Settings Page Class
TZRP_Settings_Page::setup();

endif;