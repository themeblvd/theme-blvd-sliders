<?php
/** 
 * Get the image attributes for image slides
 *
 * @since 1.1.0
 *
 * @param string $slider_type Type of slider, standard or carrousel
 * @param array $slide All data for slide
 * @param string $slider ID of slider
 * @param array $settings Settings for slider 
 * @return array $atts Attributes for image, size, url, and alt title
 */

function themeblvd_sliders_get_image_atts( $slider_type, $slide, $slider, $settings ){

	// This only should be used with image slides.
	if( $slide['slide_type'] != 'image' )
		return;

	// Setup $atts array
	$atts = array(
		'size'	=> '',
		'url'	=> '',
		'alt'	=> ''
	);

	// Image Size
	switch( $slider_type ) {
		case 'standard' :
			if( $slide['position'] == 'full' ){
				// @todo Allow for custom crop size
				// $slide ...
				$atts['size'] = apply_filters('themeblvd_standard_slider_full_size', 'slider-large', $slider, $settings);
			} else {
				$atts['size'] = apply_filters('themeblvd_standard_slider_staged_size', 'slider-staged', $slider, $settings);
			}
			break;

		case 'carrousel' : // @todo Allow for custom crop size
			$atts['size'] = apply_filters( 'themeblvd_carrousel_image_size', 'grid_4' );
			break;
	}
	
	// Image URL
	if( ! empty( $slide['image'][$atts['size']] ) )
		$atts['url'] = $slide['image'][$atts['size']]; // If slider is from prior to framework v2.1, they won't have this.
	
	if( ! $atts['url'] || apply_filters('themeblvd_'.$slider_type.'_slider_force_url', false) || ( ! is_ssl() && strpos($atts['url'], 'https://') ) ) {
		// Force to query image from DB (fallback).
		$attachment = wp_get_attachment_image_src( $slide['image']['id'], $atts['size'] );
		$atts['url'] = $attachment[0];
	}

	// Image ALT
	if( ! empty( $slide['image']['id'] ) ) {
		$attachment = get_post( $slide['image']['id'], OBJECT );
		$atts['alt'] = $attachment->post_title;
	}

	return apply_filters( 'themeblvd_sliders_image_atts', $atts, $slider_type, $slide, $slider, $settings );
}

/** 
 * Get the image attributes for image slides
 *
 * @since 1.1.0
 *
 * @param string $slider ID of slider
 * @param array $slide All data for slide
 * @return string $video Embed code for video
 */

function themeblvd_sliders_get_video( $slider, $slide ){

	// Give a chance to override withcustom video solution
	$video = apply_filters('themeblvd_sliders_video_intervene', '', $slider, $slide);
	if( $video )
		return $video;

	// Attributes
	if( $slide['position'] == 'full' )
		$atts = array( 'height' => '350' );
	else
		$atts = array( 'width' => '564' );
	
	// Get HTML
	$video = wp_oembed_get( $slide['video'], $atts );
	
	// Set error message
	if( ! $video )
		$video = '<p>'.themeblvd_get_local( 'no_video' ).'</p>';

	return $video;
}
