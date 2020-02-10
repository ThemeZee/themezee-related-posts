<?php
/*
Plugin Name: ThemeZee Related Posts
Plugin URI: https://themezee.com/plugins/related-posts/
Description: This plugin is an easy way to display related posts on your website. Your visitors are introduced to other relevant content they might be interested in, which leads to an increase in traffic and reduced bounce rates.
Author: ThemeZee
Author URI: https://themezee.com/
Version: 1.0.6
Text Domain: themezee-related-posts
Domain Path: /languages/
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ThemeZee Related Posts
Copyright(C) 2018, ThemeZee.com - support@themezee.com

*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Use class to avoid namespace collisions.
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

			// Setup Constants.
			self::constants();

			// Setup Translation.
			add_action( 'plugins_loaded', array( __CLASS__, 'translation' ) );

			// Include Files.
			self::includes();

			// Setup Action Hooks.
			self::setup_actions();
		}

		/**
		 * Setup plugin constants
		 *
		 * @return void
		 */
		static function constants() {

			// Define Plugin Name.
			define( 'TZRP_NAME', 'ThemeZee Related Posts' );

			// Define Version Number.
			define( 'TZRP_VERSION', '1.0.6' );

			// Define Plugin Name.
			define( 'TZRP_PRODUCT_ID', 51298 );

			// Define Update API URL.
			define( 'TZRP_STORE_API_URL', 'https://themezee.com' );

			// Define Plugin Name.
			define( 'TZRP_LICENSE', '6934e03f8d16074ee58fd950088172e3' );

			// Plugin Folder Path.
			define( 'TZRP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin Folder URL.
			define( 'TZRP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

			// Plugin Root File.
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

			// Include Admin Classes.
			require_once TZRP_PLUGIN_DIR . '/includes/admin/class-themezee-plugins-page.php';
			require_once TZRP_PLUGIN_DIR . '/includes/admin/class-tzrp-plugin-updater.php';

			// Include Settings Classes.
			require_once TZRP_PLUGIN_DIR . '/includes/settings/class-tzrp-settings.php';
			require_once TZRP_PLUGIN_DIR . '/includes/settings/class-tzrp-settings-page.php';

			// Include Related Posts Files.
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

			// Enqueue Frontend Widget Styles.
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles' ) );

			// Register Image Sizes.
			add_action( 'init', array( __CLASS__, 'add_image_size' ) );

			// Add related posts to content.
			add_filter( 'the_content', array( __CLASS__, 'related_posts_content_filter' ) );

			// Add Settings link to Plugin actions.
			add_filter( 'plugin_action_links_' . plugin_basename( TZRP_PLUGIN_FILE ), array( __CLASS__, 'plugin_action_links' ) );

			// Add Related Posts Box to Plugin Overview Page.
			add_action( 'themezee_plugins_overview_page', array( __CLASS__, 'plugin_overview_page' ) );

			// Add License Key admin notice.
			add_action( 'admin_notices', array( __CLASS__, 'license_key_admin_notice' ) );

			// Add automatic plugin updater from ThemeZee Store API.
			add_action( 'admin_init', array( __CLASS__, 'plugin_updater' ), 0 );
		}

		/**
		 * Enqueue Styles
		 *
		 * @return void
		 */
		static function enqueue_styles() {

			// Return early if theme handles styling.
			if ( current_theme_supports( 'themezee-related-posts' ) ) {
				return;
			}

			// Enqueue Plugin Stylesheet.
			wp_enqueue_style( 'themezee-related-posts', TZRP_PLUGIN_URL . 'assets/css/themezee-related-posts.css', array(), TZRP_VERSION );
		}

		/**
		 * Add custom image size for post thumbnails in related posts
		 *
		 * @return void
		 */
		static function add_image_size() {

			// Check if theme defines custom image size.
			if ( current_theme_supports( 'themezee-related-posts' ) ) :

				$theme_support = get_theme_support( 'themezee-related-posts' );

				// Set custom image size.
				if ( isset( $theme_support[0]['thumbnail_size'] ) && is_array( $theme_support[0]['thumbnail_size'] ) ) :

					$thumbnail_size = $theme_support[0]['thumbnail_size'];
					add_image_size( 'themezee-related-posts', $thumbnail_size[0], $thumbnail_size[1], true );

				endif;

			else :

				// Set default image size.
				add_image_size( 'themezee-related-posts', 480, 300, true );

			endif;

		}

		/**
		 * Add related posts to single posts content unless theme handles output
		 *
		 * @uses the_content filter hook
		 * @return void
		 */
		static function related_posts_content_filter( $content ) {

			// Return early if theme handles plugin output.
			if ( current_theme_supports( 'themezee-related-posts' ) ) {
				return $content;
			}

			// Return early if it is not a single post.
			if ( ! is_singular( 'post' ) ) {
				return $content;
			}

			$related_posts = TZRP_Related_Posts::instance( array( 'echo' => false ) );

			return $content . $related_posts->render();
		}

		/**
		 * Add Settings link to the plugin actions
		 *
		 * @return array $actions Plugin action links
		 */
		static function plugin_action_links( $actions ) {

			$settings_link = array( 'settings' => sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=themezee-plugins&tab=relatedposts' ), __( 'Settings', 'themezee-related-posts' ) ) );

			return array_merge( $settings_link, $actions );
		}

		/**
		 * Add widget bundle box to plugin overview admin page
		 *
		 * @return void
		 */
		static function plugin_overview_page() {

			$plugin_data = get_plugin_data( __FILE__ );

			?>

			<dl>
				<dt>
					<h4><?php echo esc_html( $plugin_data['Name'] ); ?></h4>
					<span><?php printf( esc_html__( 'Version %s', 'themezee-related-posts' ), esc_html( $plugin_data['Version'] ) ); ?></span>
				</dt>
				<dd>
					<p><?php echo wp_kses_post( $plugin_data['Description'] ); ?><br/></p>
					<a href="<?php echo admin_url( 'options-general.php?page=themezee-plugins&tab=relatedposts' ); ?>" class="button button-primary"><?php esc_html_e( 'Plugin Settings', 'themezee-related-posts' ); ?></a>&nbsp;
					<a href="<?php echo esc_url( 'https://themezee.com/docs/related-posts-documentation/?utm_source=plugin-overview&utm_medium=button&utm_campaign=related-posts&utm_content=documentation' ); ?>" class="button button-secondary" target="_blank"><?php esc_html_e( 'View Documentation', 'themezee-related-posts' ); ?></a>
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

			// Display only on Plugins and Updates page.
			if ( ! ( 'plugins.php' == $pagenow or 'update-core.php' == $pagenow ) ) {
				return;
			}

			// Get Settings.
			$options = TZRP_Settings::instance();

			if ( 'valid' <> $options->get( 'license_status' ) ) :
				?>

				<div class="updated">
					<p>
						<?php
						printf( __( 'Please activate your license for the %1$s plugin in order to receive updates and support. <a href="%2$s">Activate License</a>', 'themezee-related-posts' ),
							TZRP_NAME,
							admin_url( 'options-general.php?page=themezee-plugins&tab=relatedposts' )
						);
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

			if ( ! is_admin() ) :
				return;
			endif;

			$options = TZRP_Settings::instance();

			if ( 'valid' === $options->get( 'license_status' ) ) :

				// Setup the updater.
				$tzrp_updater = new TZRP_Plugin_Updater( TZRP_STORE_API_URL, __FILE__, array(
					'version'   => TZRP_VERSION,
					'license'   => TZRP_LICENSE,
					'item_name' => TZRP_NAME,
					'item_id'   => TZRP_PRODUCT_ID,
					'author'    => 'ThemeZee',
				) );

			endif;
		}
	}

	// Run Plugin.
	ThemeZee_Related_Posts::setup();

endif;
