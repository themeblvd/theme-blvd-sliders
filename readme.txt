=== Theme Blvd Sliders ===
Author URI: http://www.jasonbobich.com
Contributors: themeblvd
Tags: slider, sliders, slideshow, slideshows, flexslider, gallery, Theme Blvd, themeblvd, Jason Bobich
Stable Tag: 1.2.5

When using a Theme Blvd theme, this plugin gives you slick interface to build custom sliders.

== Description ==

When using a Theme Blvd theme, this plugin gives you slick interface to build custom, responsive sliders that can then be incorporated throughout your website via shortcode or [custom layouts](http://wordpress.org/extend/plugins/theme-blvd-layout-builder/).

= Quick Feature Overview =

* WordPress admin interface to create custom sliders.
* An element added to the [Layout Builder](http://wordpress.org/extend/plugins/theme-blvd-layout-builder/) to include a custom slider.
* An element added to the [Layout Builder](http://wordpress.org/extend/plugins/theme-blvd-layout-builder/) to include a slider generated from posts (with Theme Blvd framework v2.2.1+).
* A shortcode for inserting custom sliders into pages and posts - `[slider id="your-slider"]`
* A shortcode for inserting sliders generated from posts (with Theme Blvd framework v2.2.1+) - `[post_slider category="foo"]` `[post_slider tag="bar"]`
* Sliders are responsive and will scale to fit their surrounding container.
* Three slider types included by default for custom sliders - [Flexslider](http://flexslider.woothemes.com), [Nivo](http://dev7studios.com/nivo-slider/), and [Roundabout](http://fredhq.com/projects/roundabout)
* Slider types are extendable through Theme Blvd framework API. - [View Docs](http://dev.themeblvd.com/tutorial/add-custom-slider/)

**NOTE: For this plugin to do anything, you must have a theme with Theme Blvd framework v2.2+ activated.**

== Installation ==

1. Upload `theme-blvd-sliders` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to *Sliders* in your WordPress admin panel to to use the Slider Manager.

= Displaying your custom sliders =

Sliders you create can be utilized within your website in two ways.

1. You can insert them with the slider shortcode like this: `[slider id="your-slider"]`
2. If you have the [Layout Builder](http://wordpress.org/extend/plugins/theme-blvd-layout-builder/) running, you can insert the sliders you've created into your custom layout via the "Slider" element.

== Screenshots ==

1. Manage your custom sliders.
2. Add a new custom slider.
3. Edit a custom slider with the Sliders interface.

== Changelog ==

= 1.2.5 =

* Improvement: Use `add_menu_page` instead of `add_object_page`, which was deprecated in WordPress 4.5.

= 1.2.4 =

* GlotPress compatibility (for 2015 wordpress.org release).
* Minor security fix.

= 1.2.3 =

* Fixed image alignment issues on “Standard” slider type that occurred from of previous update.

= 1.2.2 =

* Fixes for themes with Theme Blvd Framework 2.5+
* Removed "3D Carousel" slider type for themes with Theme Blvd Framework 2.5+

= 1.2.1 =

* Added support for thumbnail navigation with Bootstrap Carousel (requires Theme Blvd framework v2.4.2+).

= 1.2.0 =

* Admin style updates for WordPress 3.8 (requires Theme Blvd framework v2.4+).
* Added "Bootstrap Carousel" slider type.
* Fixed 3D Carousel slider's navigation for FontAwesome 4.
* Added filter `themeblvd_nivo_image` to image output of Nivo type sliders.

= 1.1.5 =

Added filters onto slider headline, description, and button text.

= 1.1.4 =

* Admin jQuery improvements for 1.9 - Converted all .live() to .on()

= 1.1.3 =

* Fixed bug with Slider Manager not showing currently selected image crop size properly.

= 1.1.2 =

* Fixed Slider Manager UI bug with switching "Media Display" and "Slide Elements" not adjusting correctly.

= 1.1.1 =

* Fixed "category" parameter not working with `[post_slider]` shortcode.

= 1.1.0 =

* Improved and moved Sliders API functionality to plugin.
* Minor improvements to admin javascript.
* Added `themeblvd_sliders_post_type_args` filter on registered `tb_sliders` post type's `$args`.
* Added compatibility for [Portfolios](http://wordpress.org/plugins/portfolios/) plugin with Post Slider. With this plugin activated you can use "portfolio" and "portfolio_tag" parameters with `[post_slider]`.

= 1.0.4 =

* Fixed non-working `nav_standard`, `nav_arrows`, and `pause_play` options of `[post_slider]` shortcode.

= 1.0.3 =

* Added filter on slides array when displaying sliders.
* Adjusted category and tag parameters for `[post_slider]` shortcode.
* Changed slide description/button area to use div's and not paragraph tags.
* Added wp auto formatting to slide description. Can turn off by filtering "themeblvd_standard_slider_desc" to false.
* Added "themeblvd_standard_slider_button" filter to parameters feed into themeblvd_button when displaying buttons in sliders.

= 1.0.2 =

* Fixed bug with "custom" type slides not saving.
* Added support for "Post Slider" element.
* Added `[post_slider]` shortcode.
* Added support for minor admin UI improvements in Theme Blvd framework v2.2.1 update.

= 1.0.1 =

* Added "clearfix" class for staged images in slides.
* Changed `<span>` in slide description to `<div>` for markup validation.

= 1.0.0 =

* This is the first release.
