# slog 
![banner](https://raw.githubusercontent.com/bobbingwide/slog/master/assets/slog-banner-772x250.jpg)
* Contributors: bobbingwide, vsgloik
* Donate link: http://www.oik-plugins.com/oik/oik-donate/
* Tags: shortcodes, smart, lazy
* Requires at least: 5.6
* Tested up to: 5.6
* Stable tag: 1.1.2
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
oik-bwtrace daily trace summary reports.

slog provides some basic mechanisms to see what your site's been doing.

Use with the sb-chart-block to see the results graphically.

- The Slog admin page is only accessible to authorised users.
- This page is only available when oik-bwtrace is activated.
- You can choose to run a range of reports, displaying the output in multiple forms on a chart.
- The summary data is also shown in a table.

## Installation 
1. Upload the contents of the slog plugin to the `/wp-content/plugins/slog' directory
1. Activate the plugin
1. Visit Settings > Slog admin to run the analyses


## Frequently Asked Questions 

# What else do I need? 

* oik-bwtrace to produce the daily trace summary files in the first place
* sb-chart-block to display the charts

# How do I measure the effect of changes? 

Use the functionality of slog, in conjunction with slog-bloat to measure the effect
of server changes on your website.

# Does it work on WordPress Multi Site? 

- WordPress MultiSite sites add the blog_id suffix to each site other than site 1.
- The results you get on a particular site depend on the daily trace summary prefix.
- This defaults to `bwtrace.vt`


# How has it been used? 

Originally developed in Herb Miller's play area to help compare performance of different hosting solutions
it was extended at the end of 2015 during the "12 days of Christmas" analysing the effect of the top 12
WordPress plugins on server execution time. This logic has since been moved into the wp-top12 plugin.

slog ( not quite an abbreviation of "trace log" ) is the generalised version of the code that might enable
others to perform their own analysis.

It is still a bespoke version for use by Herb Miller on bobbingwide, oik-plugins and wp-a2z.


## Screenshots 
1. slog in action

## Upgrade Notice 
# 1.1.2 
Update for improved resilience.

# 1.1.1 
Update for automatic filtering, if required.

# 1.1.0 
Upgrade for Horizontal bar charts and other improvements related to slog-bloat.

# 0.0.1 
Now supports source trace summary files with date format yyyymmdd or mmdd

# 0.0.0 
New plugin, available from oik-plugins and GitHub

## Changelog 
# 1.1.2 
* Changed: Don't run reports when the selected file is missing or there are 0 loaded rows,https://github.com/bobbingwide/issues/11

# 1.1.1 
* Changed: Apply slog bloat automatic filters if required,https://github.com/bobbingwide/slog-bloat/issues/3
* Fixed: Avoid Warning when running Elapsed report against badly formed records,https://github.com/bobbingwide/slog/issues/15

# 1.1.0 
* Added: Add Horizontal bar chart. Remove pompey_chart logic,https://github.com/bobbingwide/slog/issues/13
* Changed: Extend slog's file list to include files in slog-bloat's download directory,https://github.com/bobbingwide/slog-bloat/issues/4
* Changed: Improve filtering on request type and http response code,https://github.com/bobbingwide/slog-bloat/issues/3
* Changed: Use asCSV_field_key() method for displaying the key - future use?,https://github.com/bobbingwide/slog/issues/2

# 1.0.0 
* Added: Lots of changes - see Git commit history ?

# 0.0.1 
* Changed: Support bwtrace.vt.yyyymmdd.site_id files
* Changed: Copied code from the unpublished play folder https://github.com/bobbingwide/slog/issues/1
* Changed: Use file_get_contents() in order to handle large files https://github.com/bobbingwide/slog/issues/2
* Added: vt-ip.php to find IPs to block https://github.com/bobbingwide/slog/issues/3

# 0.0.0 
* Added: New plugin

