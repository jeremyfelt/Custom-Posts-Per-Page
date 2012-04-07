Custom Posts Per Page
====================

Contributors: jeremyfelt
Donate link: http://www.jeremyfelt.com/wordpress/plugins/custom-posts-per-page/
Tags: admin, administration, settings, archives, posts-per-page, paged, posts, count, number, custom-post-type
Requires at least: 3.1
Tested up to: 3.4
Stable tag: 1.5

Custom Posts Per Page provides a settings page in your WordPress admin that allows you to specify how many posts are displayed for different views.

Description
--------------
Custom Posts Per Page allows you to specify how many posts are displayed per page depending on your current view. Once settings are changed, the *Blog pages show at most* setting in the *Reading* menu will ignored.

Settings are available for:

* Home (Index) Page
    * As long as view is set to blog posts, not static page.
* Category Pages
* Tag Pages
* Author Pages
* Archive Pages
* Search Pages
* Default Page (*Any page not covered above.*)
* Custom Post Type archive pages
    * All Custom Post Types are detected automatically.

Each of the above settings can have a different value for the first page displayed **and** subsequent paged views.

Custom Posts Per Page makes it easy to manage exactly how your content is displayed to your readers, especially when different views have different layouts, rather than relying on the single setting in the Reading menu or having to hard code options in your custom theme.

Installation
---------------

1. Upload 'custom-posts-per-page-count.php' to your plugin directory, usually 'wp-content/plugins/', or install automatically via your WordPress admin page.
1. Active Custom Posts Per Page in your plugin menu.
1. Configure using the Posts Per Page menu under Settings in your admin page. (*See Screenshot*)

That's it! The current setting for *Blog pages show at most* under *Reading* will be used to fill in the default values. You can take over from there.

Frequently Asked Questions
----------------------------
= Should I keep using WordPress 3.2.1? =

* No. I'm being nice for now, but when 3.5 comes out, I'm totally removing the last piece of code that ties this plugin to 3.2.1. Sorry. :)

= Why aren't there any FAQs? =

*  Because nobody has asked a question yet.

Screenshots
--------------

1. An overview of the Custom Posts Per Page settings screen.

Changelog
----------
= 1.5 =
* A bunch of code cleanup. Move everything to a class.
* Cleanup text domain stuff in preparation for a new translation (sweet!)
* Document more, handle default settings a bit better.

= 1.4 =
* **New** - Proper handling with is_main_query. Will no longer affect queries for side bars and such.
* General code cleanup, IDE was using ugly spaces
* Reworked some DB options to fit the schema for the rest. Easier to handle in code now.

= 1.3.3 =
* Beginnings of new fix to handle paged offsets. Paging works as expected now.
* Sorry for all the updates. Screwed that one up for a minute. :)

= 1.3.2 =
* Quick immediate fix of offset issues on paged views. Exploring deeper fix.

= 1.3.1 =
* Fix mishandling of adding new options during upgrade.

= 1.3 =
* **New** - Added options to control first page vs subsequent pages for all views.
* Cleaned up handling of option initialization upon activation.
* Cleaned up handling of option validation

= 1.2.2 =
* Undefined index headers may have been output on some servers, causing a small error in WordPress upon activation. Resolved.

= 1.2.1 =
* Confirmed and noted support for 3.3

= 1.2 =
* Added I18n support, now accepting translations!
* Added an uninstall.php file to handle option cleanup in the database if the plugin is ever deleted. Please don't delete me. :)
* Added a 'Settings' link under the plugin once activated to make it easier to configure right away.
* Made some changes to the readme file better describing the current state of things.

= 1.1 =
* Added an option for Front Page Posts Count so that the front page could be treated differently than pages 2,3,etc..
* Corrected issue that may have made it possible to lose settings on deactivation/activation or update.

= 1.0 =

* **Custom Post Types** - Custom post types are automatically detected and can be configured through settings.
* Now pulls current *Blog pages show at most* value for use on plugin activation instead of defaulting to 10.
* Allows for value of 0 to be set on any option in order for that view to be controlled somewhere else.

= 0.2 =

* Directory structure sent to SVN stopped plugin from working. Resolved.

= 0.1 =

* In which a plugin begins its life.

Upgrade Notice
---------------
= 1.3 =

* Adds awesome support for paged views.

= 1.2.2 =

* Cleans up possible small error upon activation.

= 1.2.1 =

* No upgrade really necessary. Just a confirmation of WordPress 3.3 support.

= 1.2 =

* Upgrade provides uninstall.php to clean up nicely if the plugin is deleted.

= 1.1 =

* Upgrade provides a different setting for Front Page view versus pages 2,3,etc... on index page.

= 1.0 =

* Upgrade, while not necessarily required, provides support for custom post types. And that's awesome.

= 0.2 =

* Upgrade required for directory structure change.

= 0.1 =

* Initial installation.