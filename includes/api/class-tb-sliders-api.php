<?php
/**
 * Theme Blvd Sliders API
 *
 * This sets up the default slider types and provides
 * an API to add custom slider types.
 *
 * @author		Jason Bobich
 * @copyright	Copyright (c) Jason Bobich
 * @link		http://jasonbobich.com
 * @link		http://themeblvd.com
 * @package 	Theme Blvd WordPress Framework
 */
class Theme_Blvd_Sliders_API {

	/*--------------------------------------------*/
	/* Properties, private
	/*--------------------------------------------*/

	/**
	 * A single instance of this class.
	 *
	 * @since 1.1.0
	 */
	private static $instance = null;

	/**
	 * Core slider types.
	 *
	 * @since 1.1.0
	 */
	private $core_sliders = array();

	/**
	 * Slider types added through client API
	 * mutators.
	 *
	 * @since 1.1.0
	 */
	private $client_sliders = array();

	/**
	 * Slider types to be removed.
	 *
	 * @since 1.1.0
	 */
	private $remove_sliders = array();

	/**
	 * Final slider types, core combined
	 * with client API-added slider types.
	 *
	 * @since 1.1.0
	 */
	private $sliders = array();

	/*--------------------------------------------*/
	/* Constructor
	/*--------------------------------------------*/

	/**
     * Creates or returns an instance of this class.
     *
     * @since 1.1.0
     *
     * @return Theme_Blvd_Frontend_Init A single instance of this class.
     */
	public static function get_instance() {

		if ( self::$instance == null ) {
            self::$instance = new self;
        }

        return self::$instance;
	}

	/**
	 * Constructor. Hook everything in.
	 *
	 * @since 1.1.0
	 */
	private function __construct() {

		if ( is_admin() ) {

			// Setup plugin default slider types.
			$this->set_core_sliders();

			// Establish slider types based on client modifications
			// combined with plugin defaults.
			add_action( 'after_setup_theme', array( $this, 'set_sliders' ), 1000 );

		}

	}

	/*--------------------------------------------*/
	/* Methods, mutators
	/*--------------------------------------------*/

	/**
	 * Set plugin default slider types.
	 *
	 * @since 1.1.0
	 */
	public function set_core_sliders() {

		$this->core_sliders = array(
			'standard' => array(
				'name' 			=> 'Standard',
				'id'			=> 'standard',
				'custom_size' 	=> true 		// Custom size allowed for full size position
			),
			'nivo' => array(
				'name' 			=> 'Nivo',
				'id'			=> 'nivo',
				'custom_size' 	=> true 		// Custom size allowed for full size position
			),
			'carrousel' => array(
				'name' 			=> '3D Carousel',
				'id'			=> 'carrousel',
				'custom_size' 	=> false 		// Custom size not allowed for full size position
			),
			'bootstrap' => array(
				'name' 			=> 'Bootstrap Carousel',
				'id'			=> 'bootstrap',
				'custom_size' 	=> false 		// Custom size allowed for full size position
			)
		);

		/*--------------------------------------------*/
		/* Standard
		/*--------------------------------------------*/

		// Slide Types
		$this->core_sliders['standard']['types'] = array(
			'image' => array(
				'name'			=> __( 'Image Slide', 'theme-blvd-sliders' ),
				'main_title' 	=> __( 'Setup Image', 'theme-blvd-sliders' )
			),
			'video' => array(
				'name' 			=> __( 'Video Slide', 'theme-blvd-sliders' ),
				'main_title' 	=> __( 'Setup Video', 'theme-blvd-sliders' )
			),
			'custom' => array(
				'name' 			=> __( 'Custom Slide', 'theme-blvd-sliders' ),
				'main_title' 	=> __( 'Setup Custom Content', 'theme-blvd-sliders' )
			)
		);

		// Slide Media Positions
		$this->core_sliders['standard']['positions'] = array(
			'full' 			=> 'slider-large', // Default
			'align-left' 	=> 'slider-staged',
			'align-right' 	=> 'slider-staged'
		);

		// Slide Elements
		$this->core_sliders['standard']['elements'] = array(
			'image_link',
			'headline',
			'description',
			'button',
			'custom_content'
		);

		// Slider Options
		$this->core_sliders['standard']['options'] = array(
			array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
			array(
				'id'		=> 'fx',
				'name'		=> __( 'How to transition between slides?', 'theme-blvd-sliders' ),
				'std'		=> 'fade',
				'type'		=> 'select',
				'options'	=> array(
		            'fade' 	=> 'Fade',
					'slide'	=> 'Slide'
				),
				'class' 	=> 'trigger'
			),
			array(
				'id'		=> 'smoothheight',
				'name'		=> __( 'Allow height to adjust on each transition?', 'theme-blvd-sliders' ),
				'std'		=> 'false',
				'type'		=> 'select',
				'options'	=> array(
		            'true' 	=> 'Yes, enable smoothHeight.',
					'false'	=> 'No, display as height of tallest slide.'
				),
				'class'		=> 'hide receiver receiver-slide'
			),
			array(
		    	'type'		=> 'subgroup_end'
		    ),
			array(
				'id'		=> 'timeout',
				'name' 		=> __( 'Seconds between each transition?', 'theme-blvd-sliders' ),
				'std'		=> '5',
				'type'		=> 'text'
		    ),
			array(
				'id'		=> 'nav_standard',
				'name'		=> __( 'Show standard slideshow navigation?', 'theme-blvd-sliders' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show navigation.', 'theme-blvd-sliders' ),
		            '0'	=> __( 'No, don\'t show it.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev arrows?', 'theme-blvd-sliders' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show arrows.', 'theme-blvd-sliders' ),
		            '0'	=> __( 'No, don\'t show them.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'pause_play',
				'name'		=> __( 'Show pause/play button?', 'theme-blvd-sliders' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show pause/play button.', 'theme-blvd-sliders' ),
		            '0'	=> __( 'No, don\'t show it.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'pause_on_hover',
				'name'		=> __( 'Enable pause on hover?', 'theme-blvd-sliders' ),
				'std'		=> 'pause_on',
				'type'		=> 'select',
				'options'	=> array(
		            'pause_on'		=> __( 'Pause on hover only.', 'theme-blvd-sliders' ),
		            'pause_on_off'	=> __( 'Pause on hover and resume when hovering off.', 'theme-blvd-sliders' ),
		            'disable'		=> __( 'No, disable this all together.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'mobile_fallback',
				'name'		=> __( 'How to display on mobile devices?', 'theme-blvd-sliders' ),
				'std'		=> 'full_list',
				'type'		=> 'radio',
				'options'	=> array(
		            'full_list'		=> __( 'List out slides for a more user-friendly mobile experience.', 'theme-blvd-sliders' ),
		            'first_slide'	=> __( 'Show first slide only for a more simple mobile experience.', 'theme-blvd-sliders' ),
		            'display'		=> __( 'Attempt to show full animated slider on mobile devices.', 'theme-blvd-sliders' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Nivo
		/*--------------------------------------------*/

		// Slide Types
		$this->core_sliders['nivo']['types'] = array(
			'image' => array(
				'name' 			=> __( 'Image Slide', 'theme-blvd-sliders' ),
				'main_title'	=> __( 'Setup Image', 'theme-blvd-sliders' )
			)
		);

		// Slide Media Positions
		$this->core_sliders['nivo']['positions'] = array(
			'full' => 'slider-large' // Default
		);

		// Slide Elements
		$this->core_sliders['nivo']['elements'] = array(
			'image_link',
			'headline',
			'description'
		);

		// Slider Options
		$this->core_sliders['nivo']['options'] = array(
			array(
		    	'type'		=> 'subgroup_start',
		    	'class'		=> 'show-hide-toggle'
		    ),
		    array(
				'id'		=> 'fx',
				'name'		=> __( 'How to transition between slides?', 'theme-blvd-sliders' ),
				'std'		=> 'random',
				'type'		=> 'select',
				'options'	=> array(
		            'boxRandom'				=> 'boxRandom',
					'boxRain'				=> 'boxRain',
					'boxRainReverse'		=> 'boxRainReverse',
					'boxRainGrow'			=> 'boxRainGrow',
					'boxRainGrowReverse'	=> 'boxRainGrowReverse',
					'fold'					=> 'fold',
					'fade'					=> 'fade',
					'random'				=> 'random',
					'sliceDown'				=> 'sliceDown',
					'sliceDownLeft'			=> 'sliceDownLeft',
					'sliceUp'				=> 'sliceUp',
					'sliceUpLeft'			=> 'sliceUpLeft',
					'sliceUpDown'			=> 'sliceUpDown',
					'sliceUpDownLeft'		=> 'sliceUpDownLeft',
					'slideInRight'			=> 'slideInRight',
					'slideInLeft'			=> 'slideInLeft'
				),
				'class' 	=> 'trigger'
			),
		    array(
				'id'		=> 'boxcols',
				'name' 		=> __( 'Number of box columns for transition?', 'theme-blvd-sliders' ),
				'std'		=> '8',
				'type'		=> 'text',
				'class'		=> 'hide receiver receiver-boxRandom receiver-boxRain receiver-boxRainReverse receiver-boxRainGrow receiver-boxRainGrowReverse'
		    ),
		    array(
				'id'		=> 'boxrows',
				'name' 		=> __( 'Number of box rows for transition?', 'theme-blvd-sliders' ),
				'std'		=> '4',
				'type'		=> 'text',
				'class'		=> 'hide receiver receiver-boxRandom receiver-boxRain receiver-boxRainReverse receiver-boxRainGrow receiver-boxRainGrowReverse'
		    ),
			array(
				'id'		=> 'slices',
				'name' 		=> __( 'Number of slices for transition?', 'theme-blvd-sliders' ),
				'std'		=> '15',
				'type'		=> 'text',
				'class'		=> 'hide receiver receiver-sliceDown receiver-sliceDownLeft receiver-sliceUp receiver-sliceUpLeft receiver-sliceUpDown receiver-sliceUpDownLeft'
		    ),
		    array(
		    	'type'		=> 'subgroup_end'
		    ),
			array(
				'id'		=> 'timeout',
				'name' 		=> __( 'Seconds between each transition?', 'theme-blvd-sliders' ),
				'std'		=> '5',
				'type'		=> 'text'
		    ),
			array(
				'id'		=> 'nav_standard',
				'name'		=> __( 'Show standard slideshow navigation?', 'theme-blvd-sliders' ),
				'std'		=> 'true',
				'type'		=> 'select',
				'options'	=> array(
		            'true'	=> __( 'Yes, show navigation.', 'theme-blvd-sliders' ),
		            'false'	=> __( 'No, don\'t show it.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev arrows?', 'theme-blvd-sliders' ),
				'std'		=> 'true',
				'type'		=> 'select',
				'options'	=> array(
		            'true'	=> __( 'Yes, show arrows.', 'theme-blvd-sliders' ),
		            'false'	=> __( 'No, don\'t show them.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'pause_on_hover',
				'name'		=> __( 'Enable pause on hover?', 'theme-blvd-sliders' ),
				'std'		=> 'true',
				'type'		=> 'select',
				'options'	=> array(
		            'true'		=> __( 'Yes, pause slider on hover.', 'theme-blvd-sliders' ),
		            'false'		=> __( 'No, don\'t pause slider on hover.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'mobile_fallback',
				'name'		=> __( 'How to display on mobile devices?', 'theme-blvd-sliders' ),
				'std'		=> 'full_list',
				'type'		=> 'radio',
				'options'	=> array(
		            'full_list'		=> __( 'List out slides for a more user-friendly mobile experience.', 'theme-blvd-sliders' ),
		            'first_slide'	=> __( 'Show first slide only for a more simple mobile experience.', 'theme-blvd-sliders' ),
		            'display'		=> __( 'Attempt to show full animated slider on mobile devices.', 'theme-blvd-sliders' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Carrousel @depracated
		/*--------------------------------------------*/

		if ( version_compare( TB_FRAMEWORK_VERSION, '2.5.0', '<' ) ) { // (only for themes prior to framwork 2.5)

			// Slide Types
			$this->core_sliders['carrousel']['types'] = array(
				'image' => array(
					'name' 			=> __( 'Image Slide', 'theme-blvd-sliders' ),
					'main_title' 	=> __( 'Setup Image', 'theme-blvd-sliders' )
				)
			);

			// Slide Media Positions
			$this->core_sliders['carrousel']['positions'] = array(
				'full' => 'grid_4' // Default
			);

			// Slide Elements
			$this->core_sliders['carrousel']['elements'] = array(
				'image_link'
			);

			// Slider Options
			$this->core_sliders['carrousel']['options'] = array(
				array(
					'id'		=> 'nav_arrows',
					'name'		=> __( 'Show next/prev arrows?', 'theme-blvd-sliders' ),
					'std'		=> '1',
					'type'		=> 'select',
					'options'	=> array(
			            '1'	=> __( 'Yes, show arrows.', 'theme-blvd-sliders' ),
			            '0'	=> __( 'No, don\'t show them.', 'theme-blvd-sliders' )
					)
				),
				array(
					'id'		=> 'mobile_fallback',
					'name'		=> __( 'How to display on mobile devices?', 'theme-blvd-sliders' ),
					'std'		=> 'full_list',
					'type'		=> 'radio',
					'options'	=> array(
			            'full_list'		=> __( 'List out slides for a more user-friendly mobile experience.', 'theme-blvd-sliders' ),
			            'first_slide'	=> __( 'Show first slide only for a more simple mobile experience.', 'theme-blvd-sliders' ),
			            'display'		=> __( 'Attempt to show full animated slider on mobile devices.', 'theme-blvd-sliders' )
					)
				)
			);
		}

		/*--------------------------------------------*/
		/* Bootstrap Carousel
		/*--------------------------------------------*/

		// Slide Types
		$this->core_sliders['bootstrap']['types'] = array(
			'image' => array(
				'name' 			=> __( 'Image Slide', 'theme-blvd-sliders' ),
				'main_title'	=> __( 'Setup Image', 'theme-blvd-sliders' )
			)
		);

		// Slide Media Positions
		$this->core_sliders['bootstrap']['positions'] = array(
			'full' => 'slider-large' // Default
		);

		// Slide Elements
		$this->core_sliders['bootstrap']['elements'] = array(
			'image_link',
			'headline',
			'description'
		);

		// Slider Options
		$this->core_sliders['bootstrap']['options'] = array(
			array(
				'id'		=> 'interval',
				'name' 		=> __( 'Seconds between each transition?', 'theme-blvd-sliders' ),
				'std'		=> '5',
				'type'		=> 'text'
		    ),
			array(
				'id'		=> 'pause',
				'name'		=> __( 'Enable pause on hover?', 'theme-blvd-sliders' ),
				'std'		=> 'true',
				'type'		=> 'select',
				'options'	=> array(
		            'hover'		=> __( 'Yes, pause slider on hover.', 'theme-blvd-sliders' ),
		            'false'		=> __( 'No, don\'t pause slider on hover.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'wrap',
				'name'		=> __( 'Cycle carousel continuously?', 'theme-blvd-sliders' ),
				'std'		=> 'true',
				'type'		=> 'select',
				'options'	=> array(
		            'true'		=> __( 'Yes, cycle continuously.', 'theme-blvd-sliders' ),
		            'false'		=> __( 'No, stop cycling.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'nav_standard',
				'name'		=> __( 'Show standard slideshow navigation?', 'theme-blvd-sliders' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show navigation.', 'theme-blvd-sliders' ),
		            '0'	=> __( 'No, don\'t show it.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'nav_arrows',
				'name'		=> __( 'Show next/prev arrows?', 'theme-blvd-sliders' ),
				'std'		=> '1',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show arrows.', 'theme-blvd-sliders' ),
		            '0'	=> __( 'No, don\'t show them.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'nav_thumbs',
				'name'		=> __( 'Show thumbnail navigation?', 'theme-blvd-sliders' ),
				'std'		=> '0',
				'type'		=> 'select',
				'options'	=> array(
		            '1'	=> __( 'Yes, show thumbnail navigation.', 'theme-blvd-sliders' ),
		            '0'	=> __( 'No, don\'t show it.', 'theme-blvd-sliders' )
				)
			),
			array(
				'id'		=> 'mobile_fallback',
				'name'		=> __( 'How to display on mobile devices?', 'theme-blvd-sliders' ),
				'std'		=> 'display',
				'type'		=> 'radio',
				'options'	=> array(
		            'full_list'		=> __( 'List out slides for a more user-friendly mobile experience.', 'theme-blvd-sliders' ),
		            'first_slide'	=> __( 'Show first slide only for a more simple mobile experience.', 'theme-blvd-sliders' ),
		            'display'		=> __( 'Attempt to show full animated slider on mobile devices.', 'theme-blvd-sliders' )
				)
			)
		);

		/*--------------------------------------------*/
		/* Extend
		/*--------------------------------------------*/

		$this->core_sliders = apply_filters( 'themeblvd_core_sliders', $this->core_sliders );

	}

	/**
	 * Set slider types by combining core elements and client-added
	 * slider types. Then remove any types that have been set to
	 * be removed. This happens at the "after_setup_theme" hook
	 * with a priority of 1000.
	 *
	 * @since 1.1.0
	 */
	public function set_sliders() {

		// Combine core elements with client elements
		$this->sliders = array_merge( $this->core_sliders, $this->client_sliders );

		// Remove elements
		if ( $this->remove_sliders ) {
			foreach ( $this->remove_sliders as $type ) {
				if ( isset( $this->sliders[$type] ) ) {
					unset( $this->sliders[$type] );
				}
			}
		}

		// Extend
		$this->sliders = apply_filters( 'themeblvd_recognized_sliders', $this->sliders );

	}

	/*--------------------------------------------*/
	/* Methods, client API mutators
	/*--------------------------------------------*/

	/**
	 * Add slider type.
	 *
	 * @since 1.1.0
	 *
	 * @param string $slider_id ID for new slider type
	 * @param string $slider_name Name for new slider type
	 * @param array $slide_types Slides types - image, video, custom
	 * @param array $media_positions Positions for media - full, align-left, align-right
	 * @param array $slide_elements Elements to include in slides - image_link, headline, description, button
	 * @param array $options Options formatted for Options Framework
	 * @param string $callback Function to display slider on frontend
	 */
	public function add( $slider_id, $slider_name, $slide_types, $media_positions, $slide_elements, $options, $callback ) {

		if ( is_admin() ) {

			// Start new slider
			$new_slider = array(
				'name' 		=> $slider_name,
				'id'		=> $slider_id,
				'options'	=> $options,
				'elements'	=> $slide_elements
			);

			// Slide Types
			// $slide_types should look something like: array( 'image', 'video', 'custom' )
			$new_slider['types'] = array();

			if ( $slide_types ) {
				foreach ( $slide_types as $type ) {
					switch ( $type ) {

						case 'image' :
							$new_slider['types']['image'] = array(
								'name' 			=> __( 'Image Slide', 'theme-blvd-sliders' ),
								'main_title' 	=> __( 'Setup Image', 'theme-blvd-sliders' )
							);
							break;

						case 'video' :
							$new_slider['types']['video'] = array(
								'name' 			=> __( 'Video Slide', 'theme-blvd-sliders' ),
								'main_title' 	=> __( 'Video Link', 'theme-blvd-sliders' )
							);
							break;

						case 'custom' :
							$new_slider['types']['custom'] = array(
								'name' 			=> __( 'Custom Slide', 'theme-blvd-sliders' ),
								'main_title' 	=> __( 'Setup Custom Content', 'theme-blvd-sliders' )
							);
							break;

					}
				}
			}

			// Slide Media Positions
			// $media_positions should look something like: array( 'full' => 'crop_size', 'align-left' => 'crop_size', 'align-right' => 'crop_size' )
			$new_slider['positions'] = array();

			$positions = apply_filters( 'themeblvd_slider_image_positions', array( 'full', 'align-left', 'align-right' ) );

			if ( $media_positions ) {
				foreach ( $media_positions as $position => $crop_size ) {
					if ( in_array( $position, $positions ) ) {
						$new_slider['positions'][$position] = $crop_size;
					}
				}
			}

			// Add new slider
			$this->client_sliders[$slider_id] = $new_slider;

		}

		// Add frontend display
		add_action( 'themeblvd_'.$slider_id.'_slider', $callback, 10, 3 );
	}

	/**
	 * Remove slider type.
	 *
	 * @since 1.1.0
	 *
	 * @param string $type ID of slider type to remove
	 */
	public function remove( $type ) {
		$this->remove_sliders[] = $type;
	}

	/*--------------------------------------------*/
	/* Methods, accessors
	/*--------------------------------------------*/

	/**
	 * Get default slider types.
	 *
	 * @since 1.1.0
	 *
	 * @return array $core_sliders
	 */
	public function get_core_sliders() {
		return $this->core_sliders;
	}

	/**
	 * Get slider types added through client
	 * API mutators.
	 *
	 * @since 1.1.0
	 *
	 * @return array $client_sliders
	 */
	public function get_client_sliders() {
		return $this->client_sliders;
	}

	/**
	 * Get slider types to be removed.
	 *
	 * @since 1.1.0
	 *
	 * @return array $remove_sliders
	 */
	public function get_remove_sliders() {
		return $this->remove_sliders;
	}

	/**
	 * Get finalized slider types.
	 *
	 * @since 1.1.0
	 *
	 * @param string $type Optional specific slider type to return
	 * @return array $sliders
	 */
	public function get_sliders( $type = '' ) {

		if ( ! $type ) {
			return $this->sliders;
		}

		if ( isset( $this->sliders[$type] ) ) {
			return $this->sliders[$type];
		}

		return array();

	}

	/*--------------------------------------------*/
	/* Methods, helpers
	/*--------------------------------------------*/

	/**
	 * Determine if slider type is valid
	 *
	 * @since 1.1.0
	 *
	 * @param string $type Type of slider to check for
	 * @return bool
	 */
	public function is_slider( $type ) {

		if ( isset( $this->sliders[$type] ) ) {
			return true;
		}

		return false;
	}

} // End class Theme_Blvd_Sliders_API