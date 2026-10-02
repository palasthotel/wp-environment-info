=== Environment Info ===
Contributors: palasthotel, edwardbock, janaeggebrecht
Donate link: https://palasthotel.de/
Tags: information, dashboard, admin bar
Requires at least: 5.0
Tested up to: 7.1.2
Stable tag: 1.1.3
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Show environmental info on your admin bar.

== Description ==

Show environmental info on your admin bar so you will never accidentally edit production site contents instead of stage or the other way around.

== Installation ==

1. Install the plugin from Plugins > Add New, or upload `environment-info.zip` under Plugins > Add New > Upload Plugin
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Describe your environments in `wp-config.php`, see below

== Frequently Asked Questions ==

= How do I describe my environments? =

Define the constant `ENVIRONMENT_INFO_SETTINGS` in `wp-config.php`, with one entry per environment:

`define( 'ENVIRONMENT_INFO_SETTINGS', [
	[ 'path' => '/var/www/staging', 'title' => 'Staging', 'background' => '#b45309', 'color' => 'white' ],
	[ 'hostname' => 'web-prod-1', 'title' => 'Production', 'background' => '#b91c1c', 'color' => 'white' ],
] );`

Each entry needs a `title` and is recognised either by `hostname`, compared with the server's host name, or by `path`, which matches when it appears anywhere in the plugin's directory path. `background` and `color` take any CSS colour.

= The admin bar says "Unknown Server" =

Exactly one entry has to match. With none, or with more than one, the label reads "Unknown Server"; more than one match is also written to the error log. Tools > Environment Info lists every entry, marks the active one and shows the current host name and path, so you can see why nothing matched.

= Who sees the label? =

Every logged-in user who sees the admin bar. The overview under Tools > Environment Info requires the `manage_options` capability.

== Changelog ==

= 1.1.3 =
**Bug Fixes**
* stop an empty path from matching every server (00ae461)

= 1.1.2 =
**Bug Fixes**
* point the admin bar at the info page and escape rendered output (ec0ed92)

= 1.1.1 =
* Bugfix: Removed a fatal error caused by wrong hook

= 1.1.0 =
* Feature: Identify environment by hostname
* Feature: Tool page with overview of all defined environments

= 1.0.0 =
* Submitted to wordpress.org plugin repo version
