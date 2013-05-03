<?php
/** 
 * Get the media attributes
 *
 * @since 1.1.0
 *
 * @param string $slider ID of slider
 * @param array $slide All data for slide
 * @param array $settings Settings for slider 
 * @param string $slider_type Type of slider, standard or carrousel
 * @return array $atts Attributes for media, size, url, alt title, video
 */

function themeblvd_sliders_get_media_atts( $slider, $slide, $settings, $slider_type = 'standard' ) {

	// This only should be used with image/video slides.
	if( $slide['slide_type'] != 'image' && $slide['slide_type'] != 'video' )
		return;

	// Setup $atts array
	$atts = array(
		'type'			=> '',
		'position'		=> $slide['position'],
		'size'			=> '',
		'url'			=> '',
		'alt'			=> '',
		'width' 		=> '',
		'height'		=> '',
		'video'			=> '',
		'link'			=> array()
	);

	if( $slide['slide_type'] == 'video' ) { 
		
		// Media type
		$atts['type'] = 'video';

		// Video embed URL
		$atts['video'] = $slide['video'];

		// Video height (available in plugin v1.1+)
		if( ! empty( $slide['video_height'] ) )
			$atts['height'] = $slide['video_height'];

	} else if ( $slide['slide_type'] == 'image' ) {

		// Image Atts
		if( ! empty( $slide['image']['display'] ) ) {
			
			// Current slider was saved with plugin v1.1+
			// No database queries involved here!
			$atts['url'] = $slide['image']['display'];
			$atts['width'] = $slide['image']['width'];
			$atts['height'] = $slide['image']['height'];
			$atts['size'] = $slide['image']['size'];
			$atts['alt'] = $slide['image']['title'];
			$atts['type'] = $slide['image']['mime_type'];

		}

		// Image Size fallback (current slider was saved prior to plugin v1.1)
		if( ! $atts['size'] ) {
			if( $slide['position'] == 'full' ) {
				if( $slider_type == 'carrousel' )
					$atts['size'] = 'grid_4';
				else
					$atts['size'] = 'slider-large';
			} else {
				$atts['size'] = 'slider-staged';
			}
		}
		
		// Manually retrieve image from DB. Possible reasons:
		// (1) Current slider was saved prior to plugin v1.1
		// (2) Developer has filtered to force retrieval of image URL.
		// (3) SSL of stored image URL doesn't match SSL of frontend of site.
		if( ! $atts['url'] || apply_filters('themeblvd_'.$slider_type.'_slider_force_url', false) || themeblvd_ssl_conflict( $atts['url'] ) ) {
			$attachment = wp_get_attachment_image_src( $slide['image']['id'], $atts['size'] );
			$atts['url'] = $attachment[0];
			$atts['width'] = $attachment[1];
			$atts['height'] = $attachment[2];
		}

		// Image ALT
		if( isset( $slide['image']['title'] ) ) {
			$atts['alt'] = $slide['image']['title']; // current slider was saved w/plugin v1.1+
		} else if( ! empty( $slide['image']['id'] ) ) {
			$attachment = get_post( $slide['image']['id'] );
			$atts['alt'] = $attachment->post_title;
			$atts['type'] = $attachment->post_mime_type;
		}

		// Image Link
		if( themeblvd_sliders_include_element( 'image_link', $slider, $slide ) ) {
			$atts['link'] = array(
				'url'		=> $slide['elements']['image_link']['url'],
				'target'	=> $slide['elements']['image_link']['target'],
				'title' 	=> $atts['alt']
			);
		}

	}

	return apply_filters( 'themeblvd_sliders_media_atts', $atts, $slider, $slide, $settings, $slider_type );
}

/** 
 * Display slide's media.
 *
 * @since 1.1.0
 *
 * @param array $atts Media attributes from themeblvd_sliders_get_media_atts()
 * @param string $slider_type Type of slider, standard or carrousel
 * @return array $atts Attributes for media, size, url, alt title, video
 */

function themeblvd_slide_media( $atts, $slider_type = 'standard' ) {
	echo themeblvd_get_slide_media( $atts, $slider_type );
}

/** 
 * Get slide's media.
 *
 * @since 1.1.0
 *
 * @param array $atts Media attributes from themeblvd_sliders_get_media_atts()
 * @param string $slider_type Type of slider, standard or carrousel
 * @return array $output HTML output for media
 */

function themeblvd_get_slide_media( $atts, $slider_type = 'standard' ) {
	
	$output = '';
	
	if( $atts['type'] == 'video' )
		$output = themeblvd_sliders_get_video( $atts, $slider_type );
	else if( substr( $atts['type'], 0, 5 ) == 'image' )
		$output = themeblvd_sliders_get_image( $atts, $slider_type );
	
	return apply_filters( 'themeblvd_slide_media', $output, $atts, $slider_type );	
}

/** 
 * Get the final image output for an image slide
 *
 * @since 1.1.0
 *
 * @param array $atts Media attributes from themeblvd_sliders_get_media_atts()
 * @param string $slider_type Type of slider, standard or carrousel
 * @return string $output HTML output for image
 */

function themeblvd_sliders_get_image( $atts, $slider_type = 'standard' ){

	// Image
	$image = sprintf( '<img src="%s" alt="%s" width="%s" height="%s" />', $atts['url'], $atts['alt'], $atts['width'], $atts['height'] );
	$output = $image; // $image passed into filter at end of function

	// Image Link
	if( $atts['link'] ) {

		// Link class and target
		switch( $atts['link']['target'] ) {
			case 'lightbox' :
				$anchor_class = 'slide-thumbnail-link image';
				$link_target = ' rel="featured_themeblvd_lightbox"';
				break;
			case 'lightbox_video' :
				$anchor_class = 'slide-thumbnail-link video';
				$link_target = ' rel="featured_themeblvd_lightbox"';
				break;
			case '_blank' : 
				$anchor_class = 'slide-thumbnail-link external';
				$link_target = ' target="_blank"';
				break;
			default :
				$anchor_class = 'slide-thumbnail-link post';
				$link_target = ' target="_self"';
		}

		// Markup used for image overlay in to work with framework javascript
		$overlay = apply_filters( 'themeblvd_sliders_image_overlay', '<span class="image-overlay"><span class="image-overlay-bg"></span><span class="image-overlay-icon"></span></span>', $atts, $link_target, $slider_type );

		// Final link format
		$link_fmt = apply_filters( 'themeblvd_sliders_image_link_format', '<a href="'.$atts['link']['url'].'" title="'.$atts['link']['title'].'"'.$link_target.' class="'.$anchor_class.'">%s'.$overlay.'</a>', $atts, $link_target, $slider_type );
		
		// Wrap link around Image for final $output
		$output = sprintf( $link_fmt, $image );

	}

	return apply_filters( 'themeblvd_sliders_image', $output, $atts, $image,$slider_type );
}

/** 
 * Get the final video output for a video slide
 *
 * @since 1.1.0
 *
 * @param array $media_atts Media attributes from themeblvd_sliders_get_media_atts()
 * @param string $slider_type Type of slider, standard or carrousel
 * @return string $video Embed code for video
 */

function themeblvd_sliders_get_video( $media_atts, $slider_type = 'standard' ){

	// This only should be used with videos.
	if( $media_atts['type'] != 'video' )
		return;

	// Give a chance to override with custom video solution before 
	// doing work of wp_oembed_get()
	$video = apply_filters( 'themeblvd_sliders_video_intervene', '', $media_atts );
	if( $video )
		return $video;

	// Attributes
	$atts = array();
	if( $media_atts['position'] == 'full' )
		$atts = array( 'height' => '350' );
	
	// Get HTML
	$video = wp_oembed_get( $media_atts['video'], $atts );

	// Append max height
	if( $video ){

		$video_max_height = '';

		if( ! empty( $media_atts['height'] ) && intval( $media_atts['height'] ) > 0 )
			$video_max_height = sprintf(' style="max-height:%dpx;"', $media_atts['height'] );
		
		$video_max_height = apply_filters( 'themeblvd_sliders_max_height', $video_max_height , $media_atts['height'] );

		if( $video_max_height ) {
			$find = '<div class="themeblvd-video-wrapper"';
			$video = str_replace($find, $find.$video_max_height , $video);
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
	
	// @todo... remove this function???

	$elements = array();
	if( $slide['slide_type'] != 'custom' ) {
		if( isset( $slide['elements']['include'] ) && is_array( $slide['elements']['include'] ) )
			$elements = $slide['elements']['include'];
		if( $slide['slide_type'] == 'video' && $slide['position'] == 'full' )
			$elements = array(); // Full width video slide can't have elements.
	}
	return apply_filters( 'themeblvd_standard_slider_elements', $elements, $slider, $slide );
}

/** 
 * Whether an element is included in a slide.
 *
 * @since 1.1.0
 *
 * @param string $element Element to check, image_link, headline, description, or button
 * @param string $slider ID of slider
 * @param array $slide All data for slide
 * @return array $elements Elements for displaying in the slide
 */

function themeblvd_sliders_include_element( $element, $slider, $slide ) {
	$include = false;
	if( isset( $slide['elements']['include'] ) && is_array( $slide['elements']['include'] ) )
		if( in_array( $element, $slide['elements']['include'] ) )
			$include = true;
	return apply_filters( 'themeblvd_sliders_include_element', $include, $element, $slider, $slide );
}

/** 
 * Check if a URL's SSL matches the SSL of the site.
 *
 * This is a pluggable function because it's also 
 * located within the Theme Blvd framework starting 
 * with v2.2.2.
 *
 * @since 1.1.0
 *
 * @param string $url URL of some asset
 * @return boolean True if there's a conflict
 */

if( ! function_exists( 'themeblvd_ssl_conflict' ) ) {
	function themeblvd_ssl_conflict( $url ) {
		if( ( ! is_ssl() && strpos( $url, 'https://' ) !== false ) || ( is_ssl() && strpos( $url, 'http://' ) !== false ) )
			return true;
		else
			return false;
	}
}