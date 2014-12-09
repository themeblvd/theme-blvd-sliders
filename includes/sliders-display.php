<?php
/**
 * Print out the JS for setting up a carrousel slider.
 *
 * @since 1.0.0
 */
function themeblvd_carrousel_slider_js_default( $id, $options ) {
	if ( version_compare( TB_FRAMEWORK_VERSION, '2.4.0', '<' ) ) {
		wp_enqueue_script( 'roundabout' ); // @deprecated
	}
	?>
	<script>
	jQuery(document).ready(function($) {
		$(window).load(function() {
			$('#tb-slider-<?php echo $id; ?> .tb-loader').fadeOut('fast');
			$('#tb-slider-<?php echo $id; ?> .slider-inner').fadeIn('fast');
			$('#tb-slider-<?php echo $id; ?> .carrousel-slider').roundabout({
				// minOpacity: '1',
				<?php if ( $options['nav_arrows'] ) : ?>
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

	if ( empty( $options['slices'] ) ) {
		$options['slices'] = '15';
	}

	if ( empty( $options['boxcols'] ) ) {
		$options['boxcols'] = '8';
	}

	if ( empty( $options['boxrows'] ) ) {
		$options['boxrows'] = '4';
	}

	if ( version_compare( TB_FRAMEWORK_VERSION, '2.4.0', '<' ) ) {
		wp_enqueue_script( 'nivo' ); // @deprecated
	}
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
	if ( $settings['nav_standard'] === false || $settings['nav_standard'] == '0' || $settings['nav_standard'] == 'false' ) {
		$settings['nav_standard'] = false;
	}

	if ( $settings['nav_arrows'] === false || $settings['nav_arrows'] == '0' || $settings['nav_arrows'] == 'false' ) {
		$settings['nav_arrows'] = false;
	}

	if ( $settings['pause_play'] === false || $settings['pause_play'] == '0' || $settings['pause_play'] == 'false' ) {
		$settings['pause_play'] = false;
	}

	// Configure additional CSS classes
	$classes = '';

	if ( function_exists('themeblvd_get_classes') ) {
		$classes = themeblvd_get_classes( 'slider_standard', true );
	}

	$classes .= $settings['nav_standard'] ? ' show-nav_standard' : ' hide-nav_standard';
	$classes .= $settings['nav_arrows'] ? ' show-nav_arrows' : ' hide-nav_arrows';
	$classes .= $settings['pause_play'] ? ' show-pause_play' : ' hide-pause_play';

	if ( ! $settings['nav_standard'] && ! $settings['nav_arrows'] ) {
		$classes .= ' hide-full_nav';
	}
	$classes = apply_filters( 'themeblvd_slider_wrapper_classes', $classes );

	$row_class = 'row tight';

	if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '<') ) {
		$row_class = 'grid-protection';
	}

	// Hide on mobile?
	$hide = '';
	if ( isset( $settings['mobile_fallback'] ) ) {
		if ( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) {
			$hide = true;
		}
	}

	// Start output
	do_action( 'themeblvd_standard_slider_plugin_js', $slider, $settings );
	?>
	<div id="tb-slider-<?php echo $slider; ?>" class="slider-wrapper standard-slider-wrapper<?php if ($hide) echo ' slider_has_mobile_fallback';?>">
		<div class="slider-inner<?php echo $classes; ?>">
			<div class="slides-wrapper slides-wrapper-<?php echo $slider; ?>">
				<div class="slides-inner">
					<?php if ( ! empty( $slides ) ) : ?>
						<div class="slider standard-slider flexslider">
							<?php if ( function_exists('themeblvd_loader') ) : ?>
								<?php themeblvd_loader(); ?>
							<?php else : ?>
								<div class="tb-loader"></div>
							<?php endif; ?>
							<ul class="slides">
								<?php foreach ( $slides as $slide ) : ?>
									<?php $media_atts = themeblvd_sliders_get_media_atts( $slider, $slide, $settings ); ?>
									<li class="slide tight <?php echo themeblvd_sliders_get_slide_classes( $slider, $slide, $media_atts ); ?>">
										<div class="slide-body">
											<div class="<?php echo $row_class; ?>">
												<?php if ( isset( $slide['custom'] ) ) : ?>
													<?php echo apply_filters( 'themeblvd_sliders_custom_content', $slide['custom'] ); ?>
												<?php else : ?>
													<?php themeblvd_slide_content( $slider, $slide, $settings ); ?>
													<div class="media <?php echo $slide['slide_type']; ?> <?php echo themeblvd_sliders_get_grid_class( $slide['position'], 'media' ); ?>">
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
	if ( isset( $settings['mobile_fallback'] ) ) {
		if ( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) {
			do_action( 'themeblvd_slider_fallback', $slider, $slides, $settings['mobile_fallback'], $settings );
		}
	}
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
	$classes = '';
	if ( function_exists('themeblvd_get_classes') ) {
		$classes = themeblvd_get_classes( 'slider_carrousel', true );
	}

	// Hide on mobile?
	if ( isset( $settings['mobile_fallback'] ) ) {
		if ( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) {
			$classes .= ' slider_has_mobile_fallback';
		}
	}
	?>
	<div id="tb-slider-<?php echo $slider; ?>" class="slider-wrapper carrousel-slider-wrapper<?php echo $classes; ?>">
		<div class="tb-loader"></div>
		<div class="slider-inner">
			<?php if ( $settings['nav_arrows'] ) : ?>
			<div class="roundabout-nav">
				<a href="#" title="Previous" class="prev"><i class="fa fa-arrow-circle-left"></i></a>
				<a href="#" title="Next" class="next"><i class="fa fa-arrow-circle-right"></i></a>
			</div><!-- .roundabout-nav (end) -->
			<?php endif; ?>
			<ul class="carrousel-slider">
				<?php if ( $slides ) : ?>
					<?php foreach ( $slides as $slide ) : ?>
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
	if ( isset( $settings['mobile_fallback'] ) ) {
		if ( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) {
			do_action( 'themeblvd_slider_fallback', $slider, $slides, $settings['mobile_fallback'], $settings );
		}
	}
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
	if ( $settings['nav_standard'] === false || $settings['nav_standard'] == '0' || $settings['nav_standard'] == 'false' ) {
		$settings['nav_standard'] = false;
	}

	if ( $settings['nav_arrows'] === false || $settings['nav_arrows'] == '0' || $settings['nav_arrows'] == 'false' ) {
		$settings['nav_arrows'] = false;
	}

	// Configure additional CSS classes
	$classes = '';

	if ( function_exists('themeblvd_get_classes') ) {
		$classes = themeblvd_get_classes( 'slider_nivo', true );
	}

	$classes .= $settings['nav_standard'] ? ' show-nav_standard' : ' hide-nav_standard';
	$classes .= $settings['nav_arrows'] ? ' show-nav_arrows' : ' hide-nav_arrows';
	if ( ! $settings['nav_standard'] && ! $settings['nav_arrows'] ) {
		$classes .= ' hide-full_nav';
	}
	$classes = apply_filters( 'themeblvd_slider_wrapper_classes', $classes );

	// Hide on mobile?
	$hide = '';
	if ( isset( $settings['mobile_fallback'] ) ) {
		if ( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) {
			$hide = true;
		}
	}

	// Insert javascript
	do_action( 'themeblvd_nivo_slider_js', $slider, $settings );
	?>

	<div id="tb-slider-<?php echo $slider; ?>" class="slider-wrapper tb-nivo-slider-wrapper nivo-slider-wrapper<?php if ($hide) echo ' slider_has_mobile_fallback';?>">
		<div class="slider-inner<?php echo $classes; ?>">
			<div class="slides-wrapper slides-wrapper-<?php echo $slider; ?>">
				<div class="slides-inner">
					<?php if ( function_exists('themeblvd_loader') ) : ?>
						<?php themeblvd_loader(); ?>
					<?php else : ?>
						<div class="tb-loader"></div>
					<?php endif; ?>
					<?php if ( $slides ) : ?>
						<div class="slider nivoSlider">
							<?php
							foreach ( $slides as $slide ) :
					            $image = themeblvd_get_slide_media( themeblvd_sliders_get_media_atts( $slider, $slide, $settings, 'nivo' ), $settings, 'nivo' );
			        			if ( themeblvd_slide_has_element( 'headline', $slide ) || themeblvd_slide_has_element( 'description', $slide ) )
			        				$image = str_replace( ' />', sprintf(' title="#%s" />', $slide['slide_id'].'_desc'), $image );
			        			echo $image = apply_filters( 'themeblvd_nivo_image', $image, $slider, $slide, $settings )."\n";
			        		endforeach;
			        		?>
				        </div><!-- .nivoSlider (end) -->
				        <?php foreach ( $slides as $slide ) : ?>
				        	<?php if ( themeblvd_slide_has_element( 'headline', $slide ) || themeblvd_slide_has_element( 'description', $slide ) ) : ?>
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
	if ( isset( $settings['mobile_fallback'] ) ) {
		if ( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) {
			do_action( 'themeblvd_slider_fallback', $slider, $slides, $settings['mobile_fallback'], $settings );
		}
	}
}

/**
 * Bootstrap Carousel Slider - default action for themeblvd_bootstrap_slider
 *
 * @since 1.2.0
 *
 * @param var $slider ID of current slider
 * @param array $settings Current settings for slider
 * @param array $slides Current slides for slider
 */
function themeblvd_bootstrap_slider_default( $slider, $settings, $slides ) {

	// Extend slides
	$slides = apply_filters( 'themeblvd_bootstrap_slides', $slides, $slider, $settings );

	$nav_standard = true;
	if ( isset( $settings['nav_standard'] ) ) {
		if ( $settings['nav_standard'] === false || $settings['nav_standard'] == '0' || $settings['nav_standard'] == 'false' ) {
			$nav_standard = false;
		}
	}

	$nav_arrows = true;
	if ( isset( $settings['nav_arrows'] ) ) {
		if ( $settings['nav_arrows'] === false || $settings['nav_arrows'] == '0' || $settings['nav_arrows'] == 'false' ) {
			$nav_arrows = false;
		}
	}

	$nav_thumbs = false;
	if ( isset( $settings['nav_thumbs'] ) ) {
		if ( $settings['nav_thumbs'] === true || $settings['nav_thumbs'] == '1' || $settings['nav_thumbs'] == 'true' ) {
			$nav_thumbs = true;
		}
	}

	// Insert javascript -- Nothing hooked here by default
	do_action( 'themeblvd_bootstrap_slider_js', $slider, $settings );

	// CSS Classes
	$classes = '';

	if ( function_exists('themeblvd_get_classes') ) {
		$classes = themeblvd_get_classes( 'slider_bootstrap', true );
	}

	// Hide on mobile?
	if ( isset( $settings['mobile_fallback'] ) ) {
		if ( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) {
			$classes .= ' slider_has_mobile_fallback';
		}
	}

	// Speed
	$speed = '0';
	if ( $settings['interval'] ) {
		$speed = $settings['interval'].'000';
	}
	?>
	<div id="tb-slider-<?php echo $slider; ?>" class="tb-bootstrap-carousel tb-simple-slider carousel slide <?php echo $classes; ?>" data-ride="carousel" data-interval="<?php echo $speed; ?>" data-pause="<?php echo $settings['pause']; ?>" data-wrap="<?php echo $settings['wrap']; ?>">

		<div class="carousel-control-wrap">

			<!-- Indicators -->
			<?php if ( $nav_standard && $slides ) : ?>
				<?php $counter = 0; ?>
				<ol class="carousel-indicators">
					<?php foreach ( $slides as $slide ) : ?>
						<li data-target="#tb-slider-<?php echo $slider; ?>" data-slide-to="<?php echo $counter; ?>" class="<?php if( $counter == 0 ) echo 'active'; ?>"></li>
						<?php $counter++; ?>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>

			<!-- Controls -->
			<?php if ( $nav_arrows ) : ?>
				<a class="left carousel-control" href="#tb-slider-<?php echo $slider; ?>" data-slide="prev">
					<span class="glyphicon glyphicon-chevron-left"></span>
				</a>
				<a class="right carousel-control" href="#tb-slider-<?php echo $slider; ?>" data-slide="next">
					<span class="glyphicon glyphicon-chevron-right"></span>
				</a>
			<?php endif; ?>

			<!-- Wrapper for slides (according to Bootstrap specs) -->
			<div class="carousel-inner">

				<?php if ( $slides ) : ?>
					<?php $counter = 0; ?>
					<?php foreach ( $slides as $slide ) : ?>
						<div class="item <?php if( $counter == 0 ) echo 'active'; ?>">

							<?php echo themeblvd_slide_media( themeblvd_sliders_get_media_atts( $slider, $slide, $settings, 'bootstrap' ), $settings, 'bootstrap' ); ?>

							<?php if ( themeblvd_slide_has_element( 'headline', $slide ) || themeblvd_slide_has_element( 'description', $slide ) ) : ?>
								<div class="carousel-caption">

									<?php if ( themeblvd_slide_has_element( 'headline', $slide ) ) : ?>
										<h3><?php echo stripslashes( $slide['elements']['headline'] ); ?></h3>
									<?php endif; ?>

									<?php if ( themeblvd_slide_has_element( 'description', $slide ) ) : ?>
										<?php echo apply_filters( 'themeblvd_the_content', stripslashes( $slide['elements']['description'] ) ); ?>
									<?php endif; ?>

								</div><!-- .carousel-caption (end) -->
							<?php endif; ?>

						</div><!-- .item (end) -->
						<?php $counter++; ?>
					<?php endforeach; ?>
				<?php endif; ?>

			</div><!-- .carousel-inner (end) -->

		</div><!-- .carousel-control-wrap (end) -->

		<!-- Thumbnail Indicators -->
		<?php if ( $nav_thumbs && $slides ) : ?>
			<?php $counter = 0; ?>
			<ul class="carousel-thumb-nav list-unstyled clearfix">
				<?php foreach ( $slides as $slide ) : ?>
					<li data-target="#tb-slider-<?php echo $slider; ?>" data-slide-to="<?php echo $counter; ?>" class="<?php if( $counter == 0 ) echo 'active'; ?>">
						<?php themeblvd_slide_thumbnail( $slider, $slide, $settings, 'bootstrap' ); ?>
					</li>
					<?php $counter++; ?>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

	</div>
	<?php
    // Display fallback if necessary
	if ( isset( $settings['mobile_fallback'] ) ) {
		if ( $settings['mobile_fallback'] == 'full_list' || $settings['mobile_fallback'] == 'first_slide' ) {
			do_action( 'themeblvd_slider_fallback', $slider, $slides, $settings['mobile_fallback'], $settings );
		}
	}
}

/**
 * Slidebar mobile fallback
 *
 * @since 1.0.0
 *
 * @param string $slider ID of current slider
 * @param array $slides Current slides for slider
 * @param string $fallback Type of fallback, full_list or first_slide
 * @param array $settings All current settings for slider
 */
function themeblvd_slider_fallback_default( $slider, $slides, $fallback, $settings = array() ) {

	echo '<div class="slider-fallback">';
	echo '<div class="slider-fallback-inner '.$fallback.'">';

	if ( $slides ) {

		echo '<ul class="slider-fallback-list">';

		foreach ( $slides as $slide ) {
			echo '<li class="slider-fallback-slide">';
			echo '<div class="slider-fallback-slide-body">';
				if ( isset( $slide['custom'] ) ) {

					// Custom Slide
					echo apply_filters( 'themeblvd_sliders_custom_content', $slide['custom'] );

				} else {

					// Headline
					if ( themeblvd_slide_has_element( 'headline', $slide ) ) {
						printf( '<h2>%s</h2>', stripslashes($slide['elements']['headline']) );
					}

					// Media (Image or Video)
					themeblvd_slide_media( themeblvd_sliders_get_media_atts( $slider, $slide, $settings, 'fallback' ), array(), 'fallback' );

					// Description
					if ( themeblvd_slide_has_element( 'description', $slide ) ) {

						$text = stripslashes( $slide['elements']['description'] );

						if ( apply_filters( 'themeblvd_fallback_slider_desc', true, $slide, $slider, $settings ) ) {
							$text = apply_filters( 'themeblvd_the_content', $text );
						}

						printf( '<div class="slide-description-text">%s</div>', $text );

					}

					// Button
					if ( themeblvd_slide_has_element( 'button', $slide ) ) {

						$button_atts = apply_filters( 'themeblvd_fallback_slider_button', array(
							'text' 		=> $slide['elements']['button']['text'],
							'url'		=> $slide['elements']['button']['url'],
							'color'		=> 'default',
							'target'	=> $slide['elements']['button']['target'],
							'size'		=> 'medium'

						), $slide, $slider, $settings, 'fallback' );

						printf( '<div class="slide-description-button">%s</div>', themeblvd_button( stripslashes( $button_atts['text'] ), $button_atts['url'], $button_atts['color'], $button_atts['target'], $button_atts['size'] ) );

					}
				}

			echo '</div><!-- .slider-fallback-slide-body (end) -->';
			echo '</li>';

			// End the loop after first slide if we're only showing the first slide.
			if ( $fallback == 'first_slide' ) {
				break;
			}

		} // End foreach ($slides)

		echo '</ul><!-- .slider-fallback-list (end) -->';

	} // End if ($slides)

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

	// Output
	ob_start();

	if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '>=' ) ) {

		themeblvd_slider( $id );

	} else {

		$type = get_post_meta( $slider_id, 'type', true );
		$classes = 'element element-slider element-slider-'.$type.themeblvd_get_classes( 'element_slider', true );

		echo '<div class="'.$classes.'">';
		echo '<div class="element-inner">';
		echo '<div class="element-inner-wrap">';
		echo '<div class="grid-protection">';
		themeblvd_slider( $id );
		echo '</div><!-- .grid-protection (end) -->';
		echo '</div><!-- .element-inner-wrap (end) -->';
		echo '</div><!-- .element-inner (end) -->';
		echo '</div><!-- .element (end) -->';

	}

	return ob_get_clean();
}

/**
 * Post slider shortcode - [post_slider]
 *
 * @since 1.0.2
 * @deprecated 1.2.2
 *
 * @param array $atts Standard WordPress shortcode attributes
 */
function themeblvd_shortcode_slider_auto( $atts ) {

	if ( ! function_exists( 'themeblvd_slider_auto' ) ) {
		return __( 'Your theme does not support the [post_slider] shortcode.', 'theme-blvd-sliders' );
	}

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
		'category'			=> '',				// Category slug(s) to include
		//'category_name'	=> '',				// Category slug(s) to include
		//'cat'				=> '',				// Category ID(s) to include/exclude
		'portfolio'     	=> '',				// Portfolio(s) slugs to include, requires Portfolios plugin
        'portfolio_tag' 	=> '',				// Portfolio Tag(s) to include, requires Portfolios plugin
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