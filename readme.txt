=== Restricted Site Access ===
Contributors: Jacob M Goldman (C. Murray Consulting), Eric Buth
Donate link: http://www.cmurrayconsulting.com/software/wordpress-restricted-site-access/
Tags: restricted, restrict, limited, permissions, security, block
Requires at least: 2.8
Tested up to: 2.9.1
Stable tag: 2.1

Limit access to visitors who are logged in or at specific IP addresses. Many options for handling blocked visitors. 
Great for Intranets, dev sites.


== Description ==

Limit access your site to visitors who are logged in or accessing the site from a set of specific IP addresses. 
Send restricted visitors to the log in page, redirect them, or display a message. A great solution for 
Extranets, publicly hosted Intranets, or parallel development sites.

It includes an easy to use configuration panel inside the WordPress settings menu. From this panel you can:

1. Enable and disable access restriction at will.
1. Change the restriction behavior: send to login, redirect, or display a message.
1. Add IP addresses not subject to restriction, including ranges.
1. Quickly add your current IP to the restriction list.
1. Control the redirect location.
1. Choose to redirect visitors to the same path that they entered the current site on.
1. Choose the HTTP redirect message for SEO friendliness.
1. Customize the blocked visitor message.
   
Version 2.0 is a major update. In addition to adding IP range support, there are significant UI and usability 
improvements, and many other under the hood improvements to the code base.
   
Requires PHP 5.1+ to support IPv6 ranges. Download version 1.0.2 if IP ranges are not needed and the host is not
running PHP 5.1 or newer.


== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted
folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the "Restricted Access" menu item under "Settings"


== Screenshots ==

1. Sceenshot of configuration page.


== Changelog ==

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