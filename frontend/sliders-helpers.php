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

function themeblvd_sliders_get_media_atts( $slider_type, $slide, $slider, $settings ){

	// This only should be used with image/video slides.
	if( $slide['slide_type'] != 'image' && $slide['slide_type'] != 'video' )
		return;

	// Setup $atts array
	$atts = array(
		'size'	=> '',
		'url'	=> '',
		'alt'	=> '',
		'video'	=> ''
	);

	if( $slide['slide_type'] == 'video' ) { 
		
		$atts['video'] = themeblvd_sliders_get_video( $slider, $slide );

	} else if ( $slide['slide_type'] == 'image' ) {

		// Image Size
		if( $slide['position'] == 'full' ) {
			$default = $slider_type == 'carrousel' ? 'grid_4' : 'slider-large';
			$atts['size'] = ! empty( $slide['image_size'] ) ? $slide['image_size'] : $default;
			$atts['size'] = apply_filters('themeblvd_'.$slider_type.'_slider_full_size', $atts['size'], $slider, $settings);
		} else if( $slide['position'] == 'align-left' || $slide['position'] == 'align-right' ) {
			$atts['size'] = apply_filters('themeblvd_'.$slider_type.'_slider_staged_size', 'slider-staged', $slider, $settings);
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

	// This only should be used with video slides.
	if( $slide['slide_type'] != 'video' )
		return;

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

/** 
 * Get the CSS classes for invividual slides.
 *
 * @since 1.1.0
 *
 * @param string $slider ID of slider
 * @param array $slide All data for slide
 * @return string $classes CSS classes for slide
 */

function themeblvd_sliders_get_slide_classes( $slider, $slide, $media ) {
	if( $slide['slide_type'] == 'custom' ) {
		$classes = 'custom';
	} else {
		$classes = 'media-'.$slide['position'].' '.$slide['slide_type'].'-slide';									
		if( ! empty( $media['size'] ) )
			$classes .= ' size-'.$media['size'];
		if( $slide['position'] == 'full' && $slide['slide_type'] == 'image' )
			$classes .= ' full-image';
	}
	return apply_filters( 'themeblvd_sliders_slide_classes', $classes );
}

/** 
 * Finalize elements for slide display.
 *
 * @since 1.1.0
 *
 * @param string $slider ID of slider
 * @param array $slide All data for slide
 * @return array $elements Elements for displaying in the slide
 */

function themeblvd_sliders_get_elements( $slider, $slide ) {
	$elements = array();
	if( $slide['slide_type'] != 'custom' ) {
		if( isset( $slide['elements']['include'] ) && is_array( $slide['elements']['include'] ) )
			$elements = $slide['elements']['include'];
		if( $slide['slide_type'] == 'video' && $slide['position'] == 'full' )
			$elements = array(); // Full width video slide can't have elements.
	}
	return apply_filters( 'themeblvd_standard_slider_elements', $elements, $slider, $slide );
}