<?php
/**
 * Slider Manager Ajax
 */
class Theme_Blvd_Sliders_Ajax {
	
	public $admin_page;
	
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param $admin_page Object from Theme_Blvd_Sliders_Admin class
	 */
	public function __construct( $admin_page ) {
		
		// Add general slider admin functions for use within Ajax
		$this->admin_page = $admin_page;
		
		// Hook in Ajax funcition to WP
		add_action( 'wp_ajax_themeblvd_add_slider', array( $this, 'add_slider' ) );
		add_action( 'wp_ajax_themeblvd_save_slider', array( $this, 'save_slider' ) );
		add_action( 'wp_ajax_themeblvd_update_slider_table', array( $this, 'update_table' )  );
		add_action( 'wp_ajax_themeblvd_add_slide', array( $this, 'add_slide' ) );
		add_action( 'wp_ajax_themeblvd_delete_slider', array( $this, 'delete_slider' ) );
		add_action( 'wp_ajax_themeblvd_edit_slider', array( $this, 'edit_slider' ) );
	
	}
	
	/**
	 * Add new slider
	 *
	 * @since 2.0.0 
	 */
	public function add_slider() {
		
		// Make sure Satan isn't lurking
		check_ajax_referer( 'optionsframework_new_slider', 'security' );
		
		// Handle form data
		parse_str( $_POST['data'], $config );
		
		// Gather default options for slider type
		$options = $this->admin_page->slider_defaults( $config['options']['slider_type'] );
			
		// Add in new slider
		if( $options == 'error_type' ) {
			
			// Slider type doesn't exist
			echo $options;
	
		} else {
	
			$args = array(	
				'post_type'			=> 'tb_slider', 
				'post_title'		=> $config['options']['slider_name'],
				'post_status' 		=> 'publish',
				'comment_status'	=> 'closed', 
				'ping_status'		=> 'closed'
			);
			
			$post_id = wp_insert_post( $args );
			
			// Set the initial post meta
			update_post_meta( $post_id, 'type', $config['options']['slider_type'] );
			update_post_meta( $post_id, 'settings', $options );
			
			// Respond with edit page for the new slider and ID
			$types = $this->admin_page->get_sliders();
			echo $post_id.'[(=>)]';
			$this->admin_page->edit_slider( $post_id, $types );
			
		}
		
		die();
	}
	
	/**
	 * Save slider 
	 *
	 * @since 1.0.0
	 */
	public function save_slider() {
		
		// Make sure Satan isn't lurking
		check_ajax_referer( 'optionsframework_save_slider', 'security' );
		
		// Handle form data
		parse_str( $_POST['data'], $data );
	
		//  Slider ID
		$slider_id = $data['slider_id'];
			
		// Start it
		$slider_type = get_post_meta( $slider_id, 'type', true );
		$tb_sliders = $this->admin_page->get_sliders();
		$slider = $tb_sliders[$slider_type];
		$targets = array( '_self', '_blank', 'lighbox' );
		$options = array();
		$slides = array();
		
		// Slides
		if( isset( $data['slides'] ) ) {
			
			$slides = $data['slides'];
			
			// Sanitize slides
			if( ! empty( $slides ) ) {
				foreach( $slides as $key => $slide ) {
					
					// Slide type
					if( ! array_key_exists( $slide['slide_type'], $slider['types'] ) ) {
						unset( $slides[$key] );
						continue;
					}
					
					// Image ID/URL for slider manager
					$no_tags = array();
					if( isset( $slides[$key]['image']['id'] ) ) {
						$slides[$key]['image']['id'] = wp_kses( $slide['image']['id'], $no_tags );
						$preview = wp_get_attachment_image_src( $slide['image']['id'], 'full' );
						$slides[$key]['image']['url'] = $preview[0];
					}	
					
					// Image URL's ready to be be used in actual slider
					if( isset( $slides[$key]['image']['id'] ) ) {
						$sizes = themeblvd_get_image_sizes();
						foreach( $sizes as $id => $atts ) {
							$image = wp_get_attachment_image_src( $slides[$key]['image']['id'], $id );
							$slides[$key]['image'][$id] = $image[0];
						}
					}	
					
					// Media position
					if( $slider['positions'] ) {
						$media = $slide['slide_type'];
						$slides[$key]['position'] = $slide['position_'.$media]; // Which select we pull from depends on teh type of slide, image or video
						if( ! array_key_exists( $slide['position_'.$media], $slider['positions'] ) ) {
							unset( $slides[$key] );
							continue;
						}
					}
					
					// Custom Content
					if( isset( $slides[$key]['custom'] ) )
						$slides[$key]['custom'] = apply_filters( 'themeblvd_sanitize_textarea', $slide['custom'] );
					
					// Elements
					if( isset( $slide['elements'] ) ) {
						foreach( $slide['elements'] as $element_key => $element ) {
							
							// Check if element should even exist
							if( ! in_array( $element_key, $slider['elements'] ) ) {
								unset( $slides[$element_key] );
								continue;
							}
							
							// Now sanitize the inner options of each element
							switch( $element_key ) {
								case 'image_link' :
									if( ! in_array( $element['target'], $targets  ) ) $element['target'] = '_self';
									$slides[$key]['elements'][$element_key]['url'] = apply_filters( 'themeblvd_sanitize_text', $element['url'] );
									break;
									
								case 'headline' :
									$slides[$key]['elements'][$element_key] = apply_filters( 'themeblvd_sanitize_textarea', $element );
									break;
									
								case 'description' :
									$slides[$key]['elements'][$element_key] = apply_filters( 'themeblvd_sanitize_textarea', $element );
									break;
									
								case 'button' :
									if( ! in_array( $element['target'], $targets  ) ) $element['target'] = '_self';
									$slides[$key]['elements'][$element_key]['url'] = apply_filters( 'themeblvd_sanitize_text', $element['url'] );
									$slides[$key]['elements'][$element_key]['text'] = apply_filters( 'themeblvd_sanitize_text', $element['text'] );
									break;
							}
						}
					}
					
					// Remove elements that aren't needed
					if( $slide['slide_type'] != 'custom' )
						unset( $slides[$key]['custom'] );
					if( $slide['slide_type'] != 'image' )
						unset( $slides[$key]['image'] );
					if( $slide['slide_type'] != 'video' )
						unset( $slides[$key]['video'] );
					if( $slide['slide_type'] == 'custom' ) {
						unset( $slides[$key]['elements'] );
						unset( $slides[$key]['position'] );
					}
	
				}
			}
		}
		
		// Options
		if( isset( $data['options'] ) ) {
			
			// Sanitize options
			$clean = array();
			foreach( $slider['options'] as $option ) {
				
				if ( ! isset( $option['id'] ) )
					continue;
	
				if ( ! isset( $option['type'] ) )
					continue;
				
				$option_id = $option['id'];
					
				// Set checkbox to false if it wasn't sent in the $_POST
				if ( 'checkbox' == $option['type'] ) {
					if( isset( $element['options'][$option_id] ) )
						$data['options'][$option_id] = '1';
					else
						$data['options'][$option_id] = '0';
				}
	
				// Set each item in the multicheck to false if it wasn't sent in the $_POST
				if ( 'multicheck' == $option['type'] ) {
					if( isset( $data['options'][$option_id] ) ) {
						foreach ( $option['options'] as $key => $value ) {
							if( isset($value) )
								$data['options'][$option_id][$key] = '1';
						}
					}
				}
				
				// For a value to be submitted to database it must pass through a sanitization filter
				if ( has_filter( 'themeblvd_sanitize_' . $option['type'] ) ) {
					$clean[$option_id] = apply_filters( 'themeblvd_sanitize_' . $option['type'], $data['options'][$option_id], $option );
				}
				
			}
			$settings = $clean;
		}
		
		// Update even they're empty
		update_post_meta( $slider_id, 'slides', $slides );
		update_post_meta( $slider_id, 'settings', $settings );
		
		// Slider Information
		if( isset( $data['info'] ) ) {
			
			// Start post data to be updated with the ID
			$post_atts = array(
				'ID' => $slider_id
			);
			
			// Post Title (only used in admin for reference)
			if( isset( $data['info']['post_title'] ) )
				$post_atts['post_title'] = $data['info']['post_title'];
			
			// Post Slug (used as custom layout ID, important! )
			if( isset( $data['info']['post_name'] ) )
				$post_atts['post_name'] = $data['info']['post_name'];
			
			// Update Post info
			wp_update_post( $post_atts );
		
		}
		
		// Allow plugins to hook in
		do_action( 'themeblvd_save_slider_'.$slider_type, $slider_id, $slides, $settings );
		
		// Get most recent slider id after doing the above processes
		$updated_slider = get_post($slider_id);
		$current_slider_id = $updated_slider->post_name;
		
		// Send current slider ID back with response
		echo $current_slider_id.'[(=>)]';
		
		// Display update message
		echo '<div id="setting-error-save_options" class="updated fade settings-error ajax-update">';
		echo '	<p><strong>'.__( 'Slider saved.', 'themeblvd_sliders' ).'</strong></p>';
		echo '</div>';
		die();
	}
	
	/**
	 * Add new slide
	 *
	 * @since 1.0.0 
	 */
	
	public function add_slide() {	
		$atts = explode( '=>', $_POST['data'] );
		$slide_id = uniqid( 'slide_'.rand() );
		$this->admin_page->edit_slide( $atts[0], $atts[1], $slide_id, null, 'hide' );
		die();
	}
	
	/**
	 * Update slider manager table 
	 *
	 * @since 1.0.0
	 */
	public function update_table() {
		$this->admin_page->manage_sliders();
		die();
	}
	
	/**
	 * Delete slider 
	 *
	 * @since 1.0.0
	 */
	public function delete_slider() {
		
		// Make sure Satan isn't lurking
		check_ajax_referer( 'optionsframework_manage_sliders', 'security' );
		
		// Handle data
		parse_str( $_POST['data'], $data );
	
		// Only run if user selected some sliders to delete
		if( isset( $data['posts'] ) ) {
	
			// Delete slider posts
			foreach( $data['posts'] as $id ) {
				
				// Can still be recovered from trash 
				// if post type's admin UI is turned on.
				wp_delete_post( $id );
			
			}
			
			// Send back number of sliders
			$posts = get_posts( array( 'post_type' => 'tb_slider', 'numberposts' => -1 ) );
			echo sprintf( _n( '1 Slider', '%s Sliders', count($posts) ), number_format_i18n( count($posts) ) ).'[(=>)]';
			
			// Display update message
			echo '<div id="setting-error-delete_slider" class="updated fade settings-error ajax-update">';
			echo '	<p><strong>'.__( 'Slider(s) deleted.', 'themeblvd_sliders' ).'</strong></p>';
			echo '</div>';
		
		}
		
		die();
	}
	
	/**
	 * Edit a slider 
	 *
	 * @since 1.0.0
	 */
	public function edit_slider() {
		$slider_id = $_POST['data'];
		$types = $this->admin_page->get_sliders();
		echo $slider_id.'[(=>)]';
		$this->admin_page->edit_slider( $_POST['data'], $types );
		die();
	}
	
}