<?php
/*
Plugin Name: Fuse Social Floating Sidebar
Plugin URI: http://photontechs.com/fuse-social-sidebar
Description: This Fuse Social Floating Sidebar plugin allow you to put social icons which can be link with your social media profiles.
Version: 3.0
Author: Daniyal Ahmed
Author URI: http://www.photontechs.com
License: GNU General Public License v3.0
License URI: http://www.opensource.org/licenses/gpl-license.php
NOTE: This plugin is released under the GPLv2 license. The icons used in this plugin are the property
of their respective owners, and do not, necessarily, inherit the GPLv2 license.
*/
// Adding Admin Menu
require_once('inc/fuse_social_sidebar_admin.php');
// Getting Values from options
$options = get_option('fuse_social_options');
// Creating Icons
require_once('inc/fuse_social_sidebar_func.php');
// Getting Style for awesome icons
require_once('inc/fuse_social_sidebar_scripts.php');
// Add settings link on plugin page
function fuse_social_settings_link($links) {
  $settings_link = '<a href="options-general.php?page=fuse-social">Settings</a>';
  array_unshift($links, $settings_link);
  return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'fuse_social_settings_link' );
add_action('wp_footer','fuse_social_sidebar',100);


		function fuse_social_sidebar()
		{
				$makeawesome_icons = new Making_Fuse_Icons;
				// Getting Icons for Shortcode
				$makeawesome_icons->Create_Awesome_Icons();
		}

?>