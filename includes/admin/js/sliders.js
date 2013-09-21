/**
 * Prints out the inline javascript needed for managing sliders.
 * This is an extension of what was already started in the
 * options-custom.js file.
 */

jQuery(document).ready(function($) {

	/*-----------------------------------------------------------------------------------*/
	/* Static Methods
	/*-----------------------------------------------------------------------------------*/

	var slider_blvd = {

    	// Update Manage Sliders page's table
    	manager : function( table )
    	{
    		if(table)
			{
				// We already have the table, so just throw it in.
				$('#slider_blvd #manage .ajax-mitt').html(table);
			}
			else
			{
				// We don't have the table yet, so let's grab it.
				$.ajax({
					type: "POST",
					url: ajaxurl,
					data:
					{
						action: 'themeblvd_update_slider_table'
					},
					success: function(response)
					{
						$('#slider_blvd #manage .ajax-mitt').html(response);
					}
				});
			}
    	},

    	// Delete Slider
    	delete_slider : function( ids, action, location )
    	{
    		var nonce  = $('#manage_sliders').find('input[name="_wpnonce"]').val();
			tbc_confirm( themeblvd.delete_slider, {'confirm':true}, function(r)
			{
		    	if(r)
		        {
		        	$.ajax({
						type: "POST",
						url: ajaxurl,
						data:
						{
							action: 'themeblvd_delete_slider',
							security: nonce,
							data: ids
						},
						success: function(response)
						{
							// Prepare response
							response = response.split('[(=>)]');

							// Insert update message, fade it in, and then remove it
							// after a few seconds.
							$('#slider_blvd #manage').prepend(response[1]);
							$('#slider_blvd #manage .ajax-update').fadeIn(500, function(){
								setTimeout( function(){
									$('#slider_blvd #manage .ajax-update').fadeOut(500, function(){
										$('#slider_blvd #manage .ajax-update').remove();
									});
						      	}, 1500);

							});

							// Change number of sliders
							$('#slider_blvd .displaying-num').text(response[0]);

							// Update table
							if(action == 'submit')
							{
								$('#manage_sliders').find('input[name="posts[]"]').each(function(){
									if( $(this).is(':checked') )
									{
										var id = $(this).val();
										if( $('#edit-tab').hasClass(id+'-edit') )
											$('#edit-tab').hide();
										$(this).closest('tr').remove();
									}
								});
							}
							else if(action == 'click')
							{
								var id = ids.replace('posts%5B%5D=', '');
								if( $('#edit-tab').hasClass(id+'-edit') )
									$('#edit-tab').hide();
								$('#row-'+id).remove();
							}

							// Uncheck all checkboxes
							$('#manage_sliders option').removeAttr('checked');

							// Forward back to manage sliders page, if
							// we're deleting this slider from the Edit
							// Slider page.
							if(location == 'edit_page')
							{
								$('#slider_blvd .group').hide();
								$('#slider_blvd .group:first').fadeIn();
								$('#slider_blvd .nav-tab-wrapper a:first').addClass('nav-tab-active');
							}
						}
					});
		        }
		    });
    	},

    	// Enter into editing a slider
    	edit : function ( name, page )
    	{
    		// Get the ID from the beginning
			var page = page.split('[(=>)]');

			// Prepare the edit tab
			$('#slider_blvd .nav-tab-wrapper a.nav-edit-slider').text(themeblvd.edit_slider+': '+name).addClass(page[0]+'-edit');
			$('#slider_blvd #edit_slider .ajax-mitt').html(page[1]).themeblvd('options', 'setup');

			// Setup elements
			$('#slider_blvd .slide-element-check input').each(function(){
				slider_blvd.elements( $(this) );
			});

			// Setup types
			$('#slider_blvd .slide-set-type select').each(function(){
				slider_blvd.type( $(this) );
			});

			// Setup widgets
			$('#slider_blvd .widget').themeblvd('widgets');

			// Setup media uploader
			$('#slider_blvd .widget').themeblvd('options', 'media-uploader');

			// Setup sortables
			$('#sortable').sortable({
				handle: '.widget-name'
			});

			// Enable WP's post box toggles
			// requires: wp_enqueue_script('postbox');
			postboxes.add_postbox_toggles(pagenow, {
				pbshow: slider_blvd.show_widget,
				pbhide: slider_blvd.hide_widget
			});

			// Take us to the tab
			$('#slider_blvd .nav-tab-wrapper a').removeClass('nav-tab-active');
			$('#slider_blvd .nav-tab-wrapper a.nav-edit-slider').show().addClass('nav-tab-active');
			$('#slider_blvd .group').hide();
			$('#slider_blvd .group:last').fadeIn();
    	},

    	// These methods are passed into WP's postboxes.add_postbox_toggles
    	// as the pbshow and bphide parameters. They allow the widgets to
    	// be toggled open and close.
    	hide_widget : function( id )
    	{
    		// Don't apply to Publish box
    		if( $('#'+id).hasClass('postbox-publish') )
    			return;

    		$('#'+id+' .tb-widget-content').hide();
    	},
    	show_widget : function( id )
    	{
    		$('#'+id+' .tb-widget-content').show();
    	},

    	// Show setup for slide according to what type of slide we're working with.
    	// Will always be image, video, or custom type of slide.
    	type : function( object )
    	{
    		var opposite,
    			helper_text,
				parent = object.closest('.widget'),
				value = object.val(),
				position = object.closest('.widget-content').find('.slide-position-'+value).val();
				name = object.find('option[value="'+value+'"]').text();

			parent.removeClass('type-image type-video type-custom');
			parent.addClass('type-'+value);
			parent.find('.slide-set-type strong').text(name);
			parent.find('.widget-name h3').removeClass('image video custom').addClass(value).text(name);

			parent.find('.slide-summary').removeClass('image video').hide().text('');

			if(value == 'custom')
			{
				parent.find('.slide-media').hide();
				parent.find('.slide-custom').fadeIn('fast');
			}
			else
			{
				if(value == 'image')
				{
					opposite = 'video';

					helper_text = parent.find('.slide-set-image .image-title').val();
					parent.find('.slide-summary').addClass('image');
					if( helper_text )
						parent.find('.slide-summary').text(helper_text).fadeIn(200);

					parent.find('.slide-position-image').show();
					parent.find('.slide-crop').show();

					parent.find('.slide-position-video').hide();
					parent.find('.slide-video-height').hide();
					parent.find('.image-note').show();

					if( parent.find('.slide-elements .element-image_link input').is(':checked') )
						parent.find('.slide-elements .element-image_link').show();
					else
						parent.find('.slide-elements .element-image_link:first').show();

					parent.find('.slide-elements-warning').hide();
					parent.find('.slide-elements').show();

					if( parent.find('.slide-position').val() == 'full' )
					{
						parent.find('.slide-crop').show();
						parent.find('.element-button').hide();
					}
					else
					{
						parent.find('.slide-crop').hide();
						parent.find('.element-button.slide-element-header').show().find('input').each(function(){
							slider_blvd.elements( $(this) );
						});
					}
				}
				else if(value == 'video')
				{

					opposite = 'image';

					helper_text = parent.find('.slide-set-video .video-link input').val();
					parent.find('.slide-summary').addClass('video');

					if( helper_text )
						parent.find('.slide-summary').text(helper_text).fadeIn(200);

					parent.find('.slide-elements .element-image_link').hide();
					parent.find('.slide-position-video').show();
					parent.find('.slide-position-image').hide();
					parent.find('.slide-video-height').show();
					parent.find('.slide-crop').hide();
					parent.find('.image-note').hide();
					parent.find('.slide-element-header').show();

					if(position == 'full')
					{
						parent.find('.slide-elements').hide();
						parent.find('.slide-elements-warning').show();
					}
					else
					{
						parent.find('.slide-elements-warning').hide();
						parent.find('.slide-elements').show();
					}
				}
				parent.find('.slide-set-media .slide-set-'+opposite).hide();
				parent.find('.slide-set-media .slide-set-'+value).show();
				parent.find('.slide-custom').hide();
				parent.find('.slide-media').fadeIn('fast');
			}
		},

		// Slide's position of media - full width, aligned right, aligned left
		position : function( object )
		{

			if( object.closest('.widget').hasClass('type-video') )
			{
				var value = object.val(), parent = object.closest('.widget-content');
				parent.find('.slide-elements .element-image_link').hide();
				if(value == 'full')
				{
					parent.find('.slide-elements').hide();
					parent.find('.slide-elements-warning').show();
				}
				else
				{
					parent.find('.slide-elements-warning').hide();
					parent.find('.slide-elements').show();
				}
			}
			else if( object.closest('.widget').hasClass('type-image') )
			{

				var slide_crop = object.closest('.widget-content').find('.slide-crop');
				if(object.val() == 'full')
				{
					object.closest('.widget-content').find('.slide-crop').show();
					object.closest('.widget-content').find('.element-button').hide();
				}
				else
				{
					object.closest('.widget-content').find('.slide-crop').hide();
					object.closest('.widget-content').find('.element-button.slide-element-header').show().find('input').each(function(){
						slider_blvd.elements( $(this) );
					});
				}
			}
		},

		// Show/hide elements when editing a slide
    	elements : function( object )
    	{
	    	var option_set = object.closest('tr').next('tr');
			if ( object.is(':checked') )
				option_set.show();
			else
				option_set.hide();
    	}
    };

	/*-----------------------------------------------------------------------------------*/
	/* General setup
	/*-----------------------------------------------------------------------------------*/

	// Items from themeblvd namespace
	$('#slider_blvd .widget').themeblvd('widgets');

	// Hide secret tab when page loads
	$('#slider_blvd .nav-tab-wrapper a.nav-edit-slider').hide();

	// If the active tab is on edit slider page, we'll
	// need to override the default functionality of
	// the Options Framework JS, because we don't want
	// to show a blank page.
	if (typeof(localStorage) != 'undefined' )
	{
		if( localStorage.getItem('activetab') == '#edit')
		{
			$('#slider_blvd .group').hide();
			$('#slider_blvd .group:first').fadeIn();
			$('#slider_blvd .nav-tab-wrapper a:first').addClass('nav-tab-active');
		}
	}

	// Edit slider binded events
	$('#slider_blvd').on( 'change', '.slide-set-type select', function() {
		slider_blvd.type( $(this) );
	});
	$('#slider_blvd').on( 'change', '.slide-position', function() {
		slider_blvd.position( $(this) );
	});
	$('#slider_blvd').on( 'change', '.slide-element-check input', function() {
		slider_blvd.elements( $(this) );
	});

	/*-----------------------------------------------------------------------------------*/
	/* Manage Sliders Page
	/*-----------------------------------------------------------------------------------*/

	// Edit slider (via Edit Link on manage page)
	$('#slider_blvd').on( 'click', '#manage .edit-tb_slider', function() {
		var name = $(this).closest('tr').find('.post-title .title-link').text(),
			id = $(this).attr('href'),
			id = id.replace('#', '');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_edit_slider',
				data: id
			},
			success: function(response)
			{
				slider_blvd.edit( name, response );
			}
		});
		return false;
	});

	// Delete slider (via Delete Link on manage page)
	$('#slider_blvd').on( 'click', '.row-actions .trash a', function() {
		var href = $(this).attr('href'), id = href.replace('#', ''), ids = 'posts%5B%5D='+id;
		slider_blvd.delete_slider( ids, 'click' );
		return false;
	});

	// Delete sliders via bulk action
	$('#slider_blvd').on( 'submit', '#manage_sliders', function() {
		var value = $(this).find('select[name="action"]').val(), ids = $(this).serialize();
		if(value == 'trash')
			slider_blvd.delete_slider( ids, 'submit' );
		return false;
	});

	/*-----------------------------------------------------------------------------------*/
	/* Add New Slider Page
	/*-----------------------------------------------------------------------------------*/

	// Add new slider
	$('#optionsframework #add_new_slider').submit(function(){
		var el = $(this),
			data = el.serialize(),
			load = el.find('.ajax-loading'),
			name = el.find('input[name="options[slider_name]"]').val(),
			nonce = el.find('input[name="_wpnonce"]').val();

		// Tell user they forgot a name
		if(!name)
		{
			tbc_confirm(themeblvd.no_name, {'textOk':'Ok'});
		    return false;
		}

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_add_slider',
				security: nonce,
				data: data
			},
			beforeSend: function()
			{
				load.fadeIn('fast');
			},
			success: function(response)
			{
			    if (response == 'error_type')
				{
					// Tell 'em the type of slider is invalid.
					tbc_confirm(themeblvd.invalid_slider, {'textOk':'Ok'});
				}
				else
				{
					// Scroll to top of page
					$('body').animate( { scrollTop: 0 }, 100, function(){
						// Everything is good to go. So, forward
						// on to the edit slider page.
						slider_blvd.edit( name, response );
						tbc_alert.init(themeblvd.slider_created, 'success');
						el.find('input[name="options[slider_name]"]').val('');
					});

					// Update slider management table in background
					slider_blvd.manager();
				}

				// Hide loader no matter what.
				load.hide();
			}
		});
		return false;
	});

	/*-----------------------------------------------------------------------------------*/
	/* Edit Slider Page
	/*-----------------------------------------------------------------------------------*/

	// Add new slide
	$('#optionsframework').on( 'click', '#add_new_slide', function() {
		var el = $(this),
			id,
			trim_front,
			trim_back,
			slide_id,
			overlay = el.parent().find('.ajax-overlay'),
			load = el.parent().find('.ajax-loading');
			type = el.attr('href'),
			type = type.replace('#', '');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_add_slide',
				data: type
			},
			beforeSend: function()
			{
				overlay.fadeIn('fast');
				load.fadeIn('fast');
			},
			success: function(response)
			{
				trim_front = response.split('<div id="');
				trim_back = trim_front[1].split('" class="widget slide-options"');
				slide_id = trim_back[0];
				$('#slider_blvd #edit #sortable .no-item-yet').remove();
				$('#slider_blvd #edit #sortable').append(response);
				$('#'+slide_id+' .slide-set-type select').each(function(){
					slider_blvd.type( $(this) );
				});
				$('#'+slide_id).themeblvd('widgets').themeblvd('options', 'media-uploader');
				$('#'+slide_id).fadeIn();
				load.fadeOut('fast');
				overlay.fadeOut('fast');
			}
		});
		return false;
	});

	// Save Slider
	$('#optionsframework').on( 'submit', '#edit_slider', function() {
		var el = $(this),
			data = el.serialize(),
			load = el.find('.publishing-action .ajax-loading'),
			nonce = el.find('input[name="_wpnonce"]').val();

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_save_slider',
				security: nonce,
				data: data
			},
			beforeSend: function()
			{
				load.fadeIn('fast');
			},
			success: function(response)
			{

				// Prepare response
				response = response.split('[(=>)]');

				// Insert update message, fade it in, and then remove it
				// after a few seconds.
				$('#slider_blvd #edit').prepend(response[1]);

				// Make sure all "Slider Names" match on current edit layout page.
				current_name = $('#slider_blvd #post_title').val();
				$('#slider_blvd #edit-tab').text(themeblvd.edit_slider+': '+current_name);
				$('#slider_blvd .postbox-publish h3').text(themeblvd.publish+' '+current_name);
				$('#slider_blvd .postbox-slider-info #post_name').val(response[0]);

				// Scroll to top of page
				$('body').animate( { scrollTop: 0 }, 50, function(){
					// Fade in the update message
					$('#slider_blvd #edit .ajax-update').fadeIn(500, function(){
						setTimeout( function(){
							$('#slider_blvd #edit .ajax-update').fadeOut(500, function(){
								$('#slider_blvd #edit .ajax-update').remove();
							});
				      	}, 1500);

					});
				});
				load.fadeOut('fast');

				// Update slider management table in background
				slider_blvd.manager();
			}
		});
		return false;
	});

	// Delete slider (via Delete Link on edit slider page)
	$('#slider_blvd #edit').on( 'click', '.delete_slider', function() {
		var href = $(this).attr('href'), id = href.replace('#', ''), ids = 'posts%5B%5D='+id;
		slider_blvd.delete_slider( ids, 'click', 'edit_page' );
		return false;
	});

	// Update helper text in slide title
	$('#slider_blvd #edit').on( 'change', '.video-link input', function() {
		var widget = $(this).closest('.widget'), helper_text = $(this).val();
		if( helper_text )
			widget.find('.slide-summary').text(helper_text).fadeIn(200);
		else
			widget.find('.slide-summary').hide().text('');
	});

});