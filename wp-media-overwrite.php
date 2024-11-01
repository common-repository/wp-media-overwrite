<?php
/**
 * WP Media Overwrite
 *
 * @package       WPMEDIAO
 * @author        Ironikus
 * @license       gplv2-or-later
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   WP Media Overwrite
 * Plugin URI:    https://ironikus.com/downloads/wp-media-overwrite/
 * Description:   Replaces files automatically which have the same file name and are stored within the same folder
 * Version:       1.0.0
 * Author:        Ironikus
 * Author URI:    http://ironikus.com/
 * Text Domain:   wp-media-overwrite
 * Domain Path:   /languages
 * License:       GPLv2 or later
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Media Overwrite. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'WPMEDIAO_NAME',		'WP Media Overwrite' );

// Plugin version
define( 'WPMEDIAO_VERSION',		'1.0.0' );

// Plugin Root File
define( 'WPMEDIAO_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'WPMEDIAO_PLUGIN_BASE',	plugin_basename( WPMEDIAO_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'WPMEDIAO_PLUGIN_DIR',	plugin_dir_path( WPMEDIAO_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'WPMEDIAO_PLUGIN_URL',	plugin_dir_url( WPMEDIAO_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once WPMEDIAO_PLUGIN_DIR . 'core/class-wp-media-overwrite.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Ironikus
 * @since   1.0.0
 * @return  object|Wp_Media_Overwrite
 */
function WPMEDIAO() {
	return Wp_Media_Overwrite::instance();
}

WPMEDIAO();
