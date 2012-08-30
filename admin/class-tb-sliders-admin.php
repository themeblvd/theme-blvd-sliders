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
		
		// Add ajax functionality to slider admin page
		include_once( TB_SLIDERS_PLUGIN_DIR . '/admin/class-tb-sliders-ajax.php' );
		$ajax = new Theme_Blvd_Sliders_Ajax( $this );
	
	}
	
	/**
	 * Add admin page
	 *
	 * @since 1.0.0
	 */
	public function add_page() {
		$admin_page = add_object_page( 'Slider Manager', 'Sliders', themeblvd_admin_module_cap( 'sliders' ), 'themeblvd_sliders', array( $this, 'admin_page' ), 'div', 31 );
		add_action( 'admin_print_styles-'.$admin_page, array( $this, 'load_styles' ) );
		add_action( 'admin_print_scripts-'.$admin_page, array( $this, 'load_scripts' ) );
		add_action( 'admin_print_styles-'.$admin_page, 'optionsframework_mlu_css', 0 );
		add_action( 'admin_print_scripts-'.$admin_page, 'optionsframework_mlu_js', 0 );
	}
	
	/** 
	 * Loads the CSS
	 *
	 * @since 1.0.0
	 */
	public function load_styles() {
		wp_enqueue_style( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/css/admin-style.min.css', null, TB_FRAMEWORK_VERSION );
		wp_enqueue_style( 'themeblvd_sliders', TB_SLIDERS_PLUGIN_URI . '/admin/css/sliders-style.min.css', null, TB_SLIDERS_PLUGIN_VERSION );
	}
	
	/**
	 * Loads the javascript
	 *
	 * @since 1.0.0 
	 */
	public function load_scripts() {
		wp_enqueue_script( 'jquery-ui-core');
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'themeblvd_admin', TB_FRAMEWORK_URI . '/admin/assets/js/shared.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_localize_script( 'themeblvd_admin', 'themeblvd', themeblvd_get_admin_locals( 'js' ) );
		wp_enqueue_script( 'themeblvd_options', TB_FRAMEWORK_URI . '/admin/options/js/options.min.js', array('jquery'), TB_FRAMEWORK_VERSION );
		wp_enqueue_script( 'themeblvd_sliders', TB_SLIDERS_PLUGIN_URI . '/admin/js/sliders.min.js', array('jquery'), TB_SLIDERS_PLUGIN_VERSION );
	}
	
	/**
	 * Builds out the full admin page. 
	 *
	 * @since 1.0.0
	 */
	function admin_page() {
		$types = $this->get_sliders();
		?>
		<div id="slider_blvd">
			<div id="optionsframework" class="wrap">
			    
			    <div class="admin-module-header">
			    	<?php do_action( 'themeblvd_admin_module_header', 'sliders' ); ?>
			    </div>
			    <?php screen_icon( 'tb_sliders' ); ?>
			    <h2 class="nav-tab-wrapper">
			        <a href="#manage" id="manage-tab" class="nav-tab" title="<?php _e( 'Manage Sliders', 'themeblvd_sliders' ); ?>"><?php _e( 'Manage Sliders', 'themeblvd_sliders' ); ?></a>
			        <a href="#add" id="add-tab" class="nav-tab" title="<?php _e( 'Add New Slider', 'themeblvd_sliders' ); ?>"><?php _e( 'Add Slider', 'themeblvd_sliders' ); ?></a>
			        <a href="#edit" id="edit-tab" class="nav-tab nav-edit-slider" title="<?php _e( 'Edit Slider', 'themeblvd_sliders' ); ?>"><?php _e( 'Edit Slider', 'themeblvd_sliders' ); ?></a>
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
		
		$default_options = array();
		$sliders = $this->get_sliders();
		
		// Set options or return error if type doesn't exist
		if( isset( $sliders[$type]['options'] ) )
			$options = $sliders[$type]['options'];
		else
			return 'error_type';
		
		// Set the options
		foreach( $options as $option ) {
			if( isset( $option['std'] ) )
				$default_options[$option['id']] = $option['std'];
			else
				$default_options[$option['id']] = null;	
		}
		
		// Return an error if options weren't found
		if( empty( $default_options ) )
			$default_options = 'error';
			
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
	    		if( isset( $slide_options['slide_type'] ) )
	    			$value = $slide_options['slide_type'];
				break;
			
	    	// Image position	
	    	case 'position' :
	    		if( isset( $slide_options['position'] ) )
	    			$value = $slide_options['position'];
	    		break;
	    	
	    	// Included elements	
	    	case 'include' :
	    		if( isset( $slide_options['elements']['include'] ) && in_array( $sub_type, $slide_options['elements']['include'] ) )
	    			$value = ' checked="checked"';
	    		break;
	    		
	    	// Image
	    	case 'image' :
	    		if( isset( $slide_options['image'] ) )
	    			$value = $slide_options['image'];
	    		break;
	    	
	    	// Video
	    	case 'video' :
	    		if( isset( $slide_options['video'] ) )
	    			$value = $slide_options['video'];
	    		break;
			
			// Image link
	    	case 'image_link' :
				if( $sub_type == 'target' ) {
					if( isset( $slide_options['elements']['image_link']['target'] ) )
	    				$value = $slide_options['elements']['image_link']['target'];
				} else if( $sub_type == 'url' ) {
					if( isset( $slide_options['elements']['image_link']['url'] ) )
	    				$value = $slide_options['elements']['image_link']['url'];
				}
				break;
	
			// Button	
			case 'button' :
				if( $sub_type == 'text' ) {
					if( isset( $slide_options['elements']['button']['text'] ) )
	    				$value = stripslashes( $slide_options['elements']['button']['text'] );
				} else if( $sub_type == 'target' ) {
					if( isset( $slide_options['elements']['button']['target']) )
	    				$value = $slide_options['elements']['button']['target'];
				} else if( $sub_type == 'url' ) {
					if( isset( $slide_options['elements']['button']['url'] ) )
	    				$value = $slide_options['elements']['button']['url'];
				}
				break;
			
			// Headline
	    	case 'headline' :
				if( isset( $slide_options['elements']['headline'] ) )
					$value = stripslashes( $slide_options['elements']['headline'] );
				break;
				
			// Description	
			case 'description' :
				if( isset( $slide_options['elements']['description'] ) )
					$value = stripslashes( $slide_options['elements']['description'] );
				break;
				
			// Custom Content	
			case 'custom' :
				if( isset( $slide_options['custom'] ) )
					$value = stripslashes( $slide_options['custom'] );
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
				'name' 		=> __( 'Slider Title', 'themeblvd_sliders' ),
				'type' 		=> 'title',
			),
			array(
				'name' 		=> __( 'Slider ID', 'themeblvd_sliders' ),
				'type' 		=> 'slug',
			),
			/* Hiding the true post ID from user to avoid confusion.
			array(
				'name' 		=> __( 'Slider ID', 'themeblvd_sliders' ),
				'type' 		=> 'id',
			),
			*/
			array(
				'name' 		=> __( 'Slider Type', 'themeblvd_sliders' ),
				'type' 		=> 'meta',
				'config' 	=> 'type' // Meta key to use to get value
			),
			array(
				'name' 		=> __( 'Shortcode Usage', 'themeblvd_sliders' ),
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
		foreach( $types as $type ) {
			$slider_types[$type['id']] = $type['name'];
		}
		
		// Setup options array to display form
		$options = array(
			array( 
				'name' 		=> __( 'Slider Name', 'themeblvd_sliders' ),
				'desc' 		=> __( 'Enter a user-friendly name for your slider.<br>Example: My Slider', 'themeblvd_sliders' ),
				'id' 		=> 'slider_name',
				'type' 		=> 'text'
			),
			array( 
				'name' 		=> __( 'Slider Type', 'themeblvd_sliders' ),
				'desc' 		=> __( 'Select which type of slider among the currently supported slider types.', 'themeblvd_sliders' ),
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
				<h3><?php _e( 'Add New Slider', 'themeblvd_sliders' ); ?></h3>
				<form id="add_new_slider">
					<div class="inner-group">
						<?php echo $form[0]; ?>
					</div><!-- .group (end) -->
					<div id="optionsframework-submit">
						<input type="submit" class="button-primary" name="update" value="<?php _e( 'Add New Slider', 'themeblvd_sliders' ); ?>">
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
		$slider_types = $this->get_sliders();
		?>
		<div id="<?php echo $slide_id; ?>" class="widget slide-options"<?php if( $visibility == 'hide' ) echo ' style="display:none"'; ?>>					
			<div class="widget-name">
				<a href="#" class="widget-name-arrow">Toggle</a>
				<h3><?php _e( 'Slide', 'themeblvd_sliders' ); ?> (<?php echo $slider_type; ?>)</h3>
			</div><!-- .element-name (end) -->
			<div class="widget-content">
				<div class="slide-set-type">
					<strong><?php _e( 'Image Slide', 'themeblvd_sliders' ); ?></strong>
					<select name="slides[<?php echo $slide_id; ?>][slide_type]">
						<?php
						$slide_type = $this->slide_value( $slide_options, 'slide_type' );
						foreach( $slider_types[$slider_type]['types'] as $key => $value ) {						
	        				echo '<option '.selected( $key, $slide_type, false ).' value="'.$key.'">'.$value['name'].'</option>';
	        			}
	        			?>
					</select>
				</div><!-- .slide-set-type (end) -->
				<div class="pad">
					<div class="slide-media controls grid-wrap">
						<div class="slide-set-media">
							<?php 
							foreach( $slider_types[$slider_type]['types'] as $type => $config ) {
								switch ( $type ) {
									case 'image' :
										?>
										<div class="slide-set-image">
											<h3><?php echo $config['main_title']; ?></h3>
											<div class="field section-upload">
												<?php
												$current_image = $this->slide_value( $slide_options, 'image' );
												echo optionsframework_medialibrary_uploader( 'slides['.$slide_id.']', 'slider', $slide_id.'image', $current_image, null, null, $slider_id, null, __( 'Get Image', 'themeblvd_sliders' ) );
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
												$current_video = $this->slide_value( $slide_options, 'video' );
												echo '<input type="text" name="slides['.$slide_id.'][video]" value="'.$current_video.'" />';
												?>
												<p class="explain"><?php _e( 'Enter in a video URL compatible with <a href="http://codex.wordpress.org/Embeds">WordPress\'s oEmbed</a>.<br><br>Ex: http://youtube.com/watch?v=HPPj6viIBmU<br>Ex: http://vimeo.com/11178250<br>Ex: http://wordpress.tv/2011/08/14/name-of-video', 'themeblvd_sliders' ); ?>
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
								<?php if( $slider_types[$slider_type]['positions'] ) : ?>
									<h4 class="header_has_icon"><?php _e( 'How would you like to display the media?', 'themeblvd_sliders' ); ?></h4>
									<?php $position = $this->slide_value( $slide_options, 'position' ); ?>
									<select class="slide-position slide-position-image" name="slides[<?php echo $slide_id; ?>][position_image]">
										<?php
										$sizes = $_wp_additional_image_sizes;
										foreach( $slider_types[$slider_type]['positions'] as $key => $value ) {
					        				// Set name for option
					        				$name = '';
					        				switch( $key ) {
						        				case 'full' :
						        					$name = __( 'Full Size', 'themeblvd_sliders' );
						        					break;
						        				case 'align-left' :
						        					$name = __( 'Aligned Left', 'themeblvd_sliders' );
						        					break;
						        				case 'align-right' :
						        					$name = __( 'Aligned Right', 'themeblvd_sliders' );
						        					break;	
					        				}
					        				// Set display dimensions for option
					        				$dimensions = '';
					        				if( isset( $sizes[$value] ) )
						        				$dimensions = ' <span class="dimensions">('.$sizes[$value]['width'].'x'.$sizes[$value]['height'].')</span>';
					        				echo '<option '.selected( $key, $position, false ).' value="'.$key.'">'.$name.$dimensions.'</option>';
					        			}
					        			?>
									</select>
									<select class="slide-position slide-position-video" name="slides[<?php echo $slide_id; ?>][position_video]">
										<?php
										foreach( $slider_types[$slider_type]['positions'] as $key => $value ) {
					        				// Set name for option
					        				$name = '';
					        				switch( $key ) {
						        				case 'full' :
						        					$name = __( 'Full Size', 'themeblvd_sliders' );
						        					break;
						        				case 'align-left' :
						        					$name = __( 'Aligned Left', 'themeblvd_sliders' );
						        					break;
						        				case 'align-right' :
						        					$name = __( 'Aligned Right', 'themeblvd_sliders' );
						        					break;	
					        				}
					        				echo '<option '.selected( $key, $position, false ).' value="'.$key.'">'.$name.'</option>';
					        			}
					        			?>
									</select>
									<p class="note image-note"><?php _e( 'When you upload an image, it must be at a minimum the size listed above in order for WordPress to generate and register the crop size. Images will be scaled down proportionally from their respective crop sizes depending on where the slider is placed.', 'themeblvd_sliders' ); ?></p>
								<?php endif; ?>
							</div><!-- .slide-section (end) -->
							<?php if( ! empty( $slider_types ) && ! empty( $slider_types[$slider_type]['elements'] ) ) : ?>
								<div class="slide-section">
									<h4><?php _e( 'Would you like to include additional elements?', 'themeblvd_sliders' ); ?></h4>
									<table class="widefat slide-elements">
										<tbody>
										<?php
										foreach( $slider_types[$slider_type]['elements'] as $element ) {
											switch( $element ) {
												
												case 'image_link' : 
													if( $key != 'video' ) {	// A video would never be wrapped in a link
														?>
														<tr class="element-image_link slide-element-header">
															<td class="slide-element-check"><input value="image_link" type="checkbox" name="slides[<?php echo $slide_id; ?>][elements][include][]"<?php echo $this->slide_value($slide_options, 'include', 'image_link'); ?> /></td>
															<td class="slide-element-name"><?php _e( 'Image Link', 'themeblvd_sliders' ); ?></td>
															<td class="slide-element-help"><a href="#" class="help-icon tooltip-link" title="<?php _e( 'This will allow you to apply a link to the image of this slide. You can configure it to open a webpage or a lightbox popup of different media types.', 'themeblvd_sliders' ); ?>">Help</a></td>
														</tr>
														<tr class="element-image_link slide-element-options">
															<td colspan="3">
																<div class="field">
																	<h5><?php _e( 'Where should the link open?', 'themeblvd_sliders' ); ?></h5>
																	<?php $target = $this->slide_value($slide_options, 'image_link', 'target'); ?>
																	<select name="slides[<?php echo $slide_id; ?>][elements][image_link][target]">
																		<option value="_self" <?php selected( $target, '_self' ); ?>><?php _e( 'Same Window', 'themeblvd_sliders' ); ?></option>
																		<option value="_blank" <?php selected( $target, '_blank' ); ?>><?php _e( 'New Window', 'themeblvd_sliders' ); ?></option>
																		<option value="lightbox" <?php selected( $target, 'lightbox' ); ?>><?php _e( 'Lightbox Popup', 'themeblvd_sliders' ); ?></option>
																	</select>
																</div><!-- .field (end) -->
																<div class="field">
																	<h5><?php _e( 'Where should the link go?', 'themeblvd_sliders' ); ?></h5>
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
														<td class="slide-element-name"><?php _e( 'Headline', 'themeblvd_sliders' ) ?></td>
														<td class="slide-element-help"><a href="#" class="help-icon tooltip-link" title="<?php _e( 'This will allow you to insert a simple headline on your slide. The location and style of this headline will vary depending on the design of the current theme.', 'themeblvd_sliders' ); ?>">Help</a></td>
													</tr>
													<tr class="element-headline slide-element-options">
														<td colspan="3">
															<div class="field">
																<h5><?php _e( 'What should the headline say?', 'themeblvd_sliders' ); ?></h5>
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
														<td class="slide-element-name"><?php _e( 'Description', 'themeblvd_sliders' ); ?></td>
														<td class="slide-element-help"><a href="#" class="help-icon tooltip-link" title="<?php _e( 'This will allow you to insert a simple description on your slide. The location and style of this description will vary depending on the design of the current theme.', 'themeblvd_sliders' ); ?>">Help</a></td>
													</tr>
													<tr class="element-description slide-element-options">
														<td colspan="3">
															<div class="field">
																<h5><?php _e( 'What should the description say?', 'themeblvd_sliders' ); ?></h5>
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
														<td class="slide-element-name"><?php _e( 'Button', 'themeblvd_sliders' ); ?></td>
														<td class="slide-element-help"><a href="#" class="help-icon tooltip-link" title="<?php _e( 'This will allow you to include a button on your slide. You can configure it to open a webpage or a lightbox popup of different media types.', 'themeblvd_sliders' ); ?>">Help</a></td>
													</tr>
													<tr class="element-button slide-element-options">
														<td colspan="3">
															<div class="field">
																<h5><?php _e( 'What should the button say?', 'themeblvd_sliders' ); ?></h5>
																<input name="slides[<?php echo $slide_id; ?>][elements][button][text]" type="text" value="<?php echo $this->slide_value($slide_options, 'button', 'text'); ?>" class="input" />
															</div><!-- .field (end) -->
															<div class="field">
																<h5><?php _e( 'Where should the link open?', 'themeblvd_sliders' ); ?></h5>
																<?php $target = $this->slide_value($slide_options, 'button', 'target'); ?>
																<select name="slides[<?php echo $slide_id; ?>][elements][button][target]">
																	<option value="_self" <?php selected( $target, '_self' ); ?>><?php _e( 'Same Window', 'themeblvd_sliders' ); ?></option>
																	<option value="_blank" <?php selected( $target, '_blank' ); ?>><?php _e( 'New Window', 'themeblvd_sliders' ); ?></option>
																	<option value="lightbox" <?php selected( $target, 'lightbox' ); ?>><?php _e( 'Lightbox Popup', 'themeblvd_sliders' ); ?></option>
																</select>
															</div><!-- .field (end) -->
															<div class="field">
																<h5><?php _e( 'Where should the link go?', 'themeblvd_sliders' ); ?></h5>
																<input name="slides[<?php echo $slide_id; ?>][elements][button][url]" type="text" value="<?php echo $this->slide_value($slide_options, 'button', 'url'); ?>" class="input" />
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
									<p class="warning slide-elements-warning"><?php _e( 'You cannot have any elements on top of full-size video. If you\'d like to include elements, align the video to the right or left.', 'themeblvd_sliders' ); ?></p>
								</div><!-- .slide-section (end) -->
							<?php endif; ?>
						</div><!-- .slide-include-elements (end) -->
						<div class="clear"></div>
					</div><!-- .grid-wrap (end) -->
					<?php if( array_key_exists( 'custom', $slider_types[$slider_type]['types'] ) ) : ?>
					<div class="controls slide-custom">
						<h3><?php echo $slider_types[$slider_type]['types']['custom']['main_title']; ?></h3>
						<?php $custom = $this->slide_value( $slide_options, 'custom' ); ?>
						<textarea name="slides[<?php echo $slide_id; ?>][custom]"><?php echo $custom; ?></textarea>
					</div><!-- .slide-custom (end) -->
					<?php endif; ?>
				</div><!-- .pad (end) -->
				<div class="submitbox widget-footer">
					<a href="#<?php echo $slide_id; ?>" class="submitdelete delete-me" title="<?php _e( 'Are you sure you want to delete this slide?', 'themeblvd_sliders' ); ?>"><?php _e( 'Delete Slide', 'themeblvd_sliders' ); ?></a>
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
	 * @param $types array all default slider info
	 */
	public function edit_slider( $id, $types ) {
		
		// Get slider custom post
		$slider = get_post($id);
		$post_id = $slider->ID;
		
		if( $slider ) {
			$current_slides = get_post_meta( $post_id, 'slides', true );
			$type = get_post_meta( $post_id, 'type', true );
			$options = $types[$type]['options'];
			$settings = get_post_meta( $post_id, 'settings', true );
			?>
			<input type="hidden" name="slider_id" value="<?php echo $post_id; ?>" />
			<div id="poststuff" class="metabox-holder full-width has-right-sidebar">
				<div class="inner-sidebar">
					<div class="postbox postbox-publish">
						<h3 class="hndle"><?php _e( 'Publish', 'themeblvd_sliders' ); ?> <?php echo stripslashes($slider->post_title); ?></h3>
						<div class="submitbox">
							<div id="major-publishing-actions">
								<div id="delete-action">
									<a class="submitdelete delete_slider" href="#<?php echo $post_id; ?>"><?php _e( 'Delete', 'themeblvd_sliders' ); ?></a>
								</div>
								<div id="publishing-action">
									<input class="button-primary" value="<?php _e( 'Update Slider', 'themeblvd_sliders' ); ?>" type="submit" />
									<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" />
								</div>
								<div class="clear"></div>
							</div>
						</div><!-- .submitbox (end) -->
					</div><!-- .post-box (end) -->
					<div class="postbox postbox-slider-info">
						<h3 class="hndle"><?php _e('Slider Information', 'themeblvd_sliders' ); ?></h3>
						<?php
						// Current settings
						$info_settings = array(
							'post_title' 	=> $slider->post_title,
							'post_name'		=> $slider->post_name
						);
						
						// Setup attribute options
						$info_options = array( 
							array( 
								'name'		=> __('Slider Name', 'themeblvd_sliders' ),
								'id' 		=> 'post_title',
								'desc'		=> __('This title is just for you. It\'ll never be used outside of your WordPress admin panel.', 'themeblvd_sliders'),
								'type' 		=> 'text'
							),
							array( 
								'name' 		=> __('Slider ID', 'themeblvd_sliders' ),
								'id' 		=> 'post_name',
								'desc'		=> __( 'Sliders are assigned based on this ID. So if you change this at any point, make sure to also update any builder elements, pages, or other options in which you\'ve assigned this specific slider.', 'themeblvd_sliders' ),
								'type' 		=> 'text'
							)
						);
		
						// Display form element
						$form = themeblvd_option_fields( 'info', $info_options, $info_settings, false );
						echo $form[0]; 
						?>
					</div><!-- .post-box (end) -->
					<?php if( $options ) : ?>
						<div class="postbox postbox-options">
							<h3 class="hndle"><?php echo $types[$type]['name'].' '.__( 'Options', 'themeblvd_sliders' ); ?></h3>
							<?php 
							// Slider Options
							$form = themeblvd_option_fields( 'options', $options, $settings, false );
							echo $form[0];
							?>
						</div><!-- .post-box (end) -->
					<?php endif; ?>
				</div><!-- .inner-sidebar (end) -->
				<div id="post-body">
					<div id="post-body-content">
						<div id="titlediv">
							<div class="ajax-overlay"></div>
							<h2><?php _e( 'Manage Slides', 'themeblvd_sliders' ); ?></h2>
							<a href="#<?php echo $post_id; ?>=><?php echo $type; ?>" id="add_new_slide" class="button-secondary"><?php _e( 'Add New Slide', 'themeblvd_sliders' ); ?></a>
							<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading">
							<div class="clear"></div>
						</div><!-- #titlediv (end) -->
						<div id="sortable">
							<?php
							if( ! empty( $current_slides ) ) {
								foreach( $current_slides as $slide_id => $slide ) {
									$this->edit_slide( $post_id, $type, $slide_id, $slide );
								}
							} else {
								echo '<p class="warning no-item-yet">'.__( 'You haven\'t added any slides yet. Get started by clicking "Add New Slide" above.', 'themeblvd_sliders' ).'</p>';
							}
	    					?>
						</div><!-- .sortable-slides (end) -->
					</div><!-- #post-body-content (end) -->
				</div><!-- #post-body (end) -->
			</div><!-- .metabox-holder (end) -->
			<?php
		} else {
			echo '<p>'.__( 'Error: The slider you\'re trying to edit doesn\'t exist.', 'themeblvd_sliders' ).'</p>';
		}
	}
	
	/**
	 * Get recognized sliders.
	 *
	 * Returns an array of all recognized sliders.
	 * Sliders included with a particular theme can 
	 * be edited by adding a filter through this array.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	function get_sliders() {
		
		global $_themeblvd_user_sliders;
		
		/**
		 * For each slider type, there are then types of 
		 * individual slides it supports.
		 */
		
		$standard_types = array(
			'image' => array(
				'name' => __( 'Image Slide', 'themeblvd' ),
				'main_title' => __( 'Setup Image', 'themeblvd' )
			),
			'video' => array(
				'name' => __( 'Video Slide', 'themeblvd' ),
				'main_title' => __( 'Video Link', 'themeblvd' )
			),
			'custom' => array(
				'name' => __( 'Custom Slide', 'themeblvd' ),
				'main_title' => __( 'Setup Custom Content', 'themeblvd' )
			)
		);
		$carrousel_types = array(
			'image' => array(
				'name' => __( 'Image Slide', 'themeblvd' ),
				'main_title' => __( 'Setup Image', 'themeblvd' )
			)
		);
		
		/**
		 * For each slider type, there are positions its media
		 * can be placed.
		 */
		
		$standard_positions = array(
			'full' 			=> 'slider-large',
			'align-left' 	=> 'slider-staged',
			'align-right' 	=> 'slider-staged'
		);
		$carrousel_positions = array(
			'full' 			=> 'grid_4'
		);
		
		/**
		 * For each slider type, these are the different elements
		 * the user can choose to include in a slide.
		 */
		
		$standard_elements = array( 'image_link', 'headline', 'description', 'button', 'custom_content' );
		$carrousel_elements = array( 'image_link' );
		
		/**
		 * For each slider type, these are settings.
		 * ALL options must have a default value 'std'.
		 */
		
		$standard_options = array(
			array(
				'id'		=> 'fx',
				'name'		=> __( 'How to transition between slides?', 'themeblvd' ),
				'std'		=> 'fade',
				'type'		=> 'select',
				'options'		=> array(
		            'fade' 	=> 'Fade',
					'slide'	=> 'Slide'
				)
			),
			array(
				'id'		=> 'timeout',
				'name' 		=> __( 'Seconds between each transition?', 'themeblvd' ),
				'std'		=> '5',
				'type'		=> 'text'
		    ),
			array(
				'id'		=> 'nav_standard',
				'name'		=> __( 'Show standard slideshow navigation?', 'themeblvd' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'		=> array(
		            '1'	=> __( 'Yes, show navigation.', 'themeblvd' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd' )
				)
			),
			array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev arrows?', 'themeblvd' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'		=> array(
		            '1'	=> __( 'Yes, show arrows.', 'themeblvd' ),
		            '0'	=> __( 'No, don\'t show them.', 'themeblvd' )
				)
			),
			array(
				'id'		=> 'pause_play',
				'name'		=> __( 'Show pause/play button?', 'themeblvd' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'		=> array(
		            '1'	=> __( 'Yes, show pause/play button.', 'themeblvd' ),
		            '0'	=> __( 'No, don\'t show it.', 'themeblvd' )
				)
			),
			array(
				'id'		=> 'pause_on_hover',
				'name'		=> __( 'Enable pause on hover?', 'themeblvd' ),
				'std'		=> 'pause_on',
				'type'		=> 'select',
				'options'		=> array(
		            'pause_on'		=> __( 'Pause on hover only.', 'themeblvd' ),
		            'pause_on_off'	=> __( 'Pause on hover and resume when hovering off.', 'themeblvd' ),
		            'disable'		=> __( 'No, disable this all together.', 'themeblvd' )
				)
			),
			array(
				'id'		=> 'mobile_fallback',
				'name'		=> __( 'How to display on mobile devices?', 'themeblvd' ),
				'std'		=> 'full_list',
				'type'		=> 'radio',
				'options'		=> array(
		            'full_list'		=> __( 'List out slides for a more user-friendly mobile experience.', 'themeblvd' ),
		            'first_slide'	=> __( 'Show first slide only for a more simple mobile experience.', 'themeblvd' ),
		            'display'		=> __( 'Attempt to show full animated slider on mobile devices.', 'themeblvd' )
				)
			)
		);
		$carrousel_options = array(
			array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev arrows?', 'themeblvd' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'		=> array(
		            '1'	=> __( 'Yes, show arrows.', 'themeblvd' ),
		            '0'	=> __( 'No, don\'t show them.', 'themeblvd' )
				)
			),
			array(
				'id'		=> 'mobile_fallback',
				'name'		=> __( 'How to display on mobile devices?', 'themeblvd' ),
				'std'		=> 'full_list',
				'type'		=> 'radio',
				'options'		=> array(
		            'full_list'		=> __( 'List out slides for a more user-friendly mobile experience.', 'themeblvd' ),
		            'first_slide'	=> __( 'Show first slide only for a more simple mobile experience.', 'themeblvd' ),
		            'display'		=> __( 'Attempt to show full animated slider on mobile devices.', 'themeblvd' )
				)
			)
		);
		
		// Final array (which is filterable from outside)
		$sliders = array(
			'standard' => array(
				'name' 		=> 'Standard',
				'id'		=> 'standard',
				'types'		=> $standard_types,
				'positions'	=> $standard_positions,
				'elements'	=> $standard_elements,
				'options'	=> $standard_options
			),
			'carrousel' => array(
				'name' 		=> 'Carrousel 3D',
				'id'		=> 'carrousel',
				'types'		=> $carrousel_types,
				'positions'	=> $carrousel_positions,
				'elements'	=> $carrousel_elements,
				'options'	=> $carrousel_options
			)
		);
		// Add in user-created sliders from API
		$sliders = array_merge( $sliders, $_themeblvd_user_sliders );
		
		// Return filtered
		return apply_filters( 'themeblvd_recognized_sliders', $sliders );
	}
}