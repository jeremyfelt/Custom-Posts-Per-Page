=== Custom Posts Per Page ===

Contributors: jeremyfelt
Donate link: https://jeremyfelt.com/wordpress/plugins/custom-posts-per-page/
Tags: admin, administration, settings, archives, posts-per-page, paged, posts, count, number, custom-post-type
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 1.7.1

Custom Posts Per Page provides a settings page in your WordPress admin that allows you to specify how many posts are displayed for different views.

== Description ==

Custom Posts Per Page allows you to specify how many posts are displayed per page depending on your current view. Once settings are changed, the *Blog pages show at most* setting in the *Reading* menu will be ignored.

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

== Installation ==

1. Upload 'custom-posts-per-page-count.php' to your plugin directory, usually 'wp-content/plugins/', or install automatically via your WordPress admin page.
1. Active Custom Posts Per Page in your plugin menu.
1. Configure using the Posts Per Page menu under Settings in your admin page. (*See Screenshot*)

That's it! The current setting for *Blog pages show at most* under *Reading* will be used to fill in the default values. You can take over from there.

== Frequently Asked Questions ==
= What are you doing with found_posts? =

* An issue was appearing in plugins that assisted with pagination when the setting for posts per page was different from subsequent pages. To resolve this issue, we do some math and return an incorrect found_posts value when that scenario occurs. This doesn't affect any core WordPress functionality, but could confuse other plugins that are looking for a correct value. I wouldn't worry about this much, but keep it in mind if you are seeing issues *and* have two different values entered as described.

= Should I keep using WordPress 3.2.1? =

* No. The next release of this plugin, version 1.8, will *not* support WordPress 3.2.1.

== Screenshots ==

1. An overview of the Custom Posts Per Page settings screen.

== Changelog ==
= 1.7.1 =
* Switch to semantic versioning. :)
* Code cleanup, compatibility testing with 4.1.
* Add some extra sanitization, escaping.
* Remove backwards compatible `is_main_query()` check. Use WordPress 4.0 or greater.

= 1.7 =
* Update documentation, compatibility testing with 3.5.

= 1.6 =
* General code cleanup, more documentation
* Move final_options into a private property for easy passing around the plugin
* Much improved logic for handling the expected page count based on found posts. Should resolve any issues found when using plugins such as WP-PageNavi to show number of pages in navigation.

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

== Upgrade Notice ==
= 1.6 =
* Fixes issues of wrongly reported page numbering when using WP-PageNavi or other paged navigation plugins that rely on WordPress's page count.

= 1.4 =

* Fixes issues where queries in sidebars were affected. My bad.

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