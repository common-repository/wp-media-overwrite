<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Wp_Media_Overwrite_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		WPMEDIAO
 * @subpackage	Classes/Wp_Media_Overwrite_Run
 * @author		Ironikus
 * @since		1.0.0
 */
class Wp_Media_Overwrite_Run{

	/**
	 * The options panel settings
	 *
	 * @since	1.0.0
	 * @var		array $options_panel The current options of the options panel
	 */
	private $options_panel;

	/**
	 * Our Wp_Media_Overwrite_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->plugin_name = WPMEDIAO()->settings->get_plugin_name();
		$this->options_panel = get_option( 'wp_mediao_options' );
		$this->original_file_name = null;
		$this->current_file_data = null;
		$this->previous_file_data = null;

		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'plugin_action_links_' . WPMEDIAO_PLUGIN_BASE, array( $this, 'add_plugin_action_link' ), 20 );
		add_action( 'admin_menu', array( $this, 'register_custom_admin_menu_pages' ), 20 );
		add_action( 'admin_init', array( $this, 'register_custom_admin_options_panel' ), 20 );

		add_filter( 'wp_handle_upload_prefilter',   array( $this, 'fetch_original_file_name' ), 1 );
		add_filter( 'wp_handle_sideload_prefilter', array( $this, 'fetch_original_file_name' ), 1 );

		add_filter( 'wp_unique_filename', array( $this, 'prepare_final_attachment_name' ), PHP_INT_MAX - 100, 4 );

		add_action( 'add_attachment', array( $this, 'apply_previous_values' ), 10 );
	
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	* Adds action links to the plugin list table
	*
	* @access	public
	* @since	1.0.0
	*
	* @param	array	$links An array of plugin action links.
	*
	* @return	array	An array of plugin action links.
	*/
	public function add_plugin_action_link( $links ) {

		$links['our_shop'] = sprintf( '<a href="%s" target="_blank title="Shop" style="font-weight:700;color:#f1592a;">%s</a>', 'http://ironikus.com/', __( 'Shop', 'wp-media-overwrite' ) );

		return $links;
	}

	/**
	 * Add custom menu pages
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function register_custom_admin_menu_pages(){

		add_submenu_page( 'upload.php', 'WP Media Overwrite', 'WP Media Overwrite', WPMEDIAO()->settings->get_capability( 'default' ), 'wp-media-overwrite', array( $this, 'custom_admin_menu_page_callback' ), 16 );

	}

	/**
	 * Add custom menu page content for the following
	 * menu item: wp-media-overwrite
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function custom_admin_menu_page_callback(){

		$this->options_panel = get_option( 'wp_mediao_options' );

		?>
		<div class="wrap">
			<h1><?php echo __( 'WP Media Overwrite settings', 'wp-media-overwrite' ); ?></h1>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'wp_mediao_options_group' );
					do_settings_sections( 'wpmo-options-panel' );
					submit_button();
				?>
			</form>
		</div>
		<?php

	}

	/**
	 * Register and add the settings
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function register_custom_admin_options_panel(){

		//Register the settings
		register_setting(
			'wp_mediao_options_group', //The option group
			'wp_mediao_options', //The option name
			array( $this, 'sanitize_options_panel' )
		);

		//Add the settings section
		add_settings_section(
			'setting_section_id',
			__( 'Settings', 'wp-media-overwrite' ),
			array( $this, 'print_options_section' ),
			'wpmo-options-panel'
		);

		//Add all settings fields
		add_settings_field(
			'wpmo_copy_meta_data', 
			__( 'Copy Meta Data', 'wp-media-overwrite' ), 
			array( $this, 'wpmo_copy_meta_data_callback' ), 
			'wpmo-options-panel', 
			'setting_section_id'
		);

	}

	/**
	 * Sanitize the registered settings
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @param	array	$input Contains all settings fields
	 *
	 * @return	array	The sanitized $input fields
	 */
	public function sanitize_options_panel( $input ){

		if( isset( $input['wpmo_copy_meta_data'] ) ){
			$input['wpmo_copy_meta_data'] = sanitize_text_field( $input['wpmo_copy_meta_data'] );
		}

		return $input;
	}

	/**
	 * Print the section text for the registered options section
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function print_options_section(){
		print __( 'To customize the functionality based on your needs, please take a look at the settings down below.', 'wp-media-overwrite' );
	}

	/**
	 * Print the content for the wpmo_copy_meta_data setting
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function wpmo_copy_meta_data_callback(){
		printf(
			'<input type="checkbox" id="wpmo_copy_meta_data" name="wp_mediao_options[wpmo_copy_meta_data]" value="1" %s>',
			( intval( $this->options_panel['wpmo_copy_meta_data'] ) === 1 ) ? 'checked' : ''
		);
		print '<p>' . __( 'Check this box to copy the data from the current media element to the new one. This include the title, caption, description, alt and all other custom meta values.', 'wp-media-overwrite' ) . '</p>';
	}

	/**
	 * Fetch the original, unmodified file name 
	 * from the initial request
	 *
	 * @access	public
	 * @since	1.0.0
	 * 
	 * @param	array	$file The file data
	 *
	 * @return	array	$file The file data
	 */
	public function fetch_original_file_name( $file ){

		//Assign original file name
		if( is_array( $file ) && isset( $file['name'] ) ){
			$this->original_file_name = $file['name'];
		}

		return $file;
	}

	/**
	 * Cor elogic that replaces the modified file names 
	 * and deletes all previously created ones with the same 
	 * file and folder name
	 *
	 * @access	public
	 * @since	1.0.0
	 * 
	 * @param	string	$filename The (modified) name of the file
	 * @param	string	$ext The file extension
	 * @param	string	$dir The absolute path of the file
	 * @param	mixed	$unique_filename_callback A callback used to modify the file name
	 *
	 * @return	string	$filename The final file name
	 */
	public function prepare_final_attachment_name( $filename, $ext, $dir, $unique_filename_callback ){

		if( ! empty( $this->original_file_name ) ){
			$filename = $this->original_file_name;
		}

		$this->current_file_data = array(
			'dir' => $dir,
			'filename' => $filename,
		);

		//Fetch previous meta data
		$this->previous_file_data = WPMEDIAO()->media->get_previous_attachments( $dir, $filename );

		WPMEDIAO()->media->remove_previous_attachments( $dir, $filename, $ext );

		return $filename;
	}

	/**
	 * Cor elogic that replaces the modified file names 
	 * and deletes all previously created ones with the same 
	 * file and folder name
	 *
	 * @access	public
	 * @since	1.0.0
	 * 
	 * @param	string	$filename The (modified) name of the file
	 * @param	string	$ext The file extension
	 * @param	string	$dir The absolute path of the file
	 * @param	mixed	$unique_filename_callback A callback used to modify the file name
	 *
	 * @return	string	$filename The final file name
	 */
	public function apply_previous_values( $new_post_id ){
		
		if( intval( $this->options_panel['wpmo_copy_meta_data'] ) !== 1 ){
			return;
		}
		
		if( empty( $this->current_file_data ) ){
			return;
		}
		
		$post_args = array();
		$meta_args = array();
		$restricted_args = array(
			'_wp_attached_file',
			'_wp_attachment_metadata',
		);

		$previous_attachments = WPMEDIAO()->media->get_previous_attachments( $this->current_file_data['dir'], $this->current_file_data['filename'] );
		if( is_array( $previous_attachments ) ){
			foreach( $previous_attachments as $post_id => $data ){

				if( ! empty( $data->post_title ) ){
					$post_args['post_title'] = $data->post_title;
				}

				if( ! empty( $data->post_excerpt ) ){
					$post_args['post_excerpt'] = $data->post_excerpt;
				}

				if( ! empty( $data->post_content ) ){
					$post_args['post_content'] = $data->post_content;
				}
				
				if( isset( $data->post_meta ) ){
					foreach( $data->post_meta as $meta_key => $meta_values ){
						if( in_array( $meta_key, $restricted_args ) ){
							continue;
						}

						if( ! isset( $meta_args[ $meta_key ] ) ){
							$meta_args[ $meta_key ] = array();
						}

						$meta_args[ $meta_key ] = array_merge( $meta_args[ $meta_key ], $meta_values );
					}
				}

			}
		}
		
		error_log( json_encode( $meta_args ) );
		if( ! empty( $post_args ) ){
			$post_args = array_merge( array( 'ID' => $new_post_id ), $post_args );
			wp_update_post( $post_args );
		}

		if( ! empty( $meta_args ) ){
			foreach( $meta_args as $smk => $smv ){
				if( is_array( $smv ) ){
					foreach( $smv as $smvv ){
						add_post_meta( $new_post_id, $smk, $smvv );
					}
				}
			}
		}

	}

}
