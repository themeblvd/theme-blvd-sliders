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
				<?php if( $options['nav_arrows'] ) : ?>
				btnNext: '#tb-slider-<?php echo $id; ?> .next',
     			btnPrev: '#tb-slider-<?php echo $id; ?> .prev',
     			<?php endif; ?>
				responsive: true
			});
		});
	});
	</script>
	<?php
}

/**
 * Print out the JS for setting up a nivo slider.
 *
 * @since 1.1.0
 */

function themeblvd_nivo_slider_js_default( $id, $options ) {
	wp_enqueue_script( 'nivo' ); // JS file registered and located within theme framework
	?>
	<script>
	jQuery(document).ready(function($) {
		$(window).load(function() {
			var slider_container = $('#tb-slider-<?php echo $id; ?>');
			slider_container.find('.nivoSlider').nivoSlider({
				effect: "<?php echo $options['fx']; ?>",
				slices: <?php echo $options['slices']; ?>,
				boxCols: <?php echo $options['boxcols']; ?>,
				boxRows: <?php echo $options['boxrows']; ?>,
				directionNav: <?php echo $options['nav_arrows'] ? 'true' : 'false'; ?>,
				controlNav: <?php echo $options['nav_standard'] ? 'true' : 'false'; ?>,
				pauseOnHover: <?php echo $options['pause_on_hover'] ? 'true' : 'false'; ?>,
				pauseTime: <?php echo $options['timeout']; ?>000,
				manualAdvance: <?php echo intval( $options['timeout'] ) > '0' ? 'false' : 'true'; ?>,
				<?php do_action( 'themeblvd_nivo_properties', $id, $options ); ?>
				afterLoad : function() {
					slider_container.find('.tb-loader').fadeOut('fast');
					slider_container.find('a.slide-thumbnail-link').click(function(){
						slider_container.find('.nivo-pauseNav').hide();
	    				slider_container.find('.nivo-playNav').show();
						$(this).closest('.nivoSlider').data('nivoslider').stop();
					});
				},
				beforeChange : function() {
					slider_container.find('a.slide-thumbnail-link').hide();
				}
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
					<?php if( ! empty( $slides ) ) : ?>
						<div class="slider standard-slider flexslider">
							<div class="tb-loader"></div>
							<ul class="slides">
								<?php foreach( $slides as $slide ) : ?>					
									<?php $media_atts = themeblvd_sliders_get_media_atts( $slider, $slide, $settings ); ?>
									<li class="slide tight <?php echo themeblvd_sliders_get_slide_classes( $slider, $slide, $media_atts ); ?>">
										<div class="slide-body">
											<div class="grid-protection clearfix">
												<?php if( isset( $slide['custom'] ) ) : ?>
													<?php echo apply_filters( 'themeblvd_sliders_custom_content', $slide['custom'] ); ?>
												<?php else : ?>
													<?php themeblvd_slide_content( $slider, $slide, $settings ); ?>
													<div class="media <?php echo $slide['slide_type']; if($slide['position'] != 'full') echo ' grid_fifth_3'; ?>">
														<div class="media-inner">
															<?php themeblvd_slide_media( $media_atts, $settings ); ?>
														</div><!-- .media-inner (end) -->
													</div><!-- .media (end) -->
												<?php endif; ?>
											</div><!-- .grid-protection (end) -->
										</div><!-- .slide-body (end) -->
									</li>
								<?php endforeach; ?>				
							</ul>
						</div><!-- .slider (end) -->
					<?php endif; ?>	
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
						<?php $media_atts = themeblvd_sliders_get_media_atts( $slider, $slide, $settings, 'carrousel' ); ?>
						<li class="slide <?php echo themeblvd_sliders_get_slide_classes( $slider, $slide, $media_atts ); ?>">
							<span class="tb-block-click"></span>
							<div class="slide-body">
								<div class="grid-protection">
									<?php themeblvd_slide_media( $media_atts, $settings, 'carrousel' ); ?>
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
 * Nivo - default action for themeblvd_nivo_slider
 *
 * @since 1.1.0
 *
 * @param var $slider ID of current slider
 * @param array $settings Current settings for slider
 * @param array $slides Current slides for slider
 */

function themeblvd_nivo_slider_default( $slider, $settings, $slides ) {
	
	// Extend slides
	$slides = apply_filters( 'themeblvd_nivo_slides', $slides, $slider, $settings );
	
	// With some different user options and ways this function 
	// can be utilized, there are going to be some inconsitancy 
	// with booleans. Let's fix that.
	if( $settings['nav_standard'] === false || $settings['nav_standard'] == '0' || $settings['nav_standard'] == 'false' )
		$settings['nav_standard'] = false;
	if( $settings['nav_arrows'] === false || $settings['nav_arrows'] == '0' || $settings['nav_arrows'] == 'false' )
		$settings['nav_arrows'] = false;

	// Configure additional CSS classes
	$classes = themeblvd_get_classes( 'slider_nivo', true );
	$classes .= $settings['nav_standard'] ? ' show-nav_standard' : ' hide-nav_standard';	
	$classes .= $settings['nav_arrows'] ? ' show-nav_arrows' : ' hide-nav_arrows';
	if( ! $settings['nav_standard'] && ! $settings['nav_arrows'] )
		$classes .= ' hide-full_nav';
	$classes = apply_filters( 'themeblvd_slider_wrapper_classes', $classes );

	// Hide on mobile?
	$hide = '';
	if( isset( $settings['mobile_fallback'] ) )
		if( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' )
			$hide = true;


	// Insert javascript
	do_action( 'themeblvd_nivo_slider_js', $slider, $settings );
	?>
	
	<div id="tb-slider-<?php echo $slider; ?>" class="slider-wrapper tb-nivo-slider-wrapper nivo-slider-wrapper<?php if($hide) echo ' slider_has_mobile_fallback';?>">
		<div class="slider-inner<?php echo $classes; ?>">	
			<div class="slides-wrapper slides-wrapper-<?php echo $slider; ?>">
				<div class="slides-inner">
					<div class="tb-loader"></div>
					<?php if( $slides ) : ?>
						<div class="slider nivoSlider">
							<?php 
							foreach( $slides as $slide ) :
					            $image = themeblvd_get_slide_media( themeblvd_sliders_get_media_atts( $slider, $slide, $settings, 'nivo' ), $settings, 'nivo' );
			        			if( themeblvd_slide_has_element( 'headline', $slide ) || themeblvd_slide_has_element( 'description', $slide ) )
			        				$image = str_replace( ' />', sprintf(' title="#%s" />', $slide['slide_id'].'_desc'), $image );
			        			echo $image."\n";
			        		endforeach;
			        		?>
				        </div><!-- .nivoSlider (end) -->
				        <?php foreach( $slides as $slide ) : ?>
				        	<?php if( themeblvd_slide_has_element( 'headline', $slide ) || themeblvd_slide_has_element( 'description', $slide ) ) : ?>
				        		<div id="<?php echo $slide['slide_id'].'_desc'; ?>" class="nivo-html-caption">
				        			<?php themeblvd_slide_content( $slider, $slide, $settings, 'nivo' ); ?>
				        		</div>
				        	<?php endif; ?>
				        <?php endforeach; ?>
			    	<?php endif; ?>
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
 * Slidebar mobile fallback
 *
 * @since 1.0.0
 *
 * @param var $slider ID of current slider
 * @param array $slides Current slides for slider
 * @param var $fallback Type of fallback, full_list or first_slide
 */

function themeblvd_slider_fallback_default( $slider, $slides, $fallback ) {

	echo '<div class="slider-fallback">';
	echo '<div class="slider-fallback-inner '.$fallback.'">';
	
	if( $slides ) {
		
		echo '<ul class="slider-fallback-list">';
		
		foreach( $slides as $slide ) {
			echo '<li class="slider-fallback-slide">';
			echo '<div class="slider-fallback-slide-body">';
				if( isset( $slide['custom'] ) ) {
					
					// Custom Slide
					echo apply_filters( 'themeblvd_sliders_custom_content', $slide['custom'] );
				
				} else {
					
					// Headline
					if( themeblvd_slide_has_element( 'headline', $slide ) )
						printf( '<h2>%s</h2>', stripslashes($slide['elements']['headline']) );
					
					// Media (Image or Video)
					themeblvd_slide_media( themeblvd_sliders_get_media_atts( $slider, $slide, $settings, 'fallback' ), array(), 'fallback' );

					// Description
					if( themeblvd_slide_has_element( 'description', $slide ) ) {
						$text = stripslashes( $slide['elements']['description'] );
						if( apply_filters( 'themeblvd_'.$slider_type.'_slider_desc', true, $slide, $slider, $settings ) )
							$text = apply_filters( 'themeblvd_the_content', $text );
						printf( '<div class="slide-description-text">%s</div>', $text );
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
						printf( '<div class="slide-description-button">%s</div>', themeblvd_button( stripslashes( $button_atts['text'] ), $button_atts['url'], $button_atts['color'], $button_atts['target'], $button_atts['size'] ) );
					}
				}
			
			echo '</div><!-- .slider-fallback-slide-body (end) -->';
			echo '</li>';
			
			// End the loop after first slide if we're only showing the first slide.
			if( $fallback == 'first_slide' )
				break;

		} // End foreach($slides)
		
		echo '</ul><!-- .slider-fallback-list (end) -->';
	
	} // End if($slides)
	
	echo '</div><!-- .slider-fallback-inner (end) -->';
	echo '</div><!-- .slider-fallback(end) -->';
}

/**
 * Custom slider
 *
 * @since 1.0.0
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
 * @since 1.0.2
 *
 * @param array $atts Standard WordPress shortcode attributes
 */

function themeblvd_shortcode_slider_auto( $atts ) {
	
	if( ! function_exists( 'themeblvd_slider_auto' ) )
		return __( 'Your theme does not support the [post_slider] shortcode.', 'themeblvd_sliders' );
	
	$default = array(
		'fx' 				=> 'slide', 		// Effect for transitions - slide, fade
		'smoothheight' 		=> 'true',			// Enable smoothheight - true, false
		'timeout' 			=> '3',				// Time between auto trasitions in seconds
		'nav_standard' 		=> '1',				// Show standard nav - true, false
		'nav_arrows'		=> '1',				// Show nav arrows - true, false
		'pause_play'		=> '1',				// Show pause/play buttons - true, false
		'pause_on_hover' 	=> 'disable',		// Pause on hover - pause_on, pause_on_off, disable
		'image' 			=> 'full',			// How to display featured images - full, align-right, align-left
		'image_size'		=> 'slider-large', 	// Crop size for full-size images
		'image_link' 		=> 'permalink',		// Where image link goes - option, permalink, lightbox, none
		'button' 			=> '',				// Text for button to lead to permalink - leave empty to hide
		'tag' 				=> '',				// Tag(s) to include/exclude
		'category_name'		=> '',				// Category slug(s) to include/exclude
		'cat'				=> '',				// Category ID(s) to include/exclude
		'numberposts' 		=> '5',				// Number of posts/slides
		'orderby' 			=> 'date',			// Orderby param for posts query
		'order'				=> 'DESC',			// Order param for posts query
		'query' 			=> '',				// Custom query string
		'mobile_fallback' 	=> 'full_list'		// How to display on mobile - full_list, first_slide, display
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