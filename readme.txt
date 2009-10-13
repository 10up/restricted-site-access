=== Restricted Site Access ===
Contributors: Jacob M Goldman (C. Murray Consulting)
Donate link: http://www.cmurrayconsulting.com/software/wordpress-restricted-site-access/
Tags: restricted, limited, permissions, security
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 1.0.2

Limit access to your site to visitors who are logged in or accessing the site from a set of specific IP addresses. 
Send restricted visitors to the log in page, redirect them, or display a message. Powerful control over 
redirection, with option to send to same path and send SEO friendly redirect headers. Great solution for 
Extranets, publicly hosted Intranets, or parallel development sites.


== Description ==

Limit access your site to visitors who are logged in or accessing the site from a set of specific IP addresses. 
Send restricted visitors to the log in page, redirect them, or display a message. A great solution for 
Extranets, publicly hosted Intranets, or parallel development sites.

It includes an easy to use configuration panel inside the WordPress settings menu. From this panel you can:

   1. Enable and disable access restriction at will
   2. Change the restriction behavior: send to login, redirect, or display a message.
   3. Add IP addresses not subject to restriction.
   4. Quickly add your current IP to the restriction list.
   5. Control the redirect location.
   6. Choose to redirect visitors to the same path that they entered the current site on
   7. Choose the HTTP redirect message for SEO friendliness
   
*1.01 is an important update* that improves the fundamental logic pertaining to which areas of the site are restricted. 
The old approach had several subtle, but problematic side effects, which could include blocking scheduled (cron)
events, not passing the "entry" permalink correctly, and similar subtle issues.


== Installation ==

1. Install easily with the WordPress plugin control panel or manually download the plugin and upload the extracted
folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the "Restricted Access" menu item under "Settings"


== Screenshots ==

1. Sceenshot of configuration page.


== Changelog ==

v1.0.1 - Important fundamental change related to handling of what should be restricted

v1.0.2 - Fix login redirect to home; improve redirect handling to take advantage of wp_redirect function


== Coming soon ==

Planned enhancements:

	1. Customize restricted access message
	2. Restriction based on user level (vs is logged in)
	3. Exclude pages or posts from restrictions
	4. Enter IP ranges