=== Restricted Site Access ===
Contributors: jakemgold, rcbth
Donate link: http://www.thinkoomph.com/plugins-modules/wordpress-restricted-site-access/
Tags: privacy, restricted, restrict, limited, permissions, security, block
Requires at least: 2.8
Tested up to: 3.1
Stable tag: 3.2.1

Limit access to visitors who are logged in or at specific IP addresses. Many options for handling blocked visitors. 
Great for Intranets, dev sites.


== Description ==

Limit access your site to visitors who are logged in or accessing the site from a set of specific IP addresses. 
Send restricted visitors to the log in page, redirect them, or display a message. A great solution for 
Extranets, publicly hosted Intranets, or parallel development sites.

Adds a number of new configuration options to the Privacy settings panel. From this panel you can:

1. Enable or disable site access restriction
1. Change the restriction behavior: send to login, redirect, or display a message
1. Add IP addresses not subject to restriction, including ranges
1. Quickly add your current IP to the restriction list
1. Control the redirect location
1. Choose to redirect visitors to the same requested path
1. Choose the HTTP redirect message for SEO friendliness
1. Customize the blocked visitor message


== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted
folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the "Privacy" menu under "Settings"


== Screenshots ==

1. Sceenshot of configuration page.


== Changelog ==

= 3.2.1 =
* Restored PHP4 compatibility

= 3.2 =
* More meaningful page title in "Display Message" mode (previously WordPress > Error)
* Code clean up, prevent rare warnings in debug mode

= 3.1.1 =
* Fixed PHP warning when debugging is enabled and redirect path is not checked

= 3.1 =
* New feature: backwards compatibility with PHP < 5.1 (limited testing with earlier versions)
* Bug fix: disappearing blocked access message text box on configuration page
* Bug fix: login always redirects visitor back to correct page
* Improved: built in help on configuration page updated, clearer
* Improved: "IP already in list" indicator
* Improved: optimizations to code that handles restriction behavior

= 3.0 =
* Integrates with Privacy settings page and site visibility option instead of adding a whole new page
* Simplified options: clearer instructions, removed unnecessary hiding / showing of some options, fewer lines
* Indicates whether the site is blocked in the admin next to the site title (WordPress 3.0+ only)
* New action hook, `restrict_site_access_handling`, allowing developers to add their own restriction handling
* Cleans up / removes settings when uninstalled
* Assorted under the hood improvements for best coding practices, sanitization of options, etc

= 2.1 =
* Customize blocked visitor message
* Stronger security (patched "search" hole)
* Better display / handling of blocked visitor message

= 2.0 =
* Add support for IP ranges courtesy Eric Buth
* Major UI changes and improvements; major code improvements

= 1.0.2 =
* Fix login redirect to home; improve redirect handling to take advantage of wp_redirect function

= 1.0.1 =
* Important fundamental change related to handling of what should be restricted

= Planned enhancements =
* Restriction based on user level (vs is logged in)
* Exclude pages or posts from restrictions