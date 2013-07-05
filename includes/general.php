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
	// DEBUG: delete_user_meta( $current_user->ID, 'tb_sliders_no_framework' );
	if( ! get_user_meta( $current_user->ID, 'tb_sliders_no_framework' ) ){
		echo '<div class="updated">';
		echo '<p>'.__( 'You currently have the "Theme Blvd Sliders" plugin activated, however you are not using a theme with Theme Blvd Framework v2.2+, and so this plugin will not do anything.', 'themeblvd_sliders' ).'</p>';
		echo '<p><a href="'.themeblvd_sliders_disable_url('tb_sliders_no_framework').'">'.__('Dismiss this notice', 'themeblvd_sliders').'</a> | <a href="http://www.themeblvd.com" target="_blank">'.__('Visit ThemeBlvd.com', 'themeblvd_sliders').'</a></p>';
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
    if ( isset( $_GET['tb_nag_ignore'] ) )
         add_user_meta( $current_user->ID, $_GET['tb_nag_ignore'], 'true', true );
}

/**
 * Disable a nag message URL.
 *
 * @since 1.1.0
 */
function themeblvd_sliders_disable_url( $id ) {

	global $pagenow;

	$url = admin_url( $pagenow );

	if( ! empty( $_SERVER['QUERY_STRING'] ) )
		$url .= sprintf( '?%s&tb_nag_ignore=%s', $_SERVER['QUERY_STRING'], $id );
	else
		$url .= sprintf( '?tb_nag_ignore=%s', $id );

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