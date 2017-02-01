# slog 
![banner](https://raw.githubusercontent.com/bobbingwide/slog/master/assets/slog-banner-772x250.jpg)
* Contributors: bobbingwide, vsgloik
* Donate link: http://www.oik-plugins.com/oik/oik-donate/
* Tags: shortcodes, smart, lazy
* Requires at least: 4.4
* Tested up to: 4.7.2
* Stable tag: 0.0.1
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
Post process oik-bwtrace daily trace summary reports

## Installation 
1. Upload the contents of the slog plugin to the `/wp-content/plugins/slog' directory
1. Run the routines using oik-batch

## Frequently Asked Questions 

# What is this for? 

slog provides some basic mechanisms to post process daily trace summary report files.
In combination with wp-top12 the routines can be used to:

* download trace summary files for analysis and comparison
* produce summary reports
* use as input to drive performance tests
* Find IPs to block

# What is provided? 

In version 0.0.1 there are 3 routines.

plugin    | file         | purpose
--------- | ------------ | ----------
slog      | vt-ip.php    | Find IPs to block by counting the number of login requests per IP
slog      | getvt.php    | Download bwtrace.vt.yyyymmdd files for post processing
slog      | vt.php       | Post process trace files


wp-top12 provides more

plugin    | file         | purpose
--------- | ------------ | ----------


wp-top12  | getvt.php    | Download bwtrace.vt.mmdd files for post processing
wp-top12  | vt-stats.php | Summarise stats for a date range
wp-top12  | vt-top12.php | Perform performance analysis comparing multiple log files
wp-top12  | vt-driver.php

* Note: vt comes from the bwtrace.vt.mmdd filename which is so named since it records
value text pairs ( see bw_trace_vt() ).

WordPress MultiSite sites add the blog_id suffix to each site other than site 1.





# What else do I need? 

* oik-bwtrace to produce the files in the first place
* oik-batch ( an alternative to WP-cli ) to drive the routines
* oik-lib, oik and other libraries used by slog
* a charting routine such as visualizer

# How has it been used? 
Originally developed in Herb Miller's play area to help compare performance of different hosting solutions
it was extended at the end of 2015 during the "12 days of Christmas" analysing the effect of the top 12
WordPress plugins on server execution time. This logic has since been moved into the wp-top12 plugin.

slog ( not quite an abbreviation of "trace log" ) is the generalised version of the code that might enable
others to perform their own analysis.

It is still a bespoke version for use by Herb Miller of bobbingwide/oik-plugins/wp-a2z.


## Screenshots 
1. slog in action

## Upgrade Notice 
# 0.0.1 
Now supports source trace summary files with date format yyyymmdd or mmdd

# 0.0.0 
New plugin, available from oik-plugins and GitHub

## Changelog 
# 0.0.1 
* Changed: Support bwtrace.vt.yyyymmdd.site_id files
* Changed: Copied code from the unpublished play folder https://github.com/bobbingwide/slog/issues/1
* Changed: Use file_get_contents() in order to handle large files https://github.com/bobbingwide/slog/issues/2
* Added: vt-ip.php to find IPs to block https://github.com/bobbingwide/slog/issues/3

# 0.0.0 
* Added: New plugin

