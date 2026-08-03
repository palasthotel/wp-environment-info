<?php

/**
 * Plugin Name:       Environment Info
 * Description:       Admin bar label to quick peed on which environment you're on
 * Version:           1.1.1
 * Requires at least: 5.0.0
 * Tested up to:      6.1.1
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

/**
 * try to identify site
 *
 * @param mixed $site
 *
 * @return array|null
 */
function identify($site){

	// if site was already identified skip
	if($site != null) return $site;

	$activeEvn = array_values(array_filter(get_defined_settings(), __NAMESPACE__."\isActiveEnv"));

	// if no match found
	if(count($activeEvn) == 0) return null;

	// if found more than one site skip and error log
	if(count($activeEvn) > 1){
		error_log(json_encode($activeEvn));
		error_log("Environment Info found more than one matching site...");
		return null;
	}

	return $activeEvn[0];
}
add_filter(FILTER_IDENTIFY_SITE, __NAMESPACE__."\identify");

/**
 * checks if we can try to understand env info
 * @param mixed $site
 *
 * @return bool
 */
function isValidEnvInfo($site){
	return $site != null && is_array($site) && isset($site['title']) && !empty($site['title']);
}

/**
 * @param mixed $site
 *
 * @return bool
 */
function isActiveEnv($site){
	if(!isValidEnvInfo($site)) return false;

	if(isset($site["hostname"]) && gethostname() === $site["hostname"]) return true;
	if(isset($site["path"]) && strpos(dirname(__FILE__), $site["path"]) !== false ) return true;

	return false;
}

/**
 * @param array $env
 *
 * @return object
 */
function getEnvColorsStyles($env){
	$background = (isset($env["background"]))? "background: ".$env["background"].";": "";
	$color = (isset($env["color"]))? "color: ".$env["color"].";":"";
	return (object)array(
		"fg" => $color,
		"bg" => $background,
	);
}

/**
 * extend admin bar with new item
 */
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
	$colors = getEnvColorsStyles($env);

	$wp_admin_bar->add_node( array(
		'id'    => "environment-info",
		'title' => "<div style='margin-left:-10px;padding:0 10px;$colors->bg$colors->fg'>$label</div>",
		'href' => admin_url('admin.php?page=environment_info'),
	) );

}
add_action( 'admin_bar_menu', __NAMESPACE__.'\admin_bar', 40 );

/**
 *
 */
function add_info_page(){
	add_submenu_page(
		'tools.php',
		"Environment Info",
		"Environment Info",
		"manage_options",
		"environment_info",
		__NAMESPACE__."\\render_info_pages"
	);
}
add_action('admin_menu', __NAMESPACE__."\add_info_page");

/**
 * renders info page content
 */
function render_info_pages(){
	?>
	<style>
		.environments{
			display: flex;
			flex-wrap: wrap;
		}
		.environments .env{
			margin: 5px;
			padding: 10px;
			width: 500px;
			min-height: 100px;
			border: 5px dotted rgba(0,0,0, 0.5);
			background: white;
			box-sizing: border-box;
		}
		.environments .env.is-active{
			border-style: solid;
			border-color: rgba(0,0,0, 0.8);
		}
		.environments .env-title{
			font-size: 1.4rem;
		}
		.environments .env-info{
			font-size: 1.2rem;
		}
	</style>
	<div class="wrap">
		<h2>Environments</h2>
		<?php
		$hostname = gethostname();
		echo "<p>Hostname: <code>$hostname</code></p>";
		$path = dirname(__FILE__);
		echo "<p>Path: <code>$path</code></p>";
		?>
		<ul class="environments">
		<?php
		$settings = get_defined_settings();
		foreach ($settings as $site){
			echo "<li>";
			render_info_page($site);
			echo "</li>";
		}
		?>
		</ul>
	</div>
	<?php
}

/**
 * @param array $site
 */
function render_info_page($site){
	$title = $site["title"];
	$isActive = isActiveEnv($site);
	$isActiveClass = $isActive ? "is-active": "";
	$colors = getEnvColorsStyles($site);

	echo "<div class='env $isActiveClass' style='$colors->bg'>";
		echo "<div class='env-title' style='$colors->fg'>$title</div>";
		echo "<div class='env-info'>";
			echo "<p class='env-info' style='$colors->fg'>";
			$info = [];
			if(isset($site["hostname"])){
				$hostname = $site["hostname"];
				$info[] = "Hostname: <code>$hostname</code>";
			}
			if(isset($site["path"])){
				$path = $site["path"];
				$info[] = "Path: <code>$path</code>";
			}
			echo implode("<br/>", $info);
			echo "</p>";
		echo "</div>";
	echo "</div>";
}

/**
 * @return array
 */
function get_defined_settings(){
	return (
		defined('ENVIRONMENT_INFO_SETTINGS') && is_array(ENVIRONMENT_INFO_SETTINGS)
	) ? ENVIRONMENT_INFO_SETTINGS : [];
}
