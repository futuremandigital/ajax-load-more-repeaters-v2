=== Ajax Load More: Custom Repeaters v2 ===

Contributors: dcooney
Author: Darren Cooney
Author URI: https://connekthq.com/
Plugin URI: https://connekthq.com/ajax-load-more/add-ons/custom-repeaters/
Requires at least: 3.6.1
Tested up to: 5.0
Stable tag: trunk
Homepage: https://connekthq.com/ajax-load-more/
Version: 2.5.3

== Copyright ==
Copyright 2018 Darren Cooney, Connekt Media

This software is NOT to be distributed, but can be INCLUDED in WP themes: Premium or Contracted.
This software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.


== Description ==

= Unlock additional repeaters and keep your site looking and feeling fresh! =

Ajax Load More’s Unlimited Repeaters add-on will unlock the ability to create an infinite number repeater templates.

http://connekthq.com/ajax-load-more/custom-repeaters/

== Installation ==

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `ajax-load-more-repeaters-v2.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `ajax-load-more-repeaters-v2.zip`
2. Extract the `ajax-load-more-repeaters-v2.zip` to your computer
3. Upload the `ajax-load-more-repeaters-v2` directory to the `/wp-content/plugins/` directory
4. Ensure Ajax Load More is installed prior to activating the repeater plugin
5. Activate the plugin in the Plugin dashboard



== Changelog ==

= 2.5.2 - November 11, 2018 =
* FIX - Fixed issue with action not running in 2.5.2.


= 2.5.2 - November 10, 2018 =
* NEW - Added new function that checks for `alm_unlimited` custom table existance.
* UPDATE - Updated `mkdir` function to use core WP function `wp_mkdir_p` for better permission setting on directory creation.


= 2.5.1 - August 24, 2018 =
* UPDATE - Updated the repeater template admin layout to match new styling in core Ajax Load More.


= 2.5 - June 20, 2018 =

#### UPDATE NOTICE
Please update to Ajax Load More 3.5.1 (or greater) prior to updating Custom Repeaters v2.

This 2.5 update contains a major change to how the repeater templates are saved and displayed.
On update, your Repeater Templates will be moved from `plugins/ajax-load-more-repeaters-v2/repeaters` to the `alm_templates` directory within `wp-content/uploads`.
This is a long overdue enhancement and I highly recommend you backup your site (or Repeater Templates) prior to updating in case permission issues occur during the upgrade process.

* UPDATE - Updated the directory ALM stores Repeater Template files. Templates are now saved to the WP Uploads directory (`uploads/alm_templates/`).
* UPDATE - Removed vendor files as they are now included in core ALM.
* UPDATE - Code cleanup, UI updates.


= 2.4.2 =
* UPDATE - Adding support new Layouts add-on. ALM 2.9 is required.


= 2.4.1 =
* UPDATE - Security fix for Ajax Load More 2.8.1.2

= 2.4 =
* NEW - Adding multisite support for repeater templates - if using a multisite, please deactivate then re-activate Ajax Load More - Custom Repeaters v2.
* NEW - Completely rebuilt update script for repeater templates to be more efficient and integrate with multisite installations.

= 2.3.1 =
* UPDATE - Updating plugin update script. Users are now required to input a license key to receive updates directly within the WP Admin. Please contact us for information regarding legacy license keys.

= 2.3 =
* Adding functionality required for the Ajax Load More v2.5.0 update.
* Be sure to update to ALM v2.5.0 before updating to Custom Repeaters v2.3

= 2.2 =
* Fixed issue with template variable naming.

= 2.1 =
* Fixed issue with saving of custom repeaters due to incorrect variable name.

= 2.0 =
* Rebuilt. From the ground up!
