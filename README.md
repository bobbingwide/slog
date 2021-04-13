# slog 
![banner](https://raw.githubusercontent.com/bobbingwide/slog/master/assets/slog-banner-772x250.jpg)
* Contributors: bobbingwide, vsgloik
* Donate link: https://www.oik-plugins.com/oik/oik-donate/
* Tags: trace, summary, performance, analysis
* Requires at least: 5.6
* Tested up to: 5.7
* Stable tag: 1.4.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
Analyse oik-bwtrace daily trace summary reports.

Making use of oik-bwtrace's daily trace summary reports, Slog provides some mechanisms to see what your site's been doing.

Use this to determine the effect of activating / deactivating a plugin on server side performance.

The Slog plugin is a generic solution to enable performance comparison
of server responses with different server configurations.

Slog admin tabs are:

Function | Processing
------- | ----------
Reports | Produce a variety of reports for a single daily trace summary file.
Compare | Produce comparison charts for two or more trace summary files.
Filter | Filter a daily trace summary file.
Download | Download a daily trace summary file from a remote host site.
Driver | Run a series of requests against the server URL.
Search | Search trace files for a selected string.
Settings | Define default/initial settings for the reports.

### Reports 
Reports - View the daily trace output grouped and summarised in a variety of ways.


- Use the sb-chart-block plugin to visualise the data in a chart.
- The summary data is also shown in a table.

### Compare 
Compares the output of two or more daily trace summary downloads / filtered files.

Requires sb-chart-block.

Use this to visualise the effect of activating / deactivating a plugin on server side performance.

### Filter 
The purpose of Filtering is to reduce a daily trace summary file to a subset of requests that allow
better comparison of multiple files.

Examples:
- Reasonable responses < x.y secs
- Only GET requests performed on the front-end ( FE ) by real users, not bots ( BOT ).
- Only requests which resulted in a 200( OK ) HTTP response code.

### Download 
Use the Download tab to download a daily trace summary file.

This will only work if the file is accessible to any browser.
If the file is protected from general access, returning a 403 or otherwise, then you'll need to download the file
by another mechanism. eg FTP or from your site's control panel.

### Driver 
Drives a series of requests to the server.
Use this when you want to measure/compare server response time over a number of requests.

### Search 
Allows you to search multiple trace files for any string.
Produces a link to each file searched.
Search may produce a lot of output.

### Settings 
Use the Settings tab to define default values to be used in the other forms.


## Usage 

- The Slog admin page is only accessible to authorised users.
- This page is only available when oik-bwtrace is activated.
- To see the results graphically you need the sb-chart-block plugin.


## Installation 
1. Upload the contents of the slog plugin to the `/wp-content/plugins/slog' directory
1. Activate the plugin
1. Visit Settings > Slog admin Settings tab to define your defaults

## Frequently Asked Questions 

# What else do I need? 

* oik-bwtrace to produce the daily trace summary files in the first place.
* sb-chart-block to display the charts.

# How do I measure the effect of changes? 

Use the Compare tab to visualise the effect of changes by comparing two or more trace summary files created with
different configurations.

# Does it work on WordPress Multi Site? 

- WordPress MultiSite sites add the blog_id suffix to each site other than site 1.
- The results you get on a particular site depend on the daily trace summary prefix.
- This defaults to `bwtrace.vt`

# Does it profile an individual request? 

No. If you want to profile individual requests then you will need more granular information.


# How has it been used? 

- Slog was originally developed to help compare the performance of 3 different hosting solutions.
- It was extended at the end of 2015 during the "12 days of Christmas" to analyse the effect of the top 12
WordPress plugins on server execution time.
- A lot of data was produced, but the charts were never published.
- In subsequent years I compared the performance of different versions of WordPress 4.4 through 4.7
- The recent improvements have been developed to help measure the effect of selected plugins on server side performance.

slog, which is a contraction of "trace log", is the generalised version of the code that might enable
others to perform their own analysis.

For other bespoke routines to analyse daily trace summary files see the slog-bloat plugin.

## Screenshots 
1. Slog admin > Reports tab - Form
2. Slog admin > Reports tab - Chart
3. Slog admin > Reports tab - Table
4. Slog admin > Compare tab
5. Slog admin > Download tab
6. Slog admin > Filter tab
7. Slog admin > Settings tab
8. Slog admin > Driver tab
9. Slog admin > Search tab

## Upgrade Notice 
# 1.4.0 
Upgrade for the prototype Search facility.

# 1.3.1 
Add 100th second interval. Set elapsed limit to 1 second. Added copy CSV to clipboard button.

# 1.3.0 
Adds the Driver tab.

# 1.2.1 
Elapsed report supports greater interval granularity.

# 1.2.0 
Now includes logic merged from slog-bloat.

# 1.1.3 
Improved the Report title.

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
# 1.4.0 
* Added: Search tab,https://github.com/bobbingwide/slog/issues/23
* Tested: With WordPress 5.7 and WordPress Multi Site
* Tested: With PHP 8.0

# 1.3.1 
* Added: Ability to set the Interval and Elapsed limit,https://github.com/bobbingwide/slog/issues/18
* Added: Copy CSV to clipboard button,https://github.com/bobbingwide/slog/issues/20
* Changed: Compare up to 15 files.
* Changed: Format table numbers with 6 decimal places.

# 1.3.0 
* Added: First pass adding a Driver tab to run a request multiple times.,https://github.com/bobbingwide/slog/issues/19
* Tested: With WordPress 5.6.1 and WordPress Multi Site
* Tested: With PHP 7.4

# 1.2.1 
* Changed: Support various intervals for Elapsed report,https://github.com/bobbingwide/slog/issues/18

# 1.2.0 
* Changed: Merged the admin page from slog-bloat,https://github.com/bobbingwide/slog/issues/16

# 1.1.3 
* Changed: Updated the Report title display.

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
