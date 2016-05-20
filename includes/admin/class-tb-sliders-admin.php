<?php
/**
 * Sliders Admin
 */
class Theme_Blvd_Sliders_Admin {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add slider admin page
		add_action( 'admin_menu', array( $this, 'add_page' ) );

		// Filter on javascript locals specifically for slider Manager
		// onto Theme Blvd framework locals.
		add_filter( 'themeblvd_locals_js', array( $this, 'add_js_locals' ) );

		// Add ajax functionality to slider admin page
		include_once( TB_SLIDERS_PLUGIN_DIR . '/includes/admin/class-tb-sliders-ajax.php' );
		$ajax = new Theme_Blvd_Sliders_Ajax( $this );

	}

	/**
	 * Add admin page
	 *
	 * @since 1.0.0
	 */
	public function add_page() {

		// Create new admin page
		$admin_page = add_menu_page( __('Slider Manager', 'theme-blvd-sliders'), __('Sliders', 'theme-blvd-sliders'), themeblvd_admin_module_cap('sliders'), 'themeblvd_sliders', array($this, 'admin_page'), null, 31 );

		// Attach assets to new admin page
		add_action( 'admin_print_styles-'.$admin_page, array( $this, 'load_styles' ) );
		add_action( 'admin_print_scripts-'.$admin_page, array( $this, 'load_scripts' ) );

		// Media Uploader fallback
		if ( ! function_exists('wp_enqueue_media') || ! function_exists('themeblvd_media_uploader') ) {
			add_action( 'admin_print_styles-'.$admin_page, 'optionsframework_mlu_css', 0 );
			add_action( 'admin_print_scripts-'.$admin_page, 'optionsframework_mlu_js', 0 );
		}
	}

	/**
	 * Loads the CSS
	 *
	 * @since 1.0.0
	 */
	public function load_styles() {
		wp_enqueue_style( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_sliders', TB_SLIDERS_PLUGIN_URI . '/includes/admin/css/sliders-style.min.css', null, TB_SLIDERS_PLUGIN_VERSION );
	}

	/**
	 * Loads the javascript
	 *
	 * @since 1.0.0
	 */
	public function load_scripts() {

		// WP-packaged scripts
		wp_enqueue_script( 'jquery-ui-core');
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'postbox' );

		if ( function_exists('wp_enqueue_media') && function_exists('themeblvd_media_uploader') ) {
			wp_enqueue_media();
		}

		// Theme Blvd scripts
		wp_enqueue_script( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/js/shared.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/js/options.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'themeblvd_sliders', TB_SLIDERS_PLUGIN_URI . '/includes/admin/js/sliders.min.js', array('jquery'), TB_SLIDERS_PLUGIN_VERSION );

		// Localize scripts
		wp_localize_script( 'themeblvd_sliders', 'themeblvd', themeblvd_get_admin_locals( 'js' ) );
	}

	/**
	 * Add javascript locals for Sliders manager onto
	 * framework js locals that are already established.
	 *
	 * @since 1.1.0
	 */
	public function add_js_locals( $current ) {
		$new = array(
			'edit_slider'		=> __( 'Edit Slider', 'theme-blvd-sliders' ),
			'delete_slider'		=> __( 'Are you sure you want to delete the slider(s)?', 'theme-blvd-sliders' ),
			'invalid_slider'	=> __( 'Oops! Somehow, you\'ve entered an invalid slider type.', 'theme-blvd-sliders' ),
			'slider_created'	=> __( 'Slider created!', 'theme-blvd-sliders' )
		);
		return array_merge($current, $new);
	}

	/**
	 * Builds out the full admin page.
	 *
	 * @since 1.0.0
	 */
	function admin_page() {
		$api = Theme_Blvd_Sliders_API::get_instance();
		$types = $api->get_sliders();
		?>
		<div id="slider_blvd">
			<div id="optionsframework" class="wrap tb-options-js">

			    <div class="admin-module-header">
			    	<?php do_action( 'themeblvd_admin_module_header', 'sliders' ); ?>
			    </div>
			    <?php screen_icon( 'tb_sliders' ); ?>
			    <h2 class="nav-tab-wrapper">
			        <a href="#manage" id="manage-tab" class="nav-tab" title="<?php _e( 'Manage Sliders', 'theme-blvd-sliders' ); ?>"><?php _e( 'Manage Sliders', 'theme-blvd-sliders' ); ?></a>
			        <a href="#add" id="add-tab" class="nav-tab" title="<?php _e( 'Add New Slider', 'theme-blvd-sliders' ); ?>"><?php _e( 'Add Slider', 'theme-blvd-sliders' ); ?></a>
			        <a href="#edit" id="edit-tab" class="nav-tab nav-edit-slider" title="<?php _e( 'Edit Slider', 'theme-blvd-sliders' ); ?>"><?php _e( 'Edit Slider', 'theme-blvd-sliders' ); ?></a>
			    </h2>

				<!-- MANAGE SLIDER (start) -->

				<div id="manage" class="group">
			    	<form id="manage_sliders">
			    		<?php
			    		$manage_nonce = wp_create_nonce( 'optionsframework_manage_sliders' );
						echo '<input type="hidden" name="option_page" value="optionsframework_manage_sliders" />';
						echo '<input type="hidden" name="_wpnonce" value="'.$manage_nonce.'" />';
						?>
						<div class="ajax-mitt"><?php $this->manage_sliders(); ?></div>
					</form><!-- #manage_sliders (end) -->
				</div><!-- #manage (end) -->

				<!-- MANAGE SLIDER (end) -->

				<!-- ADD SLIDER (start) -->

				<div id="add" class="group">
					<form id="add_new_slider">
						<?php
						$add_nonce = wp_create_nonce( 'optionsframework_new_slider' );
						echo '<input type="hidden" name="option_page" value="optionsframework_add_slider" />';
						echo '<input type="hidden" name="_wpnonce" value="'.$add_nonce.'" />';
						$this->new_slider( $types );
						?>
					</form><!-- #add_new_slider (end) -->
				</div><!-- #manage (end) -->

				<!-- ADD SLIDER (end) -->

				<!-- EDIT SLIDER (start) -->

				<div id="edit" class="group">
					<form id="edit_slider" method="post">
						<?php
						$edit_nonce = wp_create_nonce( 'optionsframework_save_slider' );
						echo '<input type="hidden" name="action" value="update" />';
						echo '<input type="hidden" name="option_page" value="optionsframework_edit_slider" />';
						echo '<input type="hidden" name="_wpnonce" value="'.$edit_nonce.'" />';
						?>
						<div class="ajax-mitt"><!-- AJAX inserts edit slider page here. --></div>
					</form>
				</div><!-- #manage (end) -->

				<!-- EDIT SLIDER (end) -->

				<div class="admin-module-footer">
					<?php do_action( 'themeblvd_admin_module_footer', 'sliders' ); ?>
				</div>

			</div><!-- #optionsframework (end) -->
		</div><!-- #slider_blvd (end) -->
		<?php
	}

	/**
	 * Get default slider options when adding a new slider.
	 * This is run from the AJAX action called when saving
	 * a new slider so that when the user edits the slider
	 * for the first time, the default settings will already
	 * be saved.
	 *
	 * @since 1.0.0
	 *
	 * @param $type string type of slider
	 * @return $default_options array all default options
	 */
	public function slider_defaults( $type ) {

		$api = Theme_Blvd_Sliders_API::get_instance();
		$sliders = $api->get_sliders();

		$default_options = array();

		// Set options or return error if type doesn't exist
		if ( isset( $sliders[$type]['options'] ) ) {
			$options = $sliders[$type]['options'];
		} else {
			return 'error_type';
		}

		// Set the options
		foreach ( $options as $option ) {
			if ( isset( $option['std'] ) ) {
				$default_options[$option['id']] = $option['std'];
			} else {
				$default_options[$option['id']] = null;
			}
		}

		// Return an error if options weren't found
		if ( empty( $default_options ) ) {
			$default_options = 'error';
		}

		return $default_options;

	}

	/**
	 * Insert the correct value for an option within a
	 * slide when using the edit_slide() function.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slide_options any currently saved options for this slide.
	 * @param string $type Type of slider
	 * @param string $sub_type Nested type of options (optional)
	 * @return string $value Value for slide's specific option
	 */
	public function slide_value( $slide_options, $type, $sub_type = null ) {

		$value = null;

		switch( $type ) {

			// Slide Type
			case 'slide_type' :
	    		if ( isset( $slide_options['slide_type'] ) ) {
	    			$value = $slide_options['slide_type'];
	    		}
				break;

	    	// Image position
	    	case 'position' :
	    		if ( isset( $slide_options['position'] ) ) {
	    			$value = $slide_options['position'];
	    		}
	    		break;

	    	// Image position
	    	case 'image_size' :
	    		if ( isset( $slide_options['image']['size'] ) ) {
	    			$value = $slide_options['image']['size'];
	    		}
	    		break;

	    	// Included elements
	    	case 'include' :
	    		if ( isset( $slide_options['elements']['include'] ) && in_array( $sub_type, $slide_options['elements']['include'] ) ) {
	    			$value = ' checked="checked"';
	    		}
	    		break;

	    	// Image
	    	case 'image' :
	    		if ( isset( $slide_options['image'] ) ) {
	    			$value = $slide_options['image'];
	    		}
	    		break;

	    	// Video
	    	case 'video' :
	    		if ( isset( $slide_options['video'] ) ) {
	    			$value = $slide_options['video'];
	    		}
	    		break;

			// Video Height
			case 'video_height' :
				if ( isset( $slide_options['video_height'] ) ) {
					$value = $slide_options['video_height'];
				} else {
					$value = 0;
				}
				break;

			// Image link
	    	case 'image_link' :
				if ( $sub_type == 'target' ) {

					if ( isset( $slide_options['elements']['image_link']['target'] ) ) {
	    				$value = $slide_options['elements']['image_link']['target'];
	    			}

				} else if ( $sub_type == 'url' ) {

					if ( isset( $slide_options['elements']['image_link']['url'] ) ) {
	    				$value = $slide_options['elements']['image_link']['url'];
	    			}

				}
				break;

			// Button
			case 'button' :
				if ( $sub_type == 'text' ) {

					if ( isset( $slide_options['elements']['button']['text'] ) ) {
	    				$value = stripslashes( $slide_options['elements']['button']['text'] );
	    			}

				} else if ( $sub_type == 'target' ) {

					if ( isset( $slide_options['elements']['button']['target']) ) {
	    				$value = $slide_options['elements']['button']['target'];
	    			}

				} else if ( $sub_type == 'url' ) {

					if ( isset( $slide_options['elements']['button']['url'] ) ) {
	    				$value = $slide_options['elements']['button']['url'];
	    			}

				}
				break;

			// Headline
	    	case 'headline' :
				if ( isset( $slide_options['elements']['headline'] ) ) {
					$value = stripslashes( $slide_options['elements']['headline'] );
				}
				break;

			// Description
			case 'description' :
				if ( isset( $slide_options['elements']['description'] ) ) {
					$value = stripslashes( $slide_options['elements']['description'] );
				}
				break;

			// Custom Content
			case 'custom' :
				if ( isset( $slide_options['custom'] ) ) {
					$value = stripslashes( $slide_options['custom'] );
				}
				break;

		} // End switch $type

		return $value;

	}

	/**
	 * Generates the the interface to manage sliders.
	 *
	 * @since 1.0.0
	 */
	public function manage_sliders() {

		// Setup columns for management table
		$columns = array(
			array(
				'name' 		=> __( 'Slider Title', 'theme-blvd-sliders' ),
				'type' 		=> 'title',
			),
			array(
				'name' 		=> __( 'Slider ID', 'theme-blvd-sliders' ),
				'type' 		=> 'slug',
			),
			/* Hiding the true post ID from user to avoid confusion.
			array(
				'name' 		=> __( 'Slider ID', 'theme-blvd-sliders' ),
				'type' 		=> 'id',
			),
			*/
			array(
				'name' 		=> __( 'Slider Type', 'theme-blvd-sliders' ),
				'type' 		=> 'meta',
				'config' 	=> 'type' // Meta key to use to get value
			),
			array(
				'name' 		=> __( 'Shortcode Usage', 'theme-blvd-sliders' ),
				'type' 		=> 'shortcode',
				'config' 	=> 'slider' // Shortcode key
			),
		);
		$columns = apply_filters( 'themeblvd_manage_sliders', $columns );

		// Display it all
		echo '<div class="metabox-holder">';
		echo themeblvd_post_table( 'tb_slider', $columns );
		echo '</div><!-- .metabox-holder (end) -->';
	}

	/**
	 * Generates the the interface to add a new slider.
	 *
	 * @since 1.0.0
	 *
	 * @param $types array All default sliders
	 */
	public function new_slider( $types ) {

		// Setup slider types for options array
		$slider_types = array();
		foreach ( $types as $type ) {
			$slider_types[$type['id']] = $type['name'];
		}

		// Setup options array to display form
		$options = array(
			array(
				'name' 		=> __( 'Slider Name', 'theme-blvd-sliders' ),
				'desc' 		=> __( 'Enter a user-friendly name for your slider.<br>Example: My Slider', 'theme-blvd-sliders' ),
				'id' 		=> 'slider_name',
				'type' 		=> 'text'
			),
			array(
				'name' 		=> __( 'Slider Type', 'theme-blvd-sliders' ),
				'desc' 		=> __( 'Select which type of slider among the currently supported slider types.', 'theme-blvd-sliders' ),
				'id' 		=> 'slider_type',
				'type' 		=> 'select',
				'options' 	=> $slider_types
			)
		);
		$options = apply_filters( 'themeblvd_add_slider_options', $options );

		// Build form
		$form = themeblvd_option_fields( 'options', $options, null, false );
		?>
		<div class="metabox-holder">
			<div class="postbox">
				<h3><?php _e( 'Add New Slider', 'theme-blvd-sliders' ); ?></h3>
				<form id="add_new_slider">
					<div class="inner-group">
						<?php echo $form[0]; ?>
					</div><!-- .group (end) -->
					<div id="optionsframework-submit">
						<input type="submit" class="button-primary" name="update" value="<?php _e( 'Add New Slider', 'theme-blvd-sliders' ); ?>">
						<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
			            <div class="clear"></div>
					</div>
				</form><!-- #add_new_slider (end) -->
			</div><!-- .postbox (end) -->
		</div><!-- .metabox-holder (end) -->
		<?php
	}

	/**
	 * Generates the an indivdual panel to edit a slide.
	 * This has been broken into a separate public function because
	 * not only does it show each slide when loading the
	 * Edit Slider screen, but it's used to insert a new
	 * slide when called with AJAX.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slider_type type of slider
	 * @param string $slide_id ID for individual slide
	 * @param array $slide_options any current options for current slide
	 */
	public function edit_slide( $slider_id, $slider_type, $slide_id, $slide_options = null, $visibility = null ) {

		global $_wp_additional_image_sizes;

		$api = Theme_Blvd_Sliders_API::get_instance();
		$slider_types = $api->get_sliders();

		$current_slide_type = $this->slide_value( $slide_options, 'slide_type' );
		$current_image = $this->slide_value( $slide_options, 'image' );
		$current_video = $this->slide_value( $slide_options, 'video' );
		?>
		<div id="<?php echo $slide_id; ?>" class="widget slide-options"<?php if ( $visibility == 'hide' ) echo ' style="display:none"'; ?>>
			<div class="widget-name">
				<a href="#" class="widget-name-arrow">Toggle</a>
				<h3 class="image"><!-- Inserted here with JS --></h3>
				<span class="slide-summary"></span>
			</div><!-- .element-name (end) -->
			<div class="widget-content">
				<div class="slide-set-type">
					<strong><?php _e( 'Image Slide', 'theme-blvd-sliders' ); ?></strong>
					<select name="slides[<?php echo $slide_id; ?>][slide_type]">
						<?php
						foreach ( $slider_types[$slider_type]['types'] as $key => $value ) {
	        				echo '<option '.selected( $key, $current_slide_type, false ).' value="'.$key.'">'.$value['name'].'</option>';
	        			}
	        			?>
					</select>
				</div><!-- .slide-set-type (end) -->
				<div class="pad">
					<div class="slide-media controls grid-wrap">
						<div class="slide-set-media">
							<?php
							foreach ( $slider_types[$slider_type]['types'] as $type => $config ) {
								switch ( $type ) {
									case 'image' :
										?>
										<div class="slide-set-image">
											<h3><?php echo $config['main_title']; ?></h3>
											<div class="field section-upload">
												<?php
												if ( function_exists('wp_enqueue_media') && function_exists('themeblvd_media_uploader') ) {
													$title = isset( $current_image['title'] ) ? $current_image['title']: ''; // If updating from v1.0, this check prevents PHP warning.
													echo themeblvd_media_uploader( array( 'option_name' => 'slides['.$slide_id.']', 'type' => 'slider', 'id' => $slide_id.'image', 'value' => $current_image['url'], 'value_title' => $title, 'value_id' => $current_image['id'] ) );
												} else {
													// @deprecated
													echo optionsframework_medialibrary_uploader( 'slides['.$slide_id.']', 'slider', $slide_id.'image', $current_image, null, null, $slider_id, null, __( 'Get Image', 'theme-blvd-sliders' ) );
												}
												?>
											</div><!-- .field (end) -->
										</div><!-- .slide-set-image (end) -->
										<?php
										break;
									case 'video' :
										?>
										<div class="slide-set-video">
											<h3><?php echo $config['main_title']; ?></h3>
											<div class="field video-link">
												<?php
												echo '<input type="text" name="slides['.$slide_id.'][video]" placeholder="'.__('Video Link', 'themeblvd').'" value="'.$current_video.'" />';
												/*
												// @todo -- Incorporate "Get Video" button. Let's not bring this feature
												// to center-satge until we have a better handle on mp4's in WP 3.6.
												$current_video = $this->slide_value( $slide_options, 'video' );
												if ( function_exists('wp_video_shortcode') && function_exists('themeblvd_media_uploader') ) {
													echo '<div class="section-upload">';
													echo themeblvd_media_uploader( array( 'option_name' => 'slides', 'type' => 'video', 'name' => 'video', 'id' => $slide_id, 'value' => $current_video ) );
													echo '</div>';
													//echo '<input type="text" name="slides['.$slide_id.'][video]" value="'.$current_video.'" />';
												} else {
													// @deprecated with release of WP 3.6
													echo '<input type="text" name="slides['.$slide_id.'][video]" placeholder="'.__('Video Link', 'themeblvd').'" value="'.$current_video.'" />';
												}
												*/
												?>
												<p class="explain">
													<?php _e( 'Enter in a video URL compatible with <a href="http://codex.wordpress.org/Embeds" target="_blank">WordPress\'s oEmbed</a>.<br><br>Ex: http://youtube.com/watch?v=HPPj6viIBmU<br>Ex: http://vimeo.com/11178250', 'theme-blvd-sliders' ); ?>
													<?php /* @todo if ( function_exists('wp_video_shortcode') ) : ?>
														<br><?php _e('Ex: http://yoursite.com/uploads/video.mp4', 'theme-blvd-sliders'); ?>
													<?php endif; */ ?>
												</p>
											</div><!-- .field (end) -->
										</div><!-- .slide-set-video (end) -->
										<?php
										break;
								}
							}
							?>
						</div><!-- .slide-set-media (end) -->
						<div class="slide-include-elements">

							<div class="slide-section">
								<?php if ( $slider_types[$slider_type]['positions'] ) : ?>
									<h4 class="header_has_icon"><?php _e( 'Media Display', 'theme-blvd-sliders' ); ?></h4>
									<?php $position = $this->slide_value( $slide_options, 'position' ); ?>

									<select class="slide-position slide-position-image" name="slides[<?php echo $slide_id; ?>][position_image]">
										<?php
										foreach ( $slider_types[$slider_type]['positions'] as $key => $value ) {
					        				// Set name for option
					        				$name = '';
					        				switch( $key ) {
						        				case 'full' :
						        					$name = __( 'Full Size', 'theme-blvd-sliders' );
						        					break;
						        				case 'align-left' :
						        					$name = __( 'Aligned Left', 'theme-blvd-sliders' );
						        					break;
						        				case 'align-right' :
						        					$name = __( 'Aligned Right', 'theme-blvd-sliders' );
						        					break;
					        				}
					        				$exclude_dimensions = $key == 'full' && $slider_types[$slider_type]['custom_size'] ? true : false;
					        				echo '<option '.selected( $key, $position, false ).' value="'.$key.'">'.$this->get_image_size_desc($value, $name, $exclude_dimensions).'</option>';
					        			}
					        			?>
									</select>

									<?php if ( isset( $slider_types[$slider_type]['positions']['full'] ) && $slider_types[$slider_type]['custom_size'] ) : ?>
										<select class="slide-crop" name="slides[<?php echo $slide_id; ?>][image_size]">
											<?php
											$full_size = $slider_types[$slider_type]['positions']['full'];
											$image_size = $this->slide_value( $slide_options, 'image_size' );

											// First option is the default framework "slider-large" image size
											echo '<option '.selected( 'slider-large', $image_size, false ).' value="'.$full_size.'">'.$this->get_image_size_desc($full_size, __('Default', 'theme-blvd-sliders')).'</option>';

											// Now get all WP-registered image sizes and make them available for selection.
											$wp_image_sizes = get_intermediate_image_sizes();
											if ( $wp_image_sizes ) {
												foreach ( $wp_image_sizes as $size ) {
													if ( $size == $full_size ) {
														continue;
													}
													echo '<option '.selected( $size, $image_size, false ).' value="'.$size.'">'.$this->get_image_size_desc($size).'</option>';
												}
											}

											// Add option for raw image, with no crop.
											echo '<option '.selected( 'full', $image_size, false ).' value="full">'.__('Do not crop image.', 'theme-blvd-sliders').'</option>';
											?>
										</select>
									<?php endif; ?>

									<select class="slide-position slide-position-video" name="slides[<?php echo $slide_id; ?>][position_video]">
										<?php
										foreach ( $slider_types[$slider_type]['positions'] as $key => $value ) {
					        				// Set name for option
					        				$name = '';
					        				switch( $key ) {
						        				case 'full' :
						        					$name = __( 'Full Width', 'theme-blvd-sliders' );
						        					break;
						        				case 'align-left' :
						        					$name = __( 'Aligned Left', 'theme-blvd-sliders' );
						        					break;
						        				case 'align-right' :
						        					$name = __( 'Aligned Right', 'theme-blvd-sliders' );
						        					break;
					        				}
					        				echo '<option '.selected( $key, $position, false ).' value="'.$key.'">'.$name.'</option>';
					        			}
					        			?>
									</select>

									<div class="slide-video-height mini-control clearfix">
										<input type="text" value="<?php echo $this->slide_value( $slide_options, 'video_height' ); ?>" name="slides[<?php echo $slide_id; ?>][video_height]" class="numeric" />
										<label for="slides[<?php echo $slide_id; ?>][video_height]">
											<?php _e( 'Maximum Video Height (use 0 for no limit)', 'theme-blvd-sliders' ); ?>
										</label>
									</div>

									<p class="note image-note"><?php _e( 'When you upload an image, it must be at a minimum the size selected above in order for WordPress to generate and register the crop size. Images will be scaled down proportionally from their respective crop sizes depending on where the slider is placed.', 'theme-blvd-sliders' ); ?></p>

								<?php endif; ?>
							</div><!-- .slide-section (end) -->

							<?php if ( ! empty( $slider_types ) && ! empty( $slider_types[$slider_type]['elements'] ) ) : ?>
								<div class="slide-section">
									<h4><?php _e( 'Slide Elements', 'theme-blvd-sliders' ); ?></h4>
									<table class="widefat slide-elements">
										<tbody>
										<?php
										foreach ( $slider_types[$slider_type]['elements'] as $element ) {
											switch ( $element ) {

												case 'image_link' :
													if ( $key != 'video' ) {	// A video would never be wrapped in a link
														?>
														<tr class="element-image_link slide-element-header">
															<td class="slide-element-check"><input value="image_link" type="checkbox" name="slides[<?php echo $slide_id; ?>][elements][include][]"<?php echo $this->slide_value($slide_options, 'include', 'image_link'); ?> /></td>
															<td class="slide-element-name"><?php _e( 'Image Link', 'theme-blvd-sliders' ); ?></td>
															<td class="slide-element-help"><a href="#" class="help-icon tb-icon-help-circled tooltip-link" title="<?php _e( 'This will allow you to apply a link to the image of this slide. You can configure it to open a webpage or a lightbox popup of different media types. If you\'re linking to a Lightbox Image, use the image file URL. If you\'re linking to a Lightbox Video, you can put in the URL to the Vimeo or YouTube video page.', 'theme-blvd-sliders' ); ?>"></a></td>
														</tr>
														<tr class="element-image_link slide-element-options">
															<td colspan="3">
																<div class="field">
																	<h5><?php _e( 'Where should the link open?', 'theme-blvd-sliders' ); ?></h5>
																	<?php $target = $this->slide_value($slide_options, 'image_link', 'target'); ?>
																	<select name="slides[<?php echo $slide_id; ?>][elements][image_link][target]">
																		<option value="_self" <?php selected( $target, '_self' ); ?>><?php _e( 'Same Window', 'theme-blvd-sliders' ); ?></option>
																		<option value="_blank" <?php selected( $target, '_blank' ); ?>><?php _e( 'New Window', 'theme-blvd-sliders' ); ?></option>
																		<option value="lightbox" <?php selected( $target, 'lightbox' ); ?>><?php _e( 'Lightbox Image', 'theme-blvd-sliders' ); ?></option>
																		<option value="lightbox_video" <?php selected( $target, 'lightbox_video' ); ?>><?php _e( 'Lightbox Video', 'theme-blvd-sliders' ); ?></option>
																	</select>
																</div><!-- .field (end) -->
																<div class="field">
																	<h5><?php _e( 'Where should the link go?', 'theme-blvd-sliders' ); ?></h5>
																	<input name="slides[<?php echo $slide_id; ?>][elements][image_link][url]" type="text" value="<?php echo $this->slide_value($slide_options, 'image_link', 'url'); ?>" class="input" />
																	</div><!-- .class="more-info (end) -->
																</div><!-- .field (end) -->
															</td>
														</tr>
														<?php
													}
													break;

												case 'headline' :

													?>
													<tr class="element-headline slide-element-header">
														<td class="slide-element-check"><input value="headline" type="checkbox" name="slides[<?php echo $slide_id; ?>][elements][include][]"<?php echo $this->slide_value($slide_options, 'include', 'headline'); ?> /></td>
														<td class="slide-element-name"><?php _e( 'Headline', 'theme-blvd-sliders' ) ?></td>
														<td class="slide-element-help"><a href="#" class="help-icon tb-icon-help-circled tooltip-link" title="<?php _e( 'This will allow you to insert a simple headline on your slide. The location and style of this headline will vary depending on the design of the current theme.', 'theme-blvd-sliders' ); ?>"></a></td>
													</tr>
													<tr class="element-headline slide-element-options">
														<td colspan="3">
															<div class="field">
																<h5><?php _e( 'What should the headline say?', 'theme-blvd-sliders' ); ?></h5>
																<textarea name="slides[<?php echo $slide_id; ?>][elements][headline]"><?php echo $this->slide_value($slide_options, 'headline'); ?></textarea>
															</div><!-- .field (end) -->
														</td>
													</tr>
													<?php

													break;

												case 'description' :

													?>
													<tr class="element-description slide-element-header">
														<td class="slide-element-check"><input value="description" type="checkbox" name="slides[<?php echo $slide_id; ?>][elements][include][]"<?php echo $this->slide_value($slide_options, 'include', 'description'); ?> /></td>
														<td class="slide-element-name"><?php _e( 'Description', 'theme-blvd-sliders' ); ?></td>
														<td class="slide-element-help"><a href="#" class="help-icon tb-icon-help-circled tooltip-link" title="<?php _e( 'This will allow you to insert a simple description on your slide. The location and style of this description will vary depending on the design of the current theme.', 'theme-blvd-sliders' ); ?>"></a></td>
													</tr>
													<tr class="element-description slide-element-options">
														<td colspan="3">
															<div class="field">
																<h5><?php _e( 'What should the description say?', 'theme-blvd-sliders' ); ?></h5>
																<textarea name="slides[<?php echo $slide_id; ?>][elements][description]"><?php echo $this->slide_value($slide_options, 'description'); ?></textarea>
															</div><!-- .field (end) -->
														</td>
													</tr>
													<?php

													break;

												case 'button' :

													?>
													<tr class="element-button slide-element-header">
														<td class="slide-element-check"><input value="button" type="checkbox" name="slides[<?php echo $slide_id; ?>][elements][include][]"<?php echo $this->slide_value( $slide_options, 'include', 'button'); ?> /></td>
														<td class="slide-element-name"><?php _e( 'Button', 'theme-blvd-sliders' ); ?></td>
														<td class="slide-element-help"><a href="#" class="help-icon tb-icon-help-circled tooltip-link" title="<?php _e( 'This will allow you to include a button on your slide. You can configure it to open a webpage or a lightbox popup of different media types.', 'theme-blvd-sliders' ); ?>"></a></td>
													</tr>
													<tr class="element-button slide-element-options">
														<td colspan="3">
															<div class="field">
																<h5><?php _e( 'What should the button say?', 'theme-blvd-sliders' ); ?></h5>
																<input name="slides[<?php echo $slide_id; ?>][elements][button][text]" type="text" value="<?php echo esc_attr( $this->slide_value($slide_options, 'button', 'text') ); ?>" class="input" />
															</div><!-- .field (end) -->
															<div class="field">
																<h5><?php _e( 'Where should the link open?', 'theme-blvd-sliders' ); ?></h5>
																<?php $target = $this->slide_value($slide_options, 'button', 'target'); ?>
																<select name="slides[<?php echo $slide_id; ?>][elements][button][target]">
																	<option value="_self" <?php selected( $target, '_self' ); ?>><?php _e( 'Same Window', 'theme-blvd-sliders' ); ?></option>
																	<option value="_blank" <?php selected( $target, '_blank' ); ?>><?php _e( 'New Window', 'theme-blvd-sliders' ); ?></option>
																	<option value="lightbox" <?php selected( $target, 'lightbox' ); ?>><?php _e( 'Lightbox Image', 'theme-blvd-sliders' ); ?></option>
																	<option value="lightbox_video" <?php selected( $target, 'lightbox_video' ); ?>><?php _e( 'Lightbox Video', 'theme-blvd-sliders' ); ?></option>
																</select>
															</div><!-- .field (end) -->
															<div class="field">
																<h5><?php _e( 'Where should the link go?', 'theme-blvd-sliders' ); ?></h5>
																<input name="slides[<?php echo $slide_id; ?>][elements][button][url]" type="text" value="<?php echo esc_attr( $this->slide_value($slide_options, 'button', 'url') ); ?>" class="input" />
															</div><!-- .field (end) -->
														</td>
													</tr>
													<?php
													break;
											}
										}
										?>
										</tbody>
									</table>
									<p class="warning slide-elements-warning"><?php _e( 'You cannot have any elements on top of full-size video. If you\'d like to include elements, align the video to the right or left.', 'theme-blvd-sliders' ); ?></p>
								</div><!-- .slide-section (end) -->
							<?php endif; ?>
						</div><!-- .slide-include-elements (end) -->
						<div class="clear"></div>
					</div><!-- .grid-wrap (end) -->
					<?php if ( array_key_exists( 'custom', $slider_types[$slider_type]['types'] ) ) : ?>
					<div class="controls slide-custom">
						<h3><?php echo $slider_types[$slider_type]['types']['custom']['main_title']; ?></h3>
						<?php $custom = $this->slide_value( $slide_options, 'custom' ); ?>
						<textarea name="slides[<?php echo $slide_id; ?>][custom]"><?php echo $custom; ?></textarea>
					</div><!-- .slide-custom (end) -->
					<?php endif; ?>
				</div><!-- .pad (end) -->
				<div class="submitbox widget-footer">
					<a href="#<?php echo $slide_id; ?>" class="submitdelete delete-me" title="<?php _e( 'Are you sure you want to delete this slide?', 'theme-blvd-sliders' ); ?>"><?php _e( 'Delete Slide', 'theme-blvd-sliders' ); ?></a>
					<div class="clear"></div>
				</div><!-- .widget-footer (end) -->
			</div><!-- .element-content (end) -->
		</div><!-- .slide-options(end) -->
		<?php
	}

	/**
	 * Generates the the interface to edit slider.
	 *
	 * @since 1.0.0
	 *
	 * @param $id string ID of slider to edit
	 */
	public function edit_slider( $id ) {

		$api = Theme_Blvd_Sliders_API::get_instance();

		// Get slider custom post
		$slider = get_post($id);

		// Check for no post object returned from ID.
		if ( ! $slider ) {
			echo '<div class="error"><p>'.__('The slider could not be found.', 'theme-blvd-sliders').'</p></div>';
			return;
		}

		// Post ID
		$post_id = $slider->ID;

		// Meta data for slider post
		$current_slides = get_post_meta( $post_id, 'slides', true );
		$type = get_post_meta( $post_id, 'type', true );
		$settings = get_post_meta( $post_id, 'settings', true );

		// Check if slider type is valid.
		if ( ! $api->is_slider( $type ) ) {
			echo '<div class="error"><p>'.sprintf(__('The slider type "%s" is not valid.', 'theme-blvd-sliders'), $type).'</p></div>';
			return;
		}

		$type_info = $types = $api->get_sliders( $type );
		$options = $type_info['options'];
		?>
		<input type="hidden" name="slider_id" value="<?php echo $post_id; ?>" />
		<div id="poststuff" class="metabox-holder full-width has-right-sidebar">
			<div class="inner-sidebar">
				<div id="slider-publish" class="postbox postbox-publish">
					<h3 class="hndle" title="<?php echo __('Click to toggle', 'theme-blvd-sliders'); ?>"><?php _e( 'Publish', 'theme-blvd-sliders' ); ?> <?php echo stripslashes($slider->post_title); ?></h3>
					<div class="tb-widget-content submitbox">
						<div id="major-publishing-actions">
							<div id="delete-action">
								<a class="submitdelete delete_slider" href="#<?php echo $post_id; ?>"><?php _e( 'Delete', 'theme-blvd-sliders' ); ?></a>
							</div>
							<div id="publishing-action">
								<input class="button-primary" value="<?php _e( 'Update Slider', 'theme-blvd-sliders' ); ?>" type="submit" />
								<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" />
							</div>
							<div class="clear"></div>
						</div>
					</div><!-- .tb-widget-content (end) -->
				</div><!-- .post-box (end) -->
				<div id="slider-info" class="postbox postbox-slider-info closed">
					<div class="handlediv" title="<?php echo __('Click to toggle', 'theme-blvd-sliders'); ?>"><br></div>
					<h3 class="hndle"><?php _e('Slider Information', 'theme-blvd-sliders' ); ?></h3>
					<div class="tb-widget-content hide">
						<?php
						// Current settings
						$info_settings = array(
							'post_title' 	=> $slider->post_title,
							'post_name'		=> $slider->post_name
						);

						// Setup attribute options
						$info_options = array(
							array(
								'name'		=> __('Slider Name', 'theme-blvd-sliders' ),
								'id' 		=> 'post_title',
								'desc'		=> __('This title is just for you. It\'ll never be used outside of your WordPress admin panel.', 'theme-blvd-sliders'),
								'type' 		=> 'text'
							),
							array(
								'name' 		=> __('Slider ID', 'theme-blvd-sliders' ),
								'id' 		=> 'post_name',
								'desc'		=> __( 'Sliders are assigned based on this ID. So if you change this at any point, make sure to also update any builder elements, pages, or other options in which you\'ve assigned this specific slider.', 'theme-blvd-sliders' ),
								'type' 		=> 'text'
							)
						);

						// Display form element
						$form = themeblvd_option_fields( 'info', $info_options, $info_settings, false );
						echo $form[0];
						?>
					</div><!-- .tb-widget-content (end) -->
				</div><!-- .post-box (end) -->
				<?php if ( $options ) : ?>
					<div id="slider-options" class="postbox postbox-options closed">
						<div class="handlediv" title="<?php echo __('Click to toggle', 'theme-blvd-sliders'); ?>"><br></div>
						<h3 class="hndle"><?php echo $type_info['name'].' '.__( 'Options', 'theme-blvd-sliders' ); ?></h3>
						<div class="tb-widget-content hide">
							<?php
							// Slider Options
							$form = themeblvd_option_fields( 'options', $options, $settings, false );
							echo $form[0];
							?>
						</div><!-- .tb-widget-content (end) -->
					</div><!-- .post-box (end) -->
				<?php endif; ?>
			</div><!-- .inner-sidebar (end) -->
			<div id="post-body">
				<div id="post-body-content">
					<div id="titlediv">
						<div class="ajax-overlay"></div>
						<h2><?php printf( __( 'Manage %s Slides', 'theme-blvd-sliders' ), ucfirst($type) ); ?></h2>
						<a href="#<?php echo $post_id; ?>=><?php echo $type; ?>" id="add_new_slide" class="button-secondary"><?php _e( 'Add New Slide', 'theme-blvd-sliders' ); ?></a>
						<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
						<div class="clear"></div>
					</div><!-- #titlediv (end) -->
					<div id="sortable">
						<?php
						if ( ! empty( $current_slides ) ) {
							foreach ( $current_slides as $slide_id => $slide ) {
								$this->edit_slide( $post_id, $type, $slide_id, $slide );
							}
						} else {
							echo '<p class="warning no-item-yet">'.__( 'You haven\'t added any slides yet. Get started by clicking "Add New Slide" above.', 'theme-blvd-sliders' ).'</p>';
						}
    					?>
					</div><!-- .sortable-slides (end) -->
				</div><!-- #post-body-content (end) -->
			</div><!-- #post-body (end) -->
		</div><!-- .metabox-holder (end) -->
		<?php
	}

	/**
	 * Get a user-friendly description of a crop size.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	function get_image_size_desc( $id, $name = '', $exclude_dimensions = false ) {

		global $_wp_additional_image_sizes;

		// Can we just skip the dimensions?
		if ( $exclude_dimensions ) {
			$desc = $name ? $name : $id;
			return apply_filters( 'themeblvd_sliders_image_size_desc', $desc, $id, $name );
		}

		// Determine width, height, and crop mode
		if ( isset( $_wp_additional_image_sizes[$id]['width'] ) ) {
			$width = intval( $_wp_additional_image_sizes[$id]['width'] ); // For theme-added sizes
		} else {
			$width = get_option( "{$id}_size_w" ); // For default sizes set in options
		}

		if ( isset( $_wp_additional_image_sizes[$id]['height'] ) ) {
			$height = intval( $_wp_additional_image_sizes[$id]['height'] ); // For theme-added sizes
		} else {
			$height = get_option( "{$id}_size_h" ); // For default sizes set in options
		}

		if ( isset( $_wp_additional_image_sizes[$id]['crop'] ) ) {
			$crop = intval( $_wp_additional_image_sizes[$id]['crop'] ); // For theme-added sizes
		} else {
			$crop = get_option( "{$id}_crop" ); // For default sizes set in options
		}

		// Crop mode message
		if ( $crop ) {
			$crop_desc = __('hard crop', 'theme-blvd-sliders');
		} else if ( $height == 9999 ) {
			$crop_desc = __('no height crop', 'theme-blvd-sliders');
		} else {
			$crop_desc = __('soft crop', 'theme-blvd-sliders');
		}

		// Piece together description
		$desc = $name ? $name : $id;
		$desc = sprintf( "$desc (%d x %d, %s)", $width, $height, $crop_desc );
		if ( ! isset( $_wp_additional_image_sizes[$id] ) ) {
			$desc .= ' -- '.__('WordPress size', 'theme-blvd-sliders');
		}

		return apply_filters( 'themeblvd_sliders_image_size_desc', $desc, $id, $name );
	}
}
