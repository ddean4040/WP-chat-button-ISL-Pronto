=== Chat button for ISL Pronto ===
Contributors: ddean
Tags: chat, isl-pronto
Requires at least: 5.0
Tested up to: 5.6
Stable tag: 1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a chat button to your site for use with ISL Pronto. This plugin is not made by ISL Online.

== Description ==

Add a chat button to your site that works with ISL Pronto. Supports cloud or conference proxy installs.

Enable the chat button globally in Settings, or use the `islpronto` shortcode to only have it appear on certain pages.

For most sites, just edit settings under ISL Pronto and enable on all pages for a quick start.


=== Shortcode support === 

The plugin also includes shortcode support in case you want to have visitors on different pages routed to different operator groups, or just to try it out.

`[islpronto domain="" filter="" offlineurl="" imagepath="" scripturl="" position=""]`

* `domain` - Domain name to append to the script URL. Used to direct chat requests to the right operators.
* `filter` - Filter to append to script URL. Used to direct chat requests to the right operators.
* `offlineurl` - The URL visitors should reach when no operators are online.
* `imagepath` - Path to ISL Pronto image files for the chat button.
* `scripturl` - URL of the ISL Pronto script.
* `position` - Where to display the chat button on the page.

== Frequently Asked Questions ==

= What should my script URL be? =

If you are using the cloud service, you don't need to change the script URL.
If you are using a conference proxy, ask your administrator for the right URL.

= My offline image works but the online image is not what I uploaded. Why? =

If you are using a conference proxy, make sure your administrator knows the image path you plan to use.
Also be sure your `domain` value is set correctly.

== Changelog ==

= 1.0 =
* Initial release
