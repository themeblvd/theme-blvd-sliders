<?php
/*
Plugin Name: Theme Blvd Sliders
Plugin URI: 
Description: This plugin works in conjuction with the Theme Blvd framework and its core addons to allow you to add sliders to your website.
Version: 1.0.0
Author: Jason Bobich
Author URI: http://jasonbobich.com
License: GPL2

    Copyright 2012  Jason Bobich

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

define( 'TB_SLIDERS_PLUGIN_VERSION', '1.0.0' );
define( 'TB_SLIDERS_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_SLIDERS_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Run Widget Area Manager
 *
 * In order for everything to run, we need to make 
 * sure Theme Blvd framework v2.2+ is running. Also 
 * to run the admin panel portion, we will also check 
 * to make sure the user is allowed. -- This supports 
 * the framework's filters on changing admin page 
 * capabilities. 
 *
 * @since 1.0.0
 */

function themeblvd_sliders_init() {
	
	global $_themeblvd_sliders_admin;
	
	// Check to make sure Theme Blvd Framework 2.2+ is running
	if( ! defined( 'TB_FRAMEWORK_VERSION' ) || version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '<' ) ) {
		add_action( 'admin_notices', 'themeblvd_sliders_warning' );
		return;
	}
	
	// Register post type
	add_action( 'init', 'themeblvd_sliders_register_post_type' );
	
	// Separate frontend and admin sections
	if( is_admin() ){
		if ( themeblvd_supports( 'admin', 'sliders' ) && current_user_can( themeblvd_admin_module_cap( 'sliders' ) ) ) {
			include_once( TB_SLIDERS_PLUGIN_DIR . '/admin/class-tb-sliders-admin.php' );
			$_themeblvd_sliders_admin = new Theme_Blvd_Sliders_Admin();
		}
	} else {
		include_once( TB_SLIDERS_PLUGIN_DIR . '/frontend/sliders-display.php' );
		add_action( 'themeblvd_standard_slider', 'themeblvd_standard_slider_default', 9, 3 ); // JS located within the theme because also works with other elements
		add_action( 'themeblvd_carrousel_slider', 'themeblvd_carrousel_slider_default', 9, 3 );
		add_action( 'themeblvd_carrousel_slider_js', 'themeblvd_carrousel_slider_js_default', 10, 2 );
		add_action( 'themeblvd_slider_fallback', 'themeblvd_slider_fallback_default', 10, 3 );
		add_shortcode( 'slider', 'themeblvd_shortcode_slider' );
	}

}
add_action( 'after_setup_theme', 'themeblvd_sliders_init' );

/**
 * Register text domain for localization.
 *
 * @since 1.0.0
 */

function themeblvd_sliders_textdomain() {
	load_plugin_textdomain( 'themeblvd_sliders', false, TB_SLIDERS_PLUGIN_DIR . '/lang' );
}
add_action( 'plugins_loaded', 'themeblvd_sliders_textdomain' );

/**
 * Display warning telling the user they must have a 
 * theme with Theme Blvd framework v2.2+ installed in 
 * order to run this plugin.
 *
 * @since 1.0.0
 */

function themeblvd_sliders_warning() {
	echo '<div class="updated">';
	echo '<p>'.__( 'You currently have the "Theme Blvd Sliders" plugin activated, however you are not using a theme with Theme Blvd Framework v2.2+, and so this plugin will not do anything.', 'themeblvd_sliders' ).'</p>';
	echo '</div>';
}

/**
 * Register "tb_slider" custom post type. This post 
 * type is how the framework internally manages sliders. 
 *
 * @since 1.0.0
 */

function themeblvd_sliders_register_post_type(){
	$args = array(
		'labels' 			=> array( 'name' => 'Sliders', 'singular_name' => 'Slider' ),
		'public'			=> false,
		//'show_ui' 		=> true,	// Can uncomment for debugging
		'query_var' 		=> true,
		'capability_type' 	=> 'post',
		'hierarchical' 		=> false,
		'rewrite' 			=> false,
		'supports' 			=> array( 'title', 'custom-fields', 'editor' ), // needs to support 'editor' for image to be inserted properly
		'can_export'		=> true
	);
	register_post_type( 'tb_slider', $args );
}