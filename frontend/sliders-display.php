<?php
/**
 * Print out the JS for setting up a carrousel slider.
 *
 * @since 1.0.0
 */

function themeblvd_carrousel_slider_js_default( $id, $options ) {
	wp_enqueue_script( 'roundabout' ); // JS file registered and located within theme framework
	?>
	<script>
	jQuery(document).ready(function($) {
		$(window).load(function() {
			$('#tb-slider-<?php echo $id; ?> .tb-loader').fadeOut('fast');
			$('#tb-slider-<?php echo $id; ?> .slider-inner').fadeIn('fast');
			$('#tb-slider-<?php echo $id; ?> .carrousel-slider').roundabout({
				// minOpacity: '1',
				responsive: true,
				<?php if( $options['nav_arrows'] ) : ?>
				btnNext: '#tb-slider-<?php echo $id; ?> .next',
     			btnPrev: '#tb-slider-<?php echo $id; ?> .prev'
     			<?php endif; ?>
			});
		});
	});
	</script>
	<?php
}

/**
 * Standard Slider - default action for themeblvd_standard_slider
 *
 * @since 1.0.0
 *
 * @param var $slider ID of current slider
 * @param array $settings Current settings for slider
 * @param array $slides Current slides for slider
 */

function themeblvd_standard_slider_default( $slider, $settings, $slides ) {
	
	// Extend slides
	$slides = apply_filters( 'themeblvd_standard_slides', $slides, $slider, $settings );

	// With some different user options and ways this function 
	// can be utilized, there are going to be some inconsitancy 
	// with booleans. Let's fix that.
	if( $settings['nav_standard'] === false || $settings['nav_standard'] == '0' || $settings['nav_standard'] == 'false' )
		$settings['nav_standard'] = false;
	if( $settings['nav_arrows'] === false || $settings['nav_arrows'] == '0' || $settings['nav_arrows'] == 'false' )
		$settings['nav_arrows'] = false;
	if( $settings['pause_play'] === false || $settings['pause_play'] == '0' || $settings['pause_play'] == 'false' )
		$settings['pause_play'] = false;

	// Configure additional CSS classes
	$classes = themeblvd_get_classes( 'slider_standard', true );
	$classes .= $settings['nav_standard'] ? ' show-nav_standard' : ' hide-nav_standard';	
	$classes .= $settings['nav_arrows'] ? ' show-nav_arrows' : ' hide-nav_arrows';
	$classes .= $settings['pause_play'] ? ' show-pause_play' : ' hide-pause_play';
	if( ! $settings['nav_standard'] && ! $settings['nav_arrows'] )
		$classes .= ' hide-full_nav';
	$classes = apply_filters( 'themeblvd_slider_wrapper_classes', $classes );
	
	// Max height
	$max_height = themeblvd_sliders_get_max_height( $slider, $slides, $settings );
	// @todo -- Determine max height before loop of slides based on the largest full width image slide

	// Hide on mobile?
	$hide = '';
	if( isset( $settings['mobile_fallback'] ) )
		if( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' )
			$hide = true;

	// Start output
	do_action( 'themeblvd_standard_slider_plugin_js', $slider, $settings );
	?>
	<div id="tb-slider-<?php echo $slider; ?>" class="slider-wrapper standard-slider-wrapper<?php if($hide) echo ' slider_has_mobile_fallback';?>">
		<div class="slider-inner<?php echo $classes; ?>">	
			<div class="slides-wrapper slides-wrapper-<?php echo $slider; ?>">
				<div class="slides-inner">
					<div class="slider standard-slider flexslider">
						<div class="tb-loader"></div>
						<ul class="slides">
							<?php if( ! empty( $slides ) ) : ?>
								<?php foreach( $slides as $slide ) : ?>					
									<?php $media = themeblvd_sliders_get_media( $slider, $slide, $settings, 'standard', $max_height ); ?>
									<?php $elements = themeblvd_sliders_get_elements( $slider, $slide ); ?>
									<li class="slide tight <?php echo themeblvd_sliders_get_slide_classes( $slider, $slide, $media ); ?>"<?php echo $max_height; ?>>
										<div class="slide-body">
											<div class="grid-protection clearfix">
												<?php // Custom Slides ?>
												<?php if( isset( $slide['custom'] ) ) : ?>
													<?php echo $slide['custom']; ?>
												<?php // Video and Image Slides ?>
												<?php else : ?>
													<?php if( in_array( 'headline', $elements ) || in_array( 'description', $elements ) || in_array( 'button', $elements ) ) : ?>
														<div class="content<?php if($slide['position'] != 'full') echo ' grid_fifth_2'; ?>">
															<div class="content-inner">	
																<?php if( in_array( 'headline', $elements ) && $slide['elements']['headline'] ) : ?>
																	<div class="slide-title"><span><?php echo stripslashes( $slide['elements']['headline'] ); ?></span></div>
																<?php endif; ?>
																<?php if( in_array( 'description', $elements ) || in_array( 'button', $elements ) ) : ?>
																	<div class="slide-description">
																		<div class="slide-description-inner">
																			<?php if( in_array( 'description', $elements ) ) : ?>
																				<?php
																				$description = stripslashes( $slide['elements']['description'] );
																				if( apply_filters( 'themeblvd_standard_slider_desc', true, $slide, $slider, $settings ) )
																					$description = apply_filters('themeblvd_the_content', $description);
																				?>
																				<div class="slide-description-text">
																					<?php echo $description; ?>
																				</div>
																			<?php endif; ?>
																			<?php if( $slide['position'] != 'full' && in_array( 'button', $elements ) && $slide['elements']['button']['text'] ) : ?>
																				<div class="slide-description-button">
																					<?php
																					$button_atts = apply_filters( 'themeblvd_standard_slider_button', array(
																						'text' 		=> $slide['elements']['button']['text'],
																						'url'		=> $slide['elements']['button']['url'],
																						'color'		=> 'default',
																						'target'	=> $slide['elements']['button']['target'],
																						'size'		=> 'medium'

																					), $slide, $slider, $settings );
																					echo themeblvd_button( stripslashes( $button_atts['text'] ), $button_atts['url'], $button_atts['color'], $button_atts['target'], $button_atts['size'] ); 
																					?>
																				</div>
																			<?php endif; ?>
																		</div><!-- .slide-description-inner (end) -->
																	</div><!-- .slide-description (end) -->
																<?php endif; ?>
															</div><!-- .content-inner (end) -->
														</div><!-- .content (end) -->
													<?php endif; ?>
													<div class="media <?php echo $slide['slide_type']; if($slide['position'] != 'full') echo ' grid_fifth_3'; ?>">
														<div class="media-inner">
															<?php if( $slide['slide_type'] == 'image' ) : ?>
																<?php if( in_array( 'image_link', $elements ) && $slide['elements']['image_link']['url'] ) : ?>
																	<?php if( $slide['elements']['image_link']['target'] == 'lightbox' ) : ?>
																		<a href="<?php echo $slide['elements']['image_link']['url']; ?>" class="image-link enlarge" rel="themeblvd_lightbox" title=""><span>Image Link</span></a>
																	<?php else : ?>
																		<a href="<?php echo $slide['elements']['image_link']['url']; ?>" target="<?php echo $slide['elements']['image_link']['target']; ?>" class="image-link external"><span>Image Link</span></a>
																	<?php endif; ?>
																<?php endif; ?>
																<img src="<?php echo $media['url']; ?>" alt="<?php echo $media['alt']; ?>" />
															<?php else : ?>
																<?php echo $media['video']; ?>
															<?php endif; ?>
														</div><!-- .media-inner (end) -->
													</div><!-- .media (end) -->
												<?php endif; ?>
											</div><!-- .grid-protection (end) -->
										</div><!-- .slide-body (end) -->
									</li>
								<?php endforeach; ?>
							<?php endif; ?>								
						</ul>
					</div><!-- .slider (end) -->
				</div><!-- .slides-inner (end) -->					
			</div><!-- .slides-wrapper (end) -->
		</div><!-- .slider-inner (end) -->
		<div class="design-1"></div>
		<div class="design-2"></div>
		<div class="design-3"></div>
		<div class="design-4"></div>					
	</div><!-- .slider-wrapper (end) -->
	<?php
	// Display fallback if necessary
	if( isset( $settings['mobile_fallback'] ) )
		if( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' )
			do_action( 'themeblvd_slider_fallback', $slider, $slides, $settings['mobile_fallback'] );
}

/**
 * Carrousel Slider - default action for themeblvd_carrousel_slider
 *
 * @since 1.0.0
 *
 * @param var $slider ID of current slider
 * @param array $settings Current settings for slider
 * @param array $slides Current slides for slider
 */
 
function themeblvd_carrousel_slider_default( $slider, $settings, $slides ) {
	
	// Extend slides
	$slides = apply_filters( 'themeblvd_carrousel_slides', $slides, $slider, $settings );

	// Insert javascript
	do_action( 'themeblvd_carrousel_slider_js', $slider, $settings );
	
	// CSS Classes
	$classes = themeblvd_get_classes( 'slider_carrousel', true );
	
	// Hide on mobile?
	if( isset( $settings['mobile_fallback'] ) )
		if( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) 
			$classes .= ' slider_has_mobile_fallback';
	?>
	<div id="tb-slider-<?php echo $slider; ?>" class="slider-wrapper carrousel-slider-wrapper<?php echo $classes; ?>">
		<div class="tb-loader"></div>
		<div class="slider-inner">
			<?php if( $settings['nav_arrows'] ) : ?>
			<div class="roundabout-nav">
				<a href="#" title="Previous" class="prev"><i class="icon-circle-arrow-left"></i></a>
				<a href="#" title="Next" class="next"><i class="icon-circle-arrow-right"></i></a>
			</div><!-- .roundabout-nav (end) -->
			<?php endif; ?>
			<ul class="carrousel-slider">
				<?php if( $slides ) : ?>
					<?php foreach( $slides as $slide ) : ?>
						<?php
						// Image
						$image_atts = themeblvd_sliders_get_media( $slider, $slide, $settings, 'carrousel' );
						// Elements
						$elements = array();
						if( isset( $slide['elements']['include'] ) && is_array( $slide['elements']['include'] ) )
							$elements = $slide['elements']['include'];
						?>
						<li class="slide size-<?php echo $image_atts['size']; ?>">
							<div class="slide-body">
								<div class="grid-protection">
									<?php if( in_array( 'image_link', $elements ) ) : ?>
										<?php if( $slide['elements']['image_link']['target'] == 'lightbox' ) : ?>
											<a href="<?php echo $slide['elements']['image_link']['url']; ?>" class="image-link enlarge" rel="themeblvd_lightbox" title=""><span><i class="icon-plus"></i></span></a>
										<?php else : ?>
											<a href="<?php echo $slide['elements']['image_link']['url']; ?>" target="<?php echo $slide['elements']['image_link']['target']; ?>" class="image-link external"><span><i class="icon-external-link"></i></span></a>
										<?php endif; ?>
									<?php endif; ?>
									<img src="<?php echo $image_atts['url']; ?>" alt="<?php echo $image_atts['alt']; ?>" />
								</div><!-- .grid-protection (end) -->
							</div><!-- .slide-body (end) -->
						</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>
		</div><!-- .slider-inner (end) -->
	</div><!-- .slider-wrapper (end) -->
	<?php
	// Display fallback if necessary
	if( isset( $settings['mobile_fallback'] ) )
		if( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' )
			do_action( 'themeblvd_slider_fallback', $slider, $slides, $settings['mobile_fallback'] );
}

/**
 * Slidebar mobile fallback
 *
 * @since 1.0.0
 *
 * @param var $slider ID of current slider
 * @param array $slides Current slides for slider
 * @param var $fallback Type of fallback, full_list or first_slide
 */

function themeblvd_slider_fallback_default( $slider, $slides, $fallback ) {
	
	// DEBUG
	// echo '<pre>'; print_r($slides); echo '</pre>';

	echo '<div class="slider-fallback">';
	echo '<div class="slider-fallback-inner '.$fallback.'">';
	echo '<ul class="slider-fallback-list">';
	foreach( $slides as $slide ) {
		if( ! isset( $slide['custom'] ) ) {
			// Image Slides
			if( $slide['slide_type'] == 'image' ) {
				// Image URL
				$image_size = '';
				$slide['position'] == 'full' ? $image_size = 'slider-large' : $image_size = 'slider-staged'; // Use crop size to match standard slider display, depending on image position				
				$image_size = apply_filters( 'themeblvd_slider_fallback_img_size', $image_size, $fallback, $slide['position'] ); // Apply optional filter and pass in fallback type & image position
				$image_url = null;
				$image_title = null;
				if( isset( $slide['image'][$image_size] ) && $slide['image'][$image_size] )
					$image_url = $slide['image'][$image_size]; // We do a strict check here so no errors will be thrown with old versions of the framework.
				if( isset( $slide['image']['id'] ) ) {
					$attachment = get_post( $slide['image']['id'], OBJECT );
					$image_title = $attachment->post_title;
				}
				if( ! $image_url ) {
					// This should only get used if user updates to v2.1.0 and 
					// didn't re-save their slider. 
					$attachment = wp_get_attachment_image_src( $slide['image']['id'], $image_size );
					$image_url = $attachment[0];
				}
			}
			// Video Slides
			if( $slide['slide_type'] == 'video' ) {	
				// Get HTML
				$video = wp_oembed_get( $slide['video'] );
				// Set error message
				if( ! $video )
					$video = '<p>'.themeblvd_get_local( 'no_video' ).'</p>';
			}
			// Elements
			$elements = array();
			if( isset( $slide['elements']['include'] ) && is_array( $slide['elements']['include'] ) )
			$elements = $slide['elements']['include'];
		}
		echo '<li class="slider-fallback-slide">';
		echo '<div class="slider-fallback-slide-body">';
			if( isset( $slide['custom'] ) ) {
				// Custom Slide
				echo $slide['custom'];
			} else {
				// Slide Headline
				if( in_array( 'headline', $elements ) && isset( $slide['elements']['headline'] ) && $slide['elements']['headline'] )
					echo '<h2>'.stripslashes($slide['elements']['headline']).'</h2>';
				// Image Slides
				if( $slide['slide_type'] == 'image' ) {
					if( in_array( 'image_link', $elements ) ) {
						if( $slide['elements']['image_link']['target'] == 'lightbox' )
							echo '<a href="'.$slide['elements']['image_link']['url'].'" class="image-link enlarge" rel="themeblvd_lightbox">';
						else
							echo '<a href="'.$slide['elements']['image_link']['url'].'" target="'.$slide['elements']['image_link']['target'].'" class="image-link external">';
					}
					echo '<img src="'.$image_url.'" alt="'.$image_title.'" />';	
					if( in_array( 'image_link', $elements ) )
						echo '</a>';
				}
				// Video Slides
				if( $slide['slide_type'] == 'video' )
					echo $video;
				// Description
				if( in_array( 'description', $elements ) && isset( $slide['elements']['description'] ) && $slide['elements']['description'] )
					echo '<p class="slide-description-text">'.do_shortcode(stripslashes($slide['elements']['description'])).'</p>';
				// Button
				if( in_array( 'button', $elements ) && isset( $slide['elements']['button']['text'] ) && $slide['elements']['button']['text'] )
					echo '<p class="slide-description-button">'.themeblvd_button( stripslashes( $slide['elements']['button']['text'] ), $slide['elements']['button']['url'], 'default', $slide['elements']['button']['target'], 'medium' ).'</p>';
			}
		echo '</div><!-- .slider-fallback-slide-body (end) -->';
		echo '</li>';
		
		// End the loop after first slide if we're only showing the first slide.
		if( $fallback == 'first_slide' )
			break;
	}
	echo '</ul>';
	echo '</div><!-- .slider-fallback-inner (end) -->';
	echo '</div><!-- .slider-fallback(end) -->';
}

/**
 * Custom slider
 *
 * @since 2.0.0
 *
 * @param array $atts Standard WordPress shortcode attributes
 */

function themeblvd_shortcode_slider( $atts ) {
	$default = array(
        'id' => ''
    );
    extract( shortcode_atts( $default, $atts ) );
	// CSS classes for element
	$slider_id = themeblvd_post_id_by_name( $id, 'tb_slider' );
	$type = get_post_meta( $slider_id, 'type', true );
	$classes = 'element element-slider element-slider-'.$type.themeblvd_get_classes( 'element_slider', true );
	// Output
	ob_start();
	echo '<div class="'.$classes.'">';
	echo '<div class="element-inner">';
	echo '<div class="element-inner-wrap">';
	echo '<div class="grid-protection">';
	themeblvd_slider( $id );
	echo '</div><!-- .grid-protection (end) -->';
	echo '</div><!-- .element-inner-wrap (end) -->';
	echo '</div><!-- .element-inner (end) -->';
	echo '</div><!-- .element (end) -->';
	return ob_get_clean();
}

/**
 * Post slider shortcode - [post_slider]
 *
 * @since 2.0.2
 *
 * @param array $atts Standard WordPress shortcode attributes
 */

function themeblvd_shortcode_slider_auto( $atts ) {
	
	if( ! function_exists( 'themeblvd_slider_auto' ) )
		return __( 'Your theme does not support the [post_slider] shortcode.', 'themeblvd_sliders' );
	
	$default = array(
		'fx' 				=> 'slide', 	// Effect for transitions
		'timeout' 			=> '3',			// Time between auto trasitions in seconds
		'nav_standard' 		=> '1',			// Show standard nav - true, false
		'nav_arrows'		=> '1',			// Show nav arrows - true, false
		'pause_play'		=> '1',			// Show pause/play buttons - true, false
		'pause_on_hover' 	=> 'disable',	// Pause on hover - pause_on, pause_on_off, disable
		'image' 			=> 'full',		// How to display featured images - full, align-right, align-left
		'image_link' 		=> 'permalink',	// Where image link goes - permalink, lightbox, none
		'button' 			=> '',			// Text for button to lead to permalink - leave empty to hide
		'tag' 				=> '',			// Tag(s) to include/exclude
		'category_name'		=> '',			// Category slug(s) to include/exclude
		'cat'				=> '',			// Category ID(s) to include/exclude
		'numberposts' 		=> '5',			// Number of posts/slides
		'orderby' 			=> 'date',		// Orderby param for posts query
		'order'				=> 'DESC',		// Order param for posts query
		'query' 			=> '',			// Custom query string
		'mobile_fallback' 	=> 'full_list'	// How to display on mobile - full_list, first_slide, display
	);
    $atts = shortcode_atts( $default, $atts );

	// Output
	ob_start();
	echo '<div class="element element-slider element-slider-standard'.themeblvd_get_classes( 'element_post_slider', true ).'">';
	echo '<div class="element-inner">';
	echo '<div class="element-inner-wrap">';
	echo '<div class="grid-protection">';
	themeblvd_slider_auto( uniqid( 'post_slider_'.rand() ), $atts );
	echo '</div><!-- .grid-protection (end) -->';
	echo '</div><!-- .element-inner-wrap (end) -->';
	echo '</div><!-- .element-inner (end) -->';
	echo '</div><!-- .element (end) -->';
	return ob_get_clean();
}