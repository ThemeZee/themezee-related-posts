<?php
/**
 * TZRP Settings Page Class
 *
 * Adds a new tab on the themezee plugins page and displays the settings page.
 *
 * @package ThemeZee Related Posts
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Use class to avoid namespace collisions.
if ( ! class_exists( 'TZRP_Settings_Page' ) ) :

	class TZRP_Settings_Page {

		/**
		 * Setup the Settings Page class
		 *
		 * @return void
		 */
		static function setup() {

			// Add settings page to plugin tabs.
			add_filter( 'themezee_plugins_settings_tabs', array( __CLASS__, 'add_settings_page' ) );

			// Hook settings page to plugin page.
			add_action( 'themezee_plugins_page_relatedposts', array( __CLASS__, 'display_settings_page' ) );

		}

		/**
		 * Add settings page to tabs list on themezee plugin page
		 *
		 * @return array Tabs
		 */
		static function add_settings_page( $tabs ) {

			// Add Related Posts Settings Page to Tabs List.
			$tabs['relatedposts'] = esc_html__( 'Related Posts', 'themezee-related-posts' );

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

				<h1><?php esc_html_e( 'Related Posts', 'themezee-related-posts' ); ?></h1>

				<form class="tzrp-settings-form" method="post" action="options.php">
					<?php
						settings_fields( 'tzrp_settings' );
						do_settings_sections( 'tzrp_settings' );
						submit_button();
					?>
				</form>

			</div>

			<?php
			echo ob_get_clean();
		}
	}

	// Run TZRP Settings Page Class.
	TZRP_Settings_Page::setup();

endif;
