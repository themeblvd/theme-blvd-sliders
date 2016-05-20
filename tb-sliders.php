<?php
/*
Plugin Name: Theme Blvd Sliders
Description: This plugin works in conjunction with the Theme Blvd framework and its core addons to allow you to add sliders to your website.
Version: 1.2.5
Author: Theme Blvd
Author URI: http://themeblvd.com
License: GPL2

    Copyright 2013  Theme Blvd

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

define( 'TB_SLIDERS_PLUGIN_VERSION', '1.2.5' );
define( 'TB_SLIDERS_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'TB_SLIDERS_PLUGIN_URI', plugins_url( '' , __FILE__ ) );

/**
 * Run Sliders Plugin
 *
 * @since 1.0.0
 */
function themeblvd_sliders_init() {

	global $_themeblvd_sliders_admin;

	// Include general functions
	include_once( TB_SLIDERS_PLUGIN_DIR . '/includes/general.php' );

	// Check to make sure Theme Blvd Framework 2.2+ is running
	if ( ! defined( 'TB_FRAMEWORK_VERSION' ) || version_compare( TB_FRAMEWORK_VERSION, '2.2.0', '<' ) ) {
		add_action( 'admin_notices', 'themeblvd_sliders_warning' );
		add_action( 'admin_init', 'themeblvd_sliders_disable_nag' );
		return;
	}

	// Register post type
	add_action( 'init', 'themeblvd_sliders_register_post_type' );

	// Separate frontend and admin sections
	if ( is_admin() ){

		if ( themeblvd_supports( 'admin', 'sliders' ) && current_user_can( themeblvd_admin_module_cap( 'sliders' ) ) ) {
			include_once( TB_SLIDERS_PLUGIN_DIR . '/includes/admin/class-tb-sliders-admin.php' );
			$_themeblvd_sliders_admin = new Theme_Blvd_Sliders_Admin();
		}

	}

	if ( ! is_admin() || ( defined('DOING_AJAX') && DOING_AJAX ) ) {

		include_once( TB_SLIDERS_PLUGIN_DIR . '/includes/sliders-helpers.php' );
		include_once( TB_SLIDERS_PLUGIN_DIR . '/includes/sliders-display.php' );

		// Standard slider
		add_action( 'themeblvd_standard_slider_plugin_js', 'themeblvd_standard_slider_js', 10, 2 );
		add_action( 'themeblvd_standard_slider', 'themeblvd_standard_slider_default', 9, 4 ); // JS located within the theme because also works with other elements
		add_action( 'themeblvd_slider_auto', 'themeblvd_standard_slider_default', 9, 3 );
		add_filter( 'themeblvd_sliders_custom_content', 'stripslashes' );
		add_filter( 'themeblvd_sliders_custom_content', 'do_shortcode' );

		// Carrousel slider
		add_action( 'themeblvd_carrousel_slider', 'themeblvd_carrousel_slider_default', 9, 3 );
		add_action( 'themeblvd_carrousel_slider_js', 'themeblvd_carrousel_slider_js_default', 10, 2 );

		// Nivo slider
		add_action( 'themeblvd_nivo_slider', 'themeblvd_nivo_slider_default', 9, 3 );
		add_action( 'themeblvd_nivo_slider_js', 'themeblvd_nivo_slider_js_default', 10, 2 );

		// Bootstrap slider
		add_action( 'themeblvd_bootstrap_slider', 'themeblvd_bootstrap_slider_default', 9, 3 );

		// General slider fallback
		add_action( 'themeblvd_slider_fallback', 'themeblvd_slider_fallback_default', 10, 3 );

		// Slider shortcodes
		add_shortcode( 'slider', 'themeblvd_shortcode_slider' );

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) {
			// If using TB framework 2.5+, [post_slider] is part of the shortcodes plugin
			add_shortcode( 'post_slider', 'themeblvd_shortcode_slider_auto' );
		}

	}

}
add_action( 'after_setup_theme', 'themeblvd_sliders_init' );

/**
 * Setup Sliders API and filter in registered "Sliders"
 * and "Post Slider" elements for Theme Blvd Layout
 * Builder plugin.
 *
 * @since 1.1.0
 */
function themeblvd_sliders_api_init(){

	// Add filter for Layout Builder's API to register
	// "Sliders" and "Post Slider" elements
	add_filter( 'themeblvd_registered_elements', 'themeblvd_sliders_registered_elements' );

	// Include Theme_Blvd_Sliders_API class.
	include_once( TB_SLIDERS_PLUGIN_DIR . '/includes/api/class-tb-sliders-api.php' );

	// Instantiate single object for Sliders API.
	// Helper functions are located within theme
	// framework. i.e. themeblvd_add_slider()
	Theme_Blvd_Sliders_API::get_instance();

}
add_action( 'themeblvd_api', 'themeblvd_sliders_api_init', 9 ); // Priority 9 to come before Builder API

/**
 * Add plugin's builder elements to current registered
 * framework elements.
 *
 * @since 1.0.2
 */
function themeblvd_sliders_registered_elements( $elements ) {
	$elements[] = 'slider';
	$elements[] = 'post_slider';
	// @todo -- Add quick slider element
	return $elements;
}

/**
 * Register text domain for localization.
 *
 * @since 1.0.0
 */
function themeblvd_sliders_textdomain() {
	load_plugin_textdomain('theme-blvd-sliders');
}
add_action( 'init', 'themeblvd_sliders_textdomain' );
