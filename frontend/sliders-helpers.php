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
		if( themeblvd_slide_has_element( 'image_link', $slide ) ) {
			$atts['link'] = array(
				'url'		=> $slide['elements']['image_link']['url'],
				'target'	=> $slide['elements']['image_link']['target'], // _self, _blank, lightbox, lightbox_video
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
 */

function themeblvd_slide_media( $atts, $settings, $slider_type = 'standard' ) {
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

function themeblvd_get_slide_media( $atts, $settings, $slider_type = 'standard' ) {
	
	$output = '';
	
	if( $atts['type'] == 'video' )
		$output = themeblvd_sliders_get_video( $atts, $slider_type );
	else if( substr( $atts['type'], 0, 5 ) == 'image' )
		$output = themeblvd_sliders_get_image( $atts, $slider_type );
	
	return apply_filters( 'themeblvd_slide_media', $output, $atts, $settings, $slider_type );	
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

	$output = '';

	// Image
	$image = sprintf( '<img src="%s" alt="%s" width="%s" height="%s" />', $atts['url'], $atts['alt'], $atts['width'], $atts['height'] );

	// Image Link
	if( $atts['link'] ) {

		// Link class and target
		switch( $atts['link']['target'] ) {
			case 'lightbox_video' :
				$anchor_class = 'slide-thumbnail-link video';
				$link_target = ' rel="featured_themeblvd_lightbox"';
				if( function_exists('themeblvd_prettyphoto_supported_file') ) {
					if( ! themeblvd_prettyphoto_supported_link( $atts['link']['url'] ) ) {
						// WP oEmbed for non YouTube and Vimeo videos
						$id = uniqid('inline-video-');
						$output .= sprintf( '<div id="%s" class="hide">%s</div>', $id, wp_oembed_get($atts['link']['url']) );
						$atts['link']['url'] = "#{$id}";
					}
				}
				break;
			case 'lightbox' :
				$anchor_class = 'slide-thumbnail-link image';
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
		$output .= sprintf( $link_fmt, $image );

	} else {

		// Output set to the raw image when there's no link
		$output .= $image;
	
	}

	return apply_filters( 'themeblvd_sliders_image', $output, $atts, $image, $slider_type );
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

	// Is this an oEmbed?
	$type = wp_check_filetype( $media_atts['video'] );
	$oembed = true;
	if( in_array( $type['ext'], wp_get_video_extensions() ) )
		$oembed = false;

	// Get HTML
	if( $oembed ) {
		// oEmbed for external videos
		$video = wp_oembed_get( $media_atts['video'] );
	} else {
		// Self-hosted videos, supported in WP 3.6+
		// @todo Improve this. Could be better with params from slider -- width, height, poster
		$video = do_shortcode( sprintf( '[video src="%s"]', $media_atts['video'] ) );
	}

	// Append max height
	if( $video ){

		$video_max_height = '';

		if( ! empty( $media_atts['height'] ) && intval( $media_atts['height'] ) > 0 )
			$video_max_height = sprintf('max-height: %dpx;', $media_atts['height'] );
		
		$video_max_height = apply_filters( 'themeblvd_sliders_max_height', $video_max_height , $media_atts['height'] );

		if( $video_max_height ) {
			if( $oembed ) {
				$find = '<div class="themeblvd-video-wrapper"';
				$video = str_replace($find, $find.' style="'.$video_max_height.'"', $video);
			} else {
				$id = uniqid('tb_video_');
				$output  = "<style>#{$id} .wp-video-shortcode { {$video_max_height} }</style>\n";
				$output .= '<div id="'.$id.'">'.$video.'</div>';
				$video = $output;
			}
		}

	}

	// Set error message
	if( ! $video )
		$video = '<p>'.themeblvd_get_local( 'no_video' ).'</p>';

	return $video;
}

/** 
 * Display content for individual slide including 
 * headline, description, and button.
 *
 * @since 1.1.0
 *
 * @param array $slider ID of slider
 * @param array $slide Data for individual slide
 * @param string $slider_type Type of slider, standard or carrousel
 */

function themeblvd_slide_content( $slider, $slide, $settings, $slider_type = 'standard' ){
	echo themeblvd_get_slide_content( $slider, $slide, $slider_type );
}

/** 
 * Get content for individual slide including 
 * headline, description, and button.
 *
 * @since 1.1.0
 *
 * @param array $slide Data for individual slide
 * @param string $slider_type Type of slider, standard or carrousel
 * @return string $output Final HTML markup for content section
 */

function themeblvd_get_slide_content( $slider, $slide, $settings, $slider_type = 'standard' ){

	$output = '';

	if( themeblvd_slide_has_element( 'headline', $slide ) || 
		themeblvd_slide_has_element( 'description', $slide ) || 
		themeblvd_slide_has_element( 'button', $slide ) ) {

		// Setup markup to wrap content area.
		$wrap_class = 'content';
		if( $slide['position'] != 'full' )
			$wrap_class .= ' grid_fifth_2';
		$wrap_fmt = apply_filters( 'themeblvd_slide_content_wrap', '<div class="'.$wrap_class.'"><div class="content-inner">%s</div></div>' );

		$content = '';
		
		// Headline
		if( themeblvd_slide_has_element( 'headline', $slide ) )
			$content .= sprintf( '<div class="slide-title"><span>%s</span></div>', stripslashes( $slide['elements']['headline'] ) );

		// Description + Button
		if( themeblvd_slide_has_element( 'description', $slide ) || themeblvd_slide_has_element( 'button', $slide ) ) {

			$desc = '';

			// Description text
			if( themeblvd_slide_has_element( 'description', $slide ) ) {
				$text = stripslashes( $slide['elements']['description'] );
				if( apply_filters( 'themeblvd_'.$slider_type.'_slider_desc', true, $slide, $slider, $settings ) )
					$text = apply_filters( 'themeblvd_the_content', $text );
				$desc .= sprintf( '<div class="slide-description-text">%s</div>', $text );
			}

			// Button
			if( themeblvd_slide_has_element( 'button', $slide ) ) {
				$button_atts = apply_filters( 'themeblvd_'.$slider_type.'_slider_button', array(
					'text' 		=> $slide['elements']['button']['text'],
					'url'		=> $slide['elements']['button']['url'],
					'color'		=> 'default',
					'target'	=> $slide['elements']['button']['target'],
					'size'		=> 'medium'

				), $slide, $slider, $settings, $slider_type );
				$desc .= sprintf( '<div class="slide-description-button">%s</div>', themeblvd_button( stripslashes( $button_atts['text'] ), $button_atts['url'], $button_atts['color'], $button_atts['target'], $button_atts['size'] ) );
			}

			$content .= sprintf( '<div class="slide-description"><div class="slide-description-inner">%s</div></div>', $desc );
		}

		// Wrap and finalize content
		$output = sprintf( $wrap_fmt, $content );
	}

	return apply_filters( 'themeblvd_slide_content', $output, $slider, $slide, $slider_type );
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

function themeblvd_slide_has_element( $element, $slide ) {
	$include = false;
	
	if( isset( $slide['elements']['include'] ) && is_array( $slide['elements']['include'] ) )
		if( in_array( $element, $slide['elements']['include'] ) )
			if( ! empty( $slide['elements'][$element] ) )
				$include = true;

	if( $element == 'button' && $slide['position'] == 'full' ) 
		$include = false; // Full-size media slides don't support buttons

	return apply_filters( 'themeblvd_slide_has_element', $include, $element, $slide );
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