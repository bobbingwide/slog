=== slog ===
Contributors: bobbingwide, vsgloik
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: shortcodes, smart, lazy
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 0.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Post process oik-bwtrace daily trace summary reports

== Installation ==
1. Upload the contents of the slog plugin to the `/wp-content/plugins/slog' directory
1. Activate the slog plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What is this for? = 

slog provides mechanisms to post process daily trace summary report files.

* download for analysis and comparison
* produce summary reports
* use as input to drive performance tests

= What is provided? = 

In version 0.0.0 there are 4 routines:

vt.php -
vt-stats.php -
vt-top12.php - 
vt-driver.php - 

Note: vt comes from the bwtrace.vt.mmdd filename which is so named since it records
value text pairs ( see bw_trace_vt() ).




= What else do I need? =

* oik-bwtrace to produce the files in the first place
* oik-batch ( an alternative to WP-cli ) to drive the routines
* oik-lib, oik and other libraries used by slog
* a charting routine such as visualizer

= How has it been used? =
Originally developed in Herb Miller's play area to help compare performance of different hosting solutions
it was extended at the end of 2015 during the "12 days of Christmas" analysing the effect of the top 12 
WordPress plugins on server execution time. 

slog ( not quite an abbreviation of "trace log" ) is the generalised version of the code that might enable
others to perform their own analysis. 





== Screenshots ==
1. slog in action

== Upgrade Notice ==
= 0.0.0 =
New plugin, available from oik-plugins and GitHub

== Changelog == 
= 0.0.0 =
* Added: New plugin

