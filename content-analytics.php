<?php

/**
 * @link              http://lingotek.com
 * @since             1.0.0
 * @package           Content_Analytics
 *
 * @wordpress-plugin
 * Plugin Name:       Content Analytics
 * Plugin URI:        https://wordpress.org/plugins/content-analytics
 * Description:       Empowers site administrators by providing insight and analytics into your content.
 * Version:           1.0.0
 * Author:            Lingotek
 * Author URI:        http://lingotek.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       content-analytics
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-content-analytics-activator.php
 */
function activate_content_analytics() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-content-analytics-activator.php';
	Content_Analytics_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-content-analytics-deactivator.php
 */
function deactivate_content_analytics() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-content-analytics-deactivator.php';
	Content_Analytics_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_content_analytics' );
register_deactivation_hook( __FILE__, 'deactivate_content_analytics' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-content-analytics.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_content_analytics() {

	$plugin = new Content_Analytics();
	$plugin->run();

}
run_content_analytics();
