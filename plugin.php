<?php

/**
 * Plugin Name:       Environment Info
 * Description:       Admin bar label to quick peed on which environment you're on
 * Version:           1.1.0
 * Requires at least: 5.0.0
 * Tested up to:      5.4.2
 * Author:            PALASTHOTEL by Edward
 * Author URI:        http://www.palasthotel.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       environment-info
 * Domain Path:       /languages
 */

namespace Palasthotel\WordPress\EnvironmentInfo;

use WP_Admin_Bar;

const FILTER_IDENTIFY_SITE = "environment_info_identify_site";

add_filter(FILTER_IDENTIFY_SITE, function($site){

	// if site was already identified skip
	if($site != null) return $site;

	// if no site settings found skip
	if(!defined('ENVIRONMENT_INFO_SETTINGS') || !is_array(ENVIRONMENT_INFO_SETTINGS)) return null;

	$settings = ENVIRONMENT_INFO_SETTINGS;
	$activeEvn = array_values(array_filter($settings, function($site){
		if(isset($site["hostname"]) && gethostname() === $site["hostname"]) return true;
		if(isset($site["path"]) && strpos(dirname(__FILE__), $site["path"]) !== false ) return true;
		return false;
	}));

	// if found more than one site skip and error log
	if(count($activeEvn) !== 1){
		error_log(json_encode($activeEvn));
		error_log("Environment Info found more than one matching site...");
		return null;
	}

	return $activeEvn[0];
});

function isValidEnvInfo($env){
	return $env != null && is_array($env) && isset($env['title']) && !empty($env['title']);
}

function admin_bar(){
	/**
	 * @var WP_Admin_Bar $wp_admin_bar
	 */
	global $wp_admin_bar;

	$env = apply_filters(FILTER_IDENTIFY_SITE, null);

	if(!isValidEnvInfo($env)) {
		$env = ['title' => '🤖 Unknown Server'];
	}

	$label = $env['title'];
	$background = (isset($env["background"]))? "background: ".$env["background"].";": "";
	$color = (isset($env["color"]))? "color: ".$env["color"].";":"";

	$wp_admin_bar->add_node( array(
		'id'    => "environment-info",
		'title' => "<div style='margin-left:-10px;padding:0 10px;$background$color'>$label</div>",
	) );

}
add_action( 'admin_bar_menu', __NAMESPACE__.'\admin_bar', 40 );