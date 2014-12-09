<?php
/**
 * Display warning telling the user they must have a
 * theme with Theme Blvd framework v2.2+ installed in
 * order to run this plugin.
 *
 * @since 1.0.0
 */
function themeblvd_sliders_warning() {

	global $current_user;

	// DEBUG: delete_user_meta( $current_user->ID, 'tb-nag-sliders-no-framework' );

	if( ! get_user_meta( $current_user->ID, 'tb-nag-sliders-no-framework' ) ) {
		echo '<div class="updated">';
		echo '<p><strong>Theme Blvd Sliders: </strong>'.__( 'You are not using a theme with the Theme Blvd Framework v2.2+, and so this plugin will not do anything.', 'theme-blvd-shortcodes' ).'</p>';
		echo '<p><a href="'.themeblvd_sliders_disable_url('sliders-no-framework').'">'.__('Dismiss this notice', 'theme-blvd-sliders').'</a> | <a href="http://www.themeblvd.com" target="_blank">'.__('Visit ThemeBlvd.com', 'theme-blvd-sliders').'</a></p>';
		echo '</div>';
	}
}

/**
 * Dismiss an admin notice.
 *
 * @since 1.1.0
 */
function themeblvd_sliders_disable_nag() {

	global $current_user;

	if ( ! isset($_GET['nag-ignore']) ) {
		return;
	}

	if ( strpos($_GET['nag-ignore'], 'tb-nag-') !== 0 ) { // meta key must start with "tb-nag-"
		return;
	}

	if ( isset($_GET['security']) && wp_verify_nonce( $_GET['security'], 'themeblvd-sliders-nag' ) ) {
		add_user_meta( $current_user->ID, $_GET['nag-ignore'], 'true', true );
	}
}

/**
 * Disable a nag message URL.
 *
 * @since 1.1.0
 */
function themeblvd_sliders_disable_url( $id ) {

	global $pagenow;

	$url = admin_url( $pagenow );

	if( ! empty( $_SERVER['QUERY_STRING'] ) ) {
		$url .= sprintf( '?%s&nag-ignore=%s', $_SERVER['QUERY_STRING'], 'tb-nag-'.$id );
	} else {
		$url .= sprintf( '?nag-ignore=%s', 'tb-nag-'.$id );
	}

	$url .= sprintf( '&security=%s', wp_create_nonce('themeblvd-sliders-nag') );

	return $url;
}

/**
 * Register "tb_slider" custom post type. This post
 * type is how the framework internally manages sliders.
 *
 * @since 1.0.0
 */
function themeblvd_sliders_register_post_type(){

	$args = apply_filters( 'themeblvd_sliders_post_type_args', array(
		'labels' 			=> array( 'name' => 'Sliders', 'singular_name' => 'Slider' ),
		'public'			=> false,
		//'show_ui' 		=> true,	// Can uncomment for debugging
		'query_var' 		=> true,
		'capability_type' 	=> 'post',
		'hierarchical' 		=> false,
		'rewrite' 			=> false,
		'supports' 			=> array( 'title', 'custom-fields', 'editor' ), // needs to support 'editor' for image to be inserted properly
		'can_export'		=> true
	));

	register_post_type( 'tb_slider', $args );
}