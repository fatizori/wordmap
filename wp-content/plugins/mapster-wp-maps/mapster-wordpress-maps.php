<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mapster.me
 * @since             1.0.0
 * @package           Mapster_Wordpress_Maps
 *
 * @wordpress-plugin
 * Plugin Name:       Mapster WP Maps
 * Plugin URI:        https://wpmaps.mapster.me/
 * Description:       Wordpress Maps is the smoothest, easiest way to make maps for your site. No API keys required.
 * Version:           0.5.5
 * Author:            Mapster Technology Inc
 * Author URI:        https://mapster.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mapster-wp-maps
 * Domain Path:       /mapster-wp-maps
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
JS BUILD COMMANDS
terser .\admin\js\mapster-wordpress-maps-constants.js .\admin\js\mapster-wordpress-maps-custom-controls.js .\admin\js\mapster-wordpress-maps-pointers.js .\admin\js\mapster-wordpress-maps-utils.js .\admin\js\mapster-wordpress-maps.js --compress --mangle --output admin\js\dist\mwp.js
terser .\admin\js\mapster-wordpress-maps-popup.js --compress --mangle --output .\admin\js\dist\mwp-popup.js
*/

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MAPSTER_WORDPRESS_MAPS_VERSION', '0.5.5' );

if ( ! class_exists( 'ACF' ) ) {
	include_once( plugin_dir_path( __FILE__ ) . 'includes/acf/acf.php' );
	add_filter('acf/settings/url', 'my_acf_settings_url');
	function my_acf_settings_url( $url ) {
	    return plugin_dir_url( __FILE__ ) . 'includes/acf/';
	}
	add_filter('acf/settings/show_admin', 'my_acf_settings_show_admin');
	function my_acf_settings_show_admin( $show_admin ) {
	    return false;
	}
}
include_once( plugin_dir_path( __FILE__ ) . 'includes/acf-mapster-map/acf-mapster-map.php' );
include_once( plugin_dir_path( __FILE__ ) . 'admin/includes/acf-map-fields.php' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mapster-wordpress-maps-activator.php
 */
function activate_mapster_wordpress_maps() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mapster-wordpress-maps-activator.php';
	Mapster_Wordpress_Maps_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mapster-wordpress-maps-deactivator.php
 */
function deactivate_mapster_wordpress_maps() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mapster-wordpress-maps-deactivator.php';
	Mapster_Wordpress_Maps_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mapster_wordpress_maps' );
register_deactivation_hook( __FILE__, 'deactivate_mapster_wordpress_maps' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mapster-wordpress-maps.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mapster_wordpress_maps() {

	$plugin = new Mapster_Wordpress_Maps();
	$plugin->run();

}
run_mapster_wordpress_maps();
