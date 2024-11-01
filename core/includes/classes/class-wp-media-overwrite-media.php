<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Wp_Media_Overwrite_Media
 *
 * This class contains the core functionality for managing
 * media related features.
 *
 * @package		WPMEDIAO
 * @subpackage	Classes/Wp_Media_Overwrite_Media
 * @author		Ironikus
 * @since		1.0.0
 */
class Wp_Media_Overwrite_Media{

    function __construct(){
        $this->previous_attachments = array();
    }

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

     /**
	 * Get the validated file name as required by 
     * the meta field _wp_attached_file
	 *
	 * @access	public
	 * @since	1.0.0
	 * 
     * @param	string	$dir The absolute path of the file
	 * @param	string	$filename The (modified) name of the file
	 *
	 * @return	mixed   The validated file name on success, null on failure
	 */
     public function validate_attached_file_name( $dir, $filename ){

        $validated_file_name = null;
        $validated_path = null;
        
        if( empty( $dir ) || empty( $filename ) ){
            return $validated_file_name;
        }

		$upload_dir = wp_upload_dir();
		if( is_array( $upload_dir ) && isset( $upload_dir['basedir'] ) ){
			$validated_path = str_replace( $upload_dir['basedir'], '', $dir );
			if( ! empty( $validated_path ) ){
				$validated_file_name = trim( $validated_path, '/' ) . '/' . $filename;
			}
		}

        return $validated_file_name;;
     }

     /**
	 * Fetch all previous attachments for a given file
	 *
	 * @access	public
	 * @since	1.0.0
	 * 
     * @param	string	$dir The absolute path of the file
	 * @param	string	$filename The (modified) name of the file
	 *
	 * @return	array   The previous attachments
	 */
     public function get_previous_attachments( $dir, $filename ){

        if( ! empty( $this->previous_attachments ) ){
            return $this->previous_attachments;
        }

        $previous_files = array();
        $validated_file_name = $this->validate_attached_file_name( $dir, $filename );

		if( ! empty( $validated_file_name ) ){
			$arguments = array(
				'numberposts'   => -1,
				'meta_key'      => '_wp_attached_file',
				'meta_value'    => $validated_file_name,
				'post_type'     => 'attachment'
			);
			$previous_files_data = get_posts($arguments);
            if( ! is_wp_error( $previous_files_data ) ){
                foreach( $previous_files_data as $single ){
                    $single->post_meta = get_post_meta( $single->ID );
                    $previous_files[ $single->ID ] =  $single;
                }
            }
        }
        
        $this->previous_attachments = $previous_files;
        
        return $previous_files;
	}

     /**
	 * Cor elogic that replaces the modified file names 
	 * and deletes all previously created ones with the same 
	 * file and folder name
	 *
	 * @access	public
	 * @since	1.0.0
	 * 
     * @param	string	$dir The absolute path of the file
	 * @param	string	$filename The (modified) name of the file
	 * @param	string	$ext The file extension
	 *
	 * @return	void
	 */
     public function remove_previous_attachments(  $dir, $filename, $ext  ){
		
        $attachments_to_remove = $this->get_previous_attachments( $dir, $filename );
		
        foreach($attachments_to_remove as $a){
            wp_delete_attachment($a->ID, true);
        }
		
	}

}
