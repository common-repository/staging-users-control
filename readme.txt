=== Plugin Name ===
Contributors: Yassineh
Donate link: None.
Tags: user control, staging, live, wpengine
Requires at least: 3.5.1
Tested up to: 3.9.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to prevent certain users from logging into the back-end in the LIVE environment. You can let your users edit staging but not live.

== Description ==

Since we've been using WPEngine we found no way to limit access to the LIVE website. We had problems with users mistakenly editing the live website and messing up the HTML. This plugin basically adds a checkbox to the users (allow login on LIVE) and compare the get_bloginfo('url') to the one setup on the plugin's setting page. If it matches, it means it's live environment and will prevent users who are not specifically authorized to login into the live environment. You can bypass this check (in case you log yourself out of live) by setting up a simple cookie. This is not a security plugin, this is just to avoid stupid mistakes. The URL meta value is base64 encoded before storage to avoid the Search and Replace generally done by the "One Click" live push.

== Installation ==

1. Upload `staging-users-control.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. IMPORTANT: First edit the users you want to allow on the LIVE environment.
4. Setup the live environment URL under Settings > Staging Users Control
5. Enjoy.

== Screenshots ==

1. This is the plugin settings page, under Settings > Staging Users Control.
2. This is the users profile page, showing the new checkbox to allow users to login into the LIVE env.
3. You can show a message on the live site for users to notice they are logging on the LIVE site.

== Frequently Asked Questions ==

= I logged myself out of the website because I didn't allow any user to login to the live site =

Just setup a COOKIE with the name "SUC-LOGMELIVE" and the specified value in the settings page if you want to bypass the plugin.
You can retrieve this value under Settings > Staging Users Control on your staging website.

= I did setup everything correctly but the plugin does not seem to be recognizing the live site. =

Sometimes character encoding can mess things up.
In the case of WP Engine, every time you push your site live, it replaces the value in get_bloginfo('url'). To work around this problem, you can use URL comparison by checking "Compare URL" in the plugin's settings page. Make sure to match the protocol (http/https).

== Changelog ==

= 1.0 =
* Initial Release


== Upgrade Notice ==


== Arbitrary section ==

Please let me know if you have any suggestions or questions.