=== oxds - Oxford Digital Signage ===
Contributors: Guido Klingbeil, Marko Jung
Tags: shortcode, posts, custom post types, digital signage
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html


== Short Description ==

Displays a single post of a series of posts as digital signs in a page.


== Description ==
Displays a single post from a given category in a page as digital sign. The post is selected in a round robin fashion where the user is able to specify that only the first n posts of the category are to be circled. The time a post is displayed can be specified. The default is 60 seconds. 

The user may specify (default value):

category           	The category of the posts to be displyed.
refresh time (60 sec.)  The time in seconds each post is displayed.
number of posts (1)     Display the first n posts of the given category in a round robin fashion. 


== Notes ==

This plugin is built upon the posts_in_page and the Auto Refresh Single Page plugin. We would like to thank the authors.

This is a minimal plugin, function over form.  If you would like to extend it, or would like us to extend it in later versions, please post feature suggestions in the plugin's [support forum](http://wordpress.org/support/plugin/posts-in-page) or [contact us](http://www.ivycat.com/contact/).

== Installation ==

You can install from within WordPress using the Plugin/Add New feature, or if you wish to manually install:

1. Download the plugin,
1. Upload the entire `ox-digital-signage` directory to your plugins folder, 
1. Activate the plugin in your WordPress plugin page,
1. Start using your posts as digital signs.


== Usage ==

Shortcode usage:

* `[oxds_add_sign]`  - Add all posts to a page (limit to what number posts in WordPress is set to), essentially adds blog "page" to page.


== Screenshots ==

1. Embed a shortcode into a page and it will automatically pull in the post(s) you need.


== Frequently Asked Questions ==
None yet. We are happy answer any questions to the best of our knowledge though.


= What is the point of this plugin? =

To be done.

We were looking for an easy to set-up and maintain digital signage (http://en.wikipedia.org/wiki/Digital_signage) solution to be connected to a CMS (content managenment system).




= How do I change the output template =

Simply copy the posts_loop_template.php to your theme directory and make changes as necessary. 

You can even rename it - but make sure to indicate that in the shortcode using the `template='template_name.php'`.  

You can even use multiple layouts for each shortcode if you like.


== Changelog ==

= 0.0.1 =
* Initial version.

= 0.0.2 =
* Lots of general bug fixes.

== Road Map ==

1. We are looking forward to get your suggestions.


