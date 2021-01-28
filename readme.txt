=== slog ===
Contributors: bobbingwide, vsgloik
Donate link: http://www.oik-plugins.com/oik/oik-donate/
Tags: shortcodes, smart, lazy
Requires at least: 5.6
Tested up to: 5.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Post process oik-bwtrace daily trace summary reports

== Installation ==
1. Upload the contents of the slog plugin to the `/wp-content/plugins/slog' directory
1. Activate the plugin
1. Visit Settings > Slog admin to run the analyses


== Frequently Asked Questions ==

= What is this for? = 

slog provides some basic mechanisms to see what your site's been doing.

Use the sb-chart-block to see the results graphically.
Use the functionality of slog, in conjunction with slog-bloat to measure the effect 
of server changes on your website.

= What is provided? = 
- The Slog admin page, only accessible to authorised users
- This page is only available when oik-bwtrace is activated.
- You can choose to run a range of reports, displaying the output in multiple forms on a chart-block
- The summary data is also shown in a table.


WordPress MultiSite sites add the blog_id suffix to each site other than site 1.  





= What else do I need? =

* oik-bwtrace to produce the files in the first place
* oik-batch ( an alternative to WP-cli ) to drive the routines
* oik-lib, oik and other libraries used by slog
* a charting routine such as visualizer

= How has it been used? =
Originally developed in Herb Miller's play area to help compare performance of different hosting solutions
it was extended at the end of 2015 during the "12 days of Christmas" analysing the effect of the top 12 
WordPress plugins on server execution time. This logic has since been moved into the wp-top12 plugin.

slog ( not quite an abbreviation of "trace log" ) is the generalised version of the code that might enable
others to perform their own analysis.

It is still a bespoke version for use by Herb Miller of bobbingwide/oik-plugins/wp-a2z. 


== Screenshots ==
1. slog in action

== Upgrade Notice ==
= 1.0.0 =
Update for an admin interface with several reports displaying charts and summary tables.

= 0.0.1 = 
Now supports source trace summary files with date format yyyymmdd or mmdd

= 0.0.0 =
New plugin, available from oik-plugins and GitHub

== Changelog == 
= 1.0.0 = 
* Added: Lots of changes

= 0.0.1 = 
* Changed: Support bwtrace.vt.yyyymmdd.site_id files
* Changed: Copied code from the unpublished play folder [github bobbingwide slog issue 1]
* Changed: Use file_get_contents() in order to handle large files [github bobbingwide slog issue 2]
* Added: vt-ip.php to find IPs to block [github bobbingwide slog issue 3]

= 0.0.0 =
* Added: New plugin

