<?php
/**
 *
 * @link              
 * @since             1.0.0
 * @package           Image_Attributes_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Image Attributes Manager
 * Plugin URI: http://www.wpexpertplugins.com/
 * Description:       A plugin to manage image attributes like alt text in bulk.
 * Version:           1.0.0
 * Author:            WpExpertPlugins
 * Author URI: http://www.wpexpertplugins.com/contact-us/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       image-attributes-manager
 * Domain Path:       /languages
 * Requires at least: 6.2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'IMAGE_ATTRIBUTES_MANAGER_VERSION', '1.0.0' );
define( 'IMAGE_ATTRIBUTES_MANAGER_DIR', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-image-attributes-manager-activator.php
 */
function activate_image_attributes_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-image-attributes-manager-activator.php';
	Image_Attributes_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-image-attributes-manager-deactivator.php
 */
function deactivate_image_attributes_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-image-attributes-manager-deactivator.php';
	Image_Attributes_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_image_attributes_manager' );
register_deactivation_hook( __FILE__, 'deactivate_image_attributes_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-image-attributes-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_image_attributes_manager() {

	$plugin = new Image_Attributes_Manager();
	$plugin->run();

}
run_image_attributes_manager();
