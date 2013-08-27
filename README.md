# ox-digital-signage
## The University of Oxford Digital Signage System
Copyright: [University of Oxford IT Services](http://www.it.ox.ac.uk)  
Contributors: [Guido Klingbeil](http://www.gklingbeil.net), [Marko Jung](http://mjung.net)  
Tags: shortcode, posts, custom post types, digital signage  
Requires at least: 3.0  
Tested up to: 3.5.1    
Stable tag: trunk  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.html  
GitHub URI: https://github.com/ox-it/ox-digital-signage


## Short Description

Displays a single post of a series of posts as digital signs in a page.


## Description 
Displays a single post from a given category in a page as digital sign. The post is selected in a round robin fashion where the user is able to specify that only the first n posts of the category are to be circled. 


## Notes

End user and system maintainer documentation including how to set-up a working digital signage system can be found in the `docs` folder of this plugin.

This is a minimal plugin, function over form.  If you would like to extend it, or would like us to extend it in later versions, please post feature in the plugin's [GitHub page](https://github.com/ox-it/ox-digital-signage).

This plugin is build upon the [posts_in_page](http://wordpress.org/extend/plugins/posts-in-page) plugin by *dgilfoy*, *ivycat*, and *sewmyheadon* and the [Auto Refresh Single Page](http://wordpress.org/extend/plugins/auto-refresh-single-page) plugin by *jkohlbach*. We would like to thank the authors for releasing their work as free software.


## Installation

This plugin is not published in the official WordPress plugin catalogue yet. If you wish to manually install it:

1. Download the plugin from [GitHub](https://github.com/ox-it/ox-digital-signage),
1. Upload the entire `ox-digital-signage` directory to your plugins folder, 
1. Activate the plugin in your WordPress plugin page,
1. Start using your posts as digital signs by using the short code on pages.


## Usage

Pages can be transformed into a digital sign by adding the `[oxds_add_sign]` short code to them.

The user may specify options for the plugin in a sidebar widget (default value):

* category: The category of the posts to be displayed.
* refresh time (20 sec.): The time in seconds each post is displayed.
* number of posts (10): Display the first n posts of the given category in a round robin fashion. 


## Frequently Asked Questions
We are happy answer any questions to the best of our knowledge.

#### What is the point of this plugin?
We were looking for an easy to set-up and maintain digital signage (http://en.wikipedia.org/wiki/Digital_signage) solution to be connected to a CMS (content management system).

#### How do I change the output template
Simply copy the posts_loop_template.php to your theme directory and make changes as necessary. You can even rename it - but make sure to indicate that in the shortcode using the `template='template_name.php'`. You can even use multiple layouts for each shortcode if you like.


## Changelog
* 0.4
  * Added support for fullscreen priority posts and image posts. The plugin supports three types for digital signs: (i) the
    default sign, (ii) a fullscreen priority sign, and (iii) an image sign. A fullscreen priority sign takes the entire screen. 
    If a published and scheduled fullscreen priority sign exists for a given screen, only this one will be displayed. 
    If there are more than one fullscreen priority signs, the youngest one is displayed.
* 0.3
  * New defaults,
  * Added documentation.
* 0.2
  * Lots of general bug fixes.
* 0.1
  * Initial version.


## Road Map

This plug-in as from our perspective feature complete. However, we are looking forward to hear your suggestions.


