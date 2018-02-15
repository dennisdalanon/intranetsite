=== Eonet Frontend Publisher ===
Contributors: alkaweb
Tags: frontend, AJAX, edit, live, create, post types, roles, posts
Requires at least: 3.0.1
Tested up to: 4.7
Stable tag: 1.0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create and Edit any WordPress post type, easily with Eonet. Manage permissions as well.

== Description ==

*Tired of having to edit everything from the backend ? Or you want your users to contribute ?*

Eonet Frontend publisher can do both. It'll add a nice button to the bottom of the post types you've selected from the settings.
And of course only for the allowed users. There is 3 main methods : create / edit / delete. They're all possible with Eonet.
Whenever you click the button, a shiny modal with a form will pop up on your right. You'll be able to make all your changes there.

**[Live Demo](http://alka-web.com/eonet/frontend-publisher/)**

= Featured features: =
* Create any Wordpress post type (page, blog post, product...)
* Edit any Wordpress post type
* Options panel
* Option to choose the post status
* Choose which role is allowed to create new posts
* Choose which role is allowed to edit current posts (post's author is by default)
* Choose which post types are concerned
* Shortcode available (see bellow)
* Add notes underneath the form to guide your users
* AJAX powered, real time saving

= For developers: =
* Hooks/Filters available in all the plugin code
* Minified files
* Documented code
* GPL license
* Secure development using tokens and Wordpress native functions

= Shortcode: =

Add this handy shortcode in any page or post to be able to create WP post from the frontend :

`[eonet_frontend_create type="post"]`

* `type` can take any Wordpress post type such as post, page...
* `wrapper` is an additional parameter to disable the button's wrapper tags, if you want it to be inline for instance.


If you're looking for a next generation post editor, you should give it a go!

We're open to any feature suggestion.

This plugin has been developed as a side project by the Alkaweb developers team.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/eonet-frontend-publisher` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Eonet screen to configure the plugin

== Screenshots ==

1. Options panel for the component. You'll find here all the options to customize it.
2. Button displayed on the page or post type if you're allowed to edit it
3. Top part of the modal
4. Bottom part of the modal

== Changelog ==

(C) stands for updates to the core of Eonet. Not its components.

= 1.0.9 - July 21th 2017 =
* (FIXED) Javascript error in the backend (Woffice conflict)

= 1.0.8 - July 20th 2017 =
* (UPDATED) The Eonet core inside the plugin
* (FIXED) The conflict with Bootstrap dropdown menu

= 1.0.7 - March 12th 2017 =
* (C)(IMPROVED) Options Style
* (C)(FIXED) Missing Font Awesome icons

= 1.0.6 - February 10th 2017 =
* (IMPROVED) Overall design
* (C)(IMPROVED) Translation management

= 1.0.5 - January 6th 2017 =
* (NEW) Translation file
* (NEW) Action 'eonet_frontend_custom_process'
* (NEW) Filter 'eonet_front_custom_fields'

= 1.0.4 - 19th November 2016 =
* (C)(NEW) Settings link in the plugins page
* (C)(FIXED) Saved option for switcher input type
* (C)(FIXED) Fatal Error on activation

= 1.0.3 - 8th November 2016 =
* (NEW) Featured Image Option
* (NEW) Tags Option for blog posts
* (FIXED) Selected categories by default
* (C)(NEW) WP Upload Attachment Option
* (C)(NEW) WP Tag Option for any taxonomy

= 1.0.2 - 3rd November 2016 =
* (NEW) Setup guide in the option's page
* (FIXED) Fullscreen mode in the frontend
* (C)(FIXED) Multi select option's default value

= 1.0.1 - 1st November 2016 =
* (FIXED) Modal issue on page loading
* (C)(IMPROVED) CSS loading by splitting core / components CSS and SCSS files
* (C)(FIXED) the built-in component installer
* (C)(FIXED) version number

= 1.0.0 - 28th October 2016 =
* Initial Release
