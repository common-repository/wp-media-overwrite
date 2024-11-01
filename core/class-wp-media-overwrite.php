<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Wp_Media_Overwrite' ) ) :

	/**
	 * Main Wp_Media_Overwrite Class.
	 *
	 * @package		WPMEDIAO
	 * @subpackage	Classes/Wp_Media_Overwrite
	 * @since		1.0.0
	 * @author		Ironikus
	 */
	final class Wp_Media_Overwrite {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Wp_Media_Overwrite
		 */
		private static $instance;

		/**
		 * WPMEDIAO helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Wp_Media_Overwrite_Helpers
		 */
		public $helpers;

		/**
		 * WPMEDIAO settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Wp_Media_Overwrite_Settings
		 */
		public $settings;

		/**
		 * WPMEDIAO media object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Wp_Media_Overwrite_Media
		 */
		public $media;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'wp-media-overwrite' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'wp-media-overwrite' ), '1.0.0' );
		}

		/**
		 * Main Wp_Media_Overwrite Instance.
		 *
		 * Insures that only one instance of Wp_Media_Overwrite exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Wp_Media_Overwrite	The one true Wp_Media_Overwrite
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Wp_Media_Overwrite ) ) {
				self::$instance					= new Wp_Media_Overwrite;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Wp_Media_Overwrite_Helpers();
				self::$instance->settings		= new Wp_Media_Overwrite_Settings();
				self::$instance->media			= new Wp_Media_Overwrite_Media();

				//Fire the plugin logic
				new Wp_Media_Overwrite_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'WPMEDIAO/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once WPMEDIAO_PLUGIN_DIR . 'core/includes/classes/class-wp-media-overwrite-helpers.php';
			require_once WPMEDIAO_PLUGIN_DIR . 'core/includes/classes/class-wp-media-overwrite-settings.php';
			require_once WPMEDIAO_PLUGIN_DIR . 'core/includes/classes/class-wp-media-overwrite-media.php';

			require_once WPMEDIAO_PLUGIN_DIR . 'core/includes/classes/class-wp-media-overwrite-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'wp-media-overwrite', FALSE, dirname( plugin_basename( WPMEDIAO_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.