=== Simple Analytics ===

Author URI: http://themeblvd.com
Contributors: themeblvd
Tags: google, analytics, tracking, Theme Blvd, themeblvd, Jason Bobich
Stable Tag: 1.1.1

A simple plugin to include your Google Analytics tracking.

== Description ==

This plugin allows you to quickly include your Google Analytics tracking. After installing the plugin, just go to *Settings > Analytics* and input your Google Analytics Tracking ID.

[vimeo https://vimeo.com/119387977]

== Installation ==

1. Upload `simple-analytics` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to *Settings > Analytics* to configure.

*Note: Tracking code will not be active for the logged-in admin user.*

== Screenshots ==

1. The settings page at Settings > Analytics.

== Changelog ==

= 1.1.1 =

= 1.1.0 =

* New: To avoid confusion, instead of *not* outputting analytics code in website source for the logged-in admin user, it is now outputted but commented out with explanation.
* New: Added option to [anonymize IP's](https://support.google.com/analytics/answer/2763052).

= 1.0.3 =

* Fixed: Typo in plugin description.

= 1.0.2 =

* Added option to include tracking immediately after opening `<body>` tag (only for Theme Blvd themes).

= 1.0.1 =

* Updated output to latest Google Analytics tracking code (as of February, 2015).
* GlotPress compatibility (for 2015 wordpress.org release).

= 1.0.0 =

* This is the first release.
