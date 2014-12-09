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
		if ( $options == 'error_type' ) {

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
			$api = Theme_Blvd_Sliders_API::get_instance();
			$types = $api->get_sliders();
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
		$api = Theme_Blvd_Sliders_API::get_instance();
		$slider = $api->get_sliders( $slider_type );
		$targets = array( '_self', '_blank', 'lighbox', 'lightbox_video' );
		$options = array();
		$slides = array();

		// Slides
		if ( isset( $data['slides'] ) ) {

			$slides = $data['slides'];

			// Sanitize slides
			if ( ! empty( $slides ) ) {
				foreach ( $slides as $key => $slide ) {

					// Slide type
					if ( ! array_key_exists( $slide['slide_type'], $slider['types'] ) ) {
						unset( $slides[$key] );
						continue;
					}

					// Attach ID of slide
					$slides[$key]['slide_id'] = $key;

					// Image attributes - The idea is to do all work here for getting
					// whatever attributes we need in displaying the image so we don't
					// need to query the DB on the frontend display at all.
					if ( isset( $slide['image']['id'] ) && $slide['slide_type'] == 'image' ) {

						global $_wp_additional_image_sizes;
						global $content_width;

						// We don't want the admin content_width screwing up how we're
						// retrieving these images that'll end up being used for the
						// frontend display.
						$content_width = 0;

						// Image ID/URL for slider manager
						$attachment_id = wp_kses( $slide['image']['id'], array() );
						$raw_image = wp_get_attachment_image_src( $attachment_id, 'full' );

						// Expand the saved image data
						$slides[$key]['image'] = array(
							'id'			=> $attachment_id,		// Attachment ID of image
							'url'			=> $raw_image[0],		// Raw image URL, uncropped
							'title'			=> '',					// Attachment title
							'mime_type'		=> '',					// Post mime type, i.e. image/jpeg, image/png, etc
							'display'		=> '',					// Cropped Image URL for slider display
							'width'			=> '',					// Width of cropped image
							'height'		=> '',					// Height of cropped image
							'size'			=> 'full',				// Name of crop size, 'full' if not registered or selected by user
							'crop'			=> 0,					// Crop mode, true for hard or false for soft
							'cropped'		=> 0,					// Whether the cropped image actually exists, or WP has returned original
							'thumb'			=> '',					// URL of thumbnail
							'thumb_width'	=> 0,					// Width of thumbnail
							'thumb_height'	=> 0					// Height of thumbnail
						);

						// Items from attachment post object - title and mime type
						$attachment = get_post( $attachment_id );
						$slides[$key]['image']['title'] = $attachment->post_title;
						$slides[$key]['image']['mime_type'] = $attachment->post_mime_type;

						// Store and verify correct crop name
						if ( $slide['position_image'] == 'full' && $slider['custom_size'] === true ) {
							if ( isset( $slide['image_size'] ) && $slide['image_size'] != 'full' ) {
								if ( in_array( $slide['image_size'], get_intermediate_image_sizes() ) ) {
									$slides[$key]['image']['size'] = $slide['image_size']; // $image_size var used below
								}
							}
						} else {
							$slides[$key]['image']['size'] = $slider['positions'][$slide['position_image']];
						}

						// Store crop mode, true for hard crop
						if ( $slides[$key]['image']['size'] != 'full' ) {

							$image_size = $slides[$key]['image']['size'];

							if ( isset( $_wp_additional_image_sizes[$image_size] ) ) {
								$slides[$key]['image']['crop'] = $_wp_additional_image_sizes[$image_size]['crop'];
							} else if ( in_array($image_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
								$slides[$key]['image']['crop'] = get_option( "{$image_size}_crop" );
							}

						}

						// Image attributes - display URL, width and height
						$display = wp_get_attachment_image_src( $attachment_id, $slides[$key]['image']['size'] );
						$slides[$key]['image']['display'] = $display[0];
						$slides[$key]['image']['width'] = $display[1];
						$slides[$key]['image']['height'] = $display[2];

						// Verify whether we retrieved an actual cropped image from the
						// selected image size. Will remain false if WP just returned the
						// same URL as the raw, uncropped image.
						if ( $raw_image[0] != $display[0] ) {
							$slides[$key]['image']['cropped'] = 1;
						}

						// Thumbnail -- Potentially used for a thumbnail navigation setup
						if ( version_compare(TB_FRAMEWORK_VERSION, '2.5.0', '>=') ) {
							$thumb_size = 'tb_thumb';
						} else {
							$thumb_size = 'square_smallest';
						}
						$thumb = wp_get_attachment_image_src( $attachment_id, apply_filters( 'themeblvd_sliders_thumb_nav_size', $thumb_size ) );
						$slides[$key]['image']['thumb'] = $thumb[0];
						$slides[$key]['image']['thumb_width'] = $thumb[1];
						$slides[$key]['image']['thumb_height'] = $thumb[2];

					}

					// Media position
					if ( $slider['positions'] && $slide['slide_type'] != 'custom' ) {
						$media = $slide['slide_type'];
						$slides[$key]['position'] = $slide['position_'.$media]; // Which select we pull from depends on teh type of slide, image or video
						if ( ! array_key_exists( $slide['position_'.$media], $slider['positions'] ) ) {
							unset( $slides[$key] );
							continue;
						}
					}

					// Custom Content
					if ( isset( $slide['custom'] ) ) {
						$slides[$key]['custom'] = apply_filters( 'themeblvd_sanitize_textarea', $slide['custom'] );
					}

					// Elements
					if ( isset( $slide['elements'] ) ) {
						foreach ( $slide['elements'] as $element_key => $element ) {

							// Check if element should even exist
							if ( ! in_array( $element_key, $slider['elements'] ) ) {
								unset( $slides[$element_key] );
								continue;
							}

							// Now sanitize the inner options of each element
							switch ( $element_key ) {

								case 'image_link' :
									if ( ! in_array( $element['target'], $targets  ) ) {
										$element['target'] = '_self';
									}
									$slides[$key]['elements'][$element_key]['url'] = apply_filters( 'themeblvd_sanitize_text', $element['url'] );
									break;

								case 'headline' :
									$slides[$key]['elements'][$element_key] = apply_filters( 'themeblvd_sanitize_textarea', $element );
									break;

								case 'description' :
									$slides[$key]['elements'][$element_key] = apply_filters( 'themeblvd_sanitize_textarea', $element );
									break;

								case 'button' :
									if ( ! in_array( $element['target'], $targets  ) ) {
										$element['target'] = '_self';
									}
									$slides[$key]['elements'][$element_key]['url'] = apply_filters( 'themeblvd_sanitize_text', $element['url'] );
									$slides[$key]['elements'][$element_key]['text'] = apply_filters( 'themeblvd_sanitize_text', $element['text'] );
									break;
							}
						}
					}

					// Video Max Height
					if ( isset( $slide['video_height'] ) ) {
						$slides[$key]['video_height'] = intval($slide['video_height']);
					}

					// Remove elements that aren't needed
					unset( $slides[$key]['position_image'], $slides[$key]['position_video'] ); // Both replaced by single "position" key
					unset( $slides[$key]['image_size'] ); // Image size stored in $slide['image']

					if ( $slide['slide_type'] != 'custom' ) {
						unset( $slides[$key]['custom'] );
					}

					if ( $slide['slide_type'] != 'image' ) {
						unset( $slides[$key]['image'] );
					}

					if ( $slide['slide_type'] != 'video' ) {
						unset( $slides[$key]['video'], $slides[$key]['video_height'] );
					}

					if ( $slide['slide_type'] == 'custom' ) {
						unset( $slides[$key]['elements'], $slides[$key]['position'] );
					}
				}
			}
		}

		// Options
		if ( isset( $data['options'] ) ) {

			// Sanitize options
			$clean = array();
			foreach ( $slider['options'] as $option ) {

				if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
					continue;
				}

				$option_id = $option['id'];

				// Set checkbox to false if it wasn't sent in the $_POST
				if ( 'checkbox' == $option['type'] ) {
					if ( isset( $element['options'][$option_id] ) ) {
						$data['options'][$option_id] = '1';
					} else {
						$data['options'][$option_id] = '0';
					}
				}

				// Set each item in the multicheck to false if it wasn't sent in the $_POST
				if ( 'multicheck' == $option['type'] ) {
					if ( isset( $data['options'][$option_id] ) ) {
						foreach ( $option['options'] as $key => $value ) {
							if ( isset($value) ) {
								$data['options'][$option_id][$key] = '1';
							}
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
		update_post_meta( $slider_id, 'slides', apply_filters('themeblvd_slider_save_slides', $slides, $slider_id, $slider_type, $slider) );
		update_post_meta( $slider_id, 'settings', apply_filters('themeblvd_slider_save_settings', $settings, $slider_id, $slider_type, $slider) );

		// Slider Information
		if ( isset( $data['info'] ) ) {

			// Start post data to be updated with the ID
			$post_atts = array(
				'ID' => $slider_id
			);

			// Post Title (only used in admin for reference)
			if ( isset( $data['info']['post_title'] ) ) {
				$post_atts['post_title'] = $data['info']['post_title'];
			}

			// Post Slug (used as custom layout ID, important! )
			if ( isset( $data['info']['post_name'] ) ) {
				$post_atts['post_name'] = $data['info']['post_name'];
			}

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
		// echo 'DEBUG: <pre>'; print_r($debug); echo '</pre>';
		echo '<div id="setting-error-save_options" class="updated fade settings-error ajax-update">';
		echo '	<p><strong>'.__( 'Slider saved.', 'theme-blvd-sliders' ).'</strong></p>';
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
		if ( isset( $data['posts'] ) ) {

			// Delete slider posts
			foreach ( $data['posts'] as $id ) {

				// Can still be recovered from trash
				// if post type's admin UI is turned on.
				wp_delete_post( $id );

			}

			// Send back number of sliders
			$posts = get_posts( array( 'post_type' => 'tb_slider', 'numberposts' => -1 ) );
			echo sprintf( _n( '1 Slider', '%s Sliders', count($posts) ), number_format_i18n( count($posts) ) ).'[(=>)]';

			// Display update message
			echo '<div id="setting-error-delete_slider" class="updated fade settings-error ajax-update">';
			echo '	<p><strong>'.__( 'Slider(s) deleted.', 'theme-blvd-sliders' ).'</strong></p>';
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

		$api = Theme_Blvd_Sliders_API::get_instance();
		$types = $api->get_sliders();

		echo $slider_id.'[(=>)]';
		$this->admin_page->edit_slider( $_POST['data'] );
		die();
	}

}