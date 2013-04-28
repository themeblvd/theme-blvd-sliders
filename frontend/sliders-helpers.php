<?php
/** 
 * Get the max-height for slides
 *
 * @since 1.1.0
 *
 * @param string $slider ID of slider
 * @param array $slide All data for slide
 * @param array $settings Settings for slider 
 * @param string $slider_type Type of slider, standard or carrousel
 * @return int Max height number
 */

function themeblvd_sliders_get_max_height( $slider, $slides, $settings, $slider_type = 'standard' ) {

	global $_wp_additional_image_sizes;
	$height = 0;
	$height_style = '';

	// Determine the maximum height allowed for this set of slides.
	// 1) Could be a full size Image with the tallest height.
	// 2) Could be a video with the tallest "Max Height" setting.
	// 3) If a Staged video or image slide exists, then exit w/no max height.
	if( $slides ) {
		foreach( $slides as $id => $slide ) {

			// Staged videos or images will throw off 
			// overall height in responsive setup.
			if( $slide['position'] != 'full' ) {
				$height = 0;
				break;
			}

			// Looking for tallest full size image or video
			if( $slide['slide_type'] == 'image' ) {
				if( ! empty( $slide['image_size'] ) ) {
					if( $slide['image_size']['valid'] && $slide['image_size']['crop'] && $slide['image_size']['height'] != 9999 )
						if( $slide['image_size']['height'] > $height )
							$height = $slide['image_size']['height'];
				} else {
					// Fallback if slider was saved before v1.1
					$height = $_wp_additional_image_sizes['slider-large']; // @todo Check!!!
				}
			} else if( $slide['slide_type'] == 'video' ) {
				if( ! empty( $slide['video_height'] ) && $slide['video_height'] > $height )
					$height = $slide['video_height'];
			}
		}
	}

	return apply_filters('themeblvd_sliders_max_height', $height, $slider, $slides, $settings, $slider_type );	
}

/** 
 * Get the max-height style string
 *
 * @since 1.1.0
 *
 * @param int $height Max height number
 * @return string Style for height, style="max-height:XXpx"
 */
function themeblvd_sliders_get_max_height_style( $height ) {
	
	if( intval( $height ) > 0 )
		$height_style = sprintf(' style="max-height:%dpx;"', $height);
	else
		return '';
	
	return apply_filters( 'themeblvd_sliders_max_height', $height_style, $height );
}

/** 
 * Get the media attributes for image slides
 *
 * @since 1.1.0
 *
 * @param string $slider ID of slider
 * @param array $slide All data for slide
 * @param array $settings Settings for slider 
 * @param string $slider_type Type of slider, standard or carrousel
 * @param string $max_height Max-height inline style string
 * @return array $atts Attributes for media, size, url, alt title, video
 */

function themeblvd_sliders_get_media( $slider, $slide, $settings, $slider_type = 'standard', $max_height = '' ){

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
		
		// Video embed (WP's oEmbed)
		$atts['video'] = themeblvd_sliders_get_video( $slider, $slide, $max_height );

	} else if ( $slide['slide_type'] == 'image' ) {

		// Image Size
		if( $slide['position'] == 'full' ) {
			$default = $slider_type == 'carrousel' ? 'grid_4' : 'slider-large';
			$atts['size'] = ! empty( $slide['image_size']['name'] ) ? $slide['image_size']['name'] : $default;
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

	return apply_filters( 'themeblvd_sliders_image_atts', $atts, $slider, $slide, $settings, $slider_type );
}

/** 
 * Get the image attributes for image slides
 *
 * @since 1.1.0
 *
 * @param string $slider ID of slider
 * @param array $slide All data for slide
 * @param string $max_height Max-height value for entire slider
 * @return string $video Embed code for video
 */

function themeblvd_sliders_get_video( $slider, $slide, $max_height = '' ){

	// This only should be used with video slides.
	if( $slide['slide_type'] != 'video' )
		return;

	// Give a chance to override with custom video solution
	$video = apply_filters('themeblvd_sliders_video_intervene', '', $slider, $slide, $max_height);
	if( $video )
		return $video;

	// Attributes
	$atts = array();
	if( $slide['position'] == 'full' )
		$atts = array( 'height' => '350' );
	
	// Get HTML
	$video = wp_oembed_get( $slide['video'], $atts );
	
	// Append max height
	if( $video ){

		$video_max_height = 0;
		$video_max_height_style = '';

		if( isset( $slide['video_height'] ) && intval( $slide['video_height'] ) > 0 )
			$video_max_height = intval( $slide['video_height'] );

		if( $video_max_height == 0 )
			$video_max_height_style = themeblvd_sliders_get_max_height_style( $max_height );
		else if( ( $video_max_height <= $max_height ) || ( $max_height == 0 && $video_max_height > 0 ) )
			$video_max_height_style = themeblvd_sliders_get_max_height_style( $video_max_height  );

		if( $video_max_height_style ) {
			$find = '<div class="themeblvd-video-wrapper"';
			$video = str_replace($find, $find.$video_max_height_style, $video);
		}

	}

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