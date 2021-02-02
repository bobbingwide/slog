<?php // (C) Copyright Bobbing Wide 2015-2021

/*
Plugin Name: slog
Plugin URI: https://www.bobbingwide.com/oik-plugins/slog
Description: Post process oik-bwtrace daily trace summary reports
Version: 1.0.0
Author: bobbingwide
Author URI: http://www.bobbingwide.com/about-bobbing-wide
Text Domain: slog
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2015-2021 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

slog_loaded();


/**
 * Processing when the plugin file is loaded.
 * 
 * This plugin is only run under the command line.
 */
function slog_loaded() {
	add_action( 'init', 'slog_init', 21 );
	add_action( 'admin_menu', 'slog_admin_menu', 21 );
	add_action( 'admin_enqueue_scripts', 'slog_admin_enqueue_scripts' );
}

/**
 * Implements 'init' hook for slog.
 *
 */
function slog_init() {
	/**
	 * Slog doesn't really need to do this since oik-trace should have already done it, if activated.
	 * This is belt and braces.
	 */
	if ( !function_exists( 'oik_require' ) ) {
		// check that oik v2.6 (or higher) is available.
		$oik_boot = dirname( __FILE__ ). "/libs/oik_boot.php";
		if ( file_exists( $oik_boot ) ) {
			require_once( $oik_boot );

		}
	}
	$libs = oik_lib_fallback( dirname( __FILE__ ) . '/libs' );
	//print_r( $libs );
	do_action( 'slog_loaded');



	add_action( 'admin_init', 'slog_options_init' );
}

function slog_admin_enqueue_scripts() {
	if ( function_exists( 'sb_chart_block_enqueue_scripts') ) {
		sb_chart_block_enqueue_scripts();
	} elseif ( function_exists( 'pompey_chart_enqueue_scripts') ) {
		pompey_chart_enqueue_scripts();
	}
}

/**
 * Note: slog is dependent upon oik-bwtrace which itself uses & delivers the shared library files we need.
 */

function slog_admin_menu() {
	if ( oik_require_lib( "oik-admin" ) ) {
		$hook=add_options_page( "Slog admin", "Slog admin", "manage_options", "slog", "slog_admin_page" );
	} else {
		//bw_trace2( "Slog admin not possible");
	}
}

/**
 * Slog admin page.
 * - Form
 * - Chart
 * - CSV Table
 * In whatever order seems most appropriate.
 */

function slog_admin_page() {
	BW_::oik_menu_header( __( "Slog", "slog" ), 'w100pc' );


	BW_::oik_box( null, null, __( "Form", "slog" ) , "slog_admin_form" );
	BW_::oik_box( null, null, __( "Chart", "slog" ), "slog_admin_chart" );
	BW_::oik_box( null, null, __( "CSV table", "slog" ), "slog_admin_table" );
	oik_menu_footer();
	bw_flush();
}


/**
 * Register slog_options
 *
 */
function slog_options_init(){
	register_setting( 'slog_options_options', 'slog_options', 'slog_options_validate' );
}

function slog_options_validate( $input ) {
	return $input;
}

/**
 * Display the Chart settings form
 *
 *Name | Selection fields | Values
----- | ----------------- | -----------
file | File to analyse   | List of daily trace summary files
report | Report              |  See Options below
type | Chart type | Line, Bar, Pie
display | Display values | Count,  Total Elapsed, Percentage, Accumulated percentage
having | Having      | Variable for selecting
 */
function slog_admin_form() {
	bw_form('options.php');
	$options = get_option('slog_options');
	stag( 'table', 'form-table' );
	bw_flush();
	settings_fields('slog_options_options');
	$file_options = slog_admin_file_options();
	$report_options = slog_admin_report_options();
	$type_options = slog_admin_type_options();
	$display_options = slog_admin_display_options();
	BW_::bw_select_arr( 'slog_options', __('Trace summary file'), $options, 'file', array( '#options' => $file_options ) );

	//BW_::bw_textfield_arr( 'slog_options', __( 'File', 'slog' ), $options, 'file', 60 );
	BW_::bw_select_arr( 'slog_options', __( 'Report type', 'slog' ), $options, 'report', array( "#options" => $report_options ) );
	BW_::bw_select_arr( 'slog_options', __( 'Chart type', 'slog' ), $options, 'type', array( "#options" => $type_options ) );
	BW_::bw_select_arr( 'slog_options', __( "Display", 'slog' ), $options, 'display', array( "#options" => $display_options ) );
	BW_::bw_textfield_arr( 'slog_options', __( 'Having', 'slog'), $options, 'having', 10 );

	etag( "table" );
	BW_::p( isubmit( "ok", __( "Save and run", 'slog' ), null, "button-primary" ) );
	etag( "form" );
	bw_flush();
}

/**
 *
 * @TODO Autoload the trace classes from oik-bwtrace/includes
 * @return null
 */
function slog_admin_get_trace_files_directory() {
	oik_require( "includes/class-trace-logs.php", "oik-bwtrace" );
	$trace_logs = new trace_logs();
	$fq_trace_files_directory = $trace_logs->get_fq_trace_files_directory();
	//echo $fq_trace_files_directory;
	return $fq_trace_files_directory;
}

function slog_admin_get_file_list( $dir, $mask ) {
	$file_options = [];
	// Use the daily trace summary report directory.
	// @TODO Use the daily trace report file prefix too
	$files = glob(  $dir . $mask );
	foreach ( $files as $file ) {
		$basename = basename( $file );
		$file_options[$file] = $basename;
	}
	arsort( $file_options );
	//print_r( $files );
	return $file_options;
}

/**
 *
 */
function slog_admin_get_trace_summary_prefix() {
	$prefix = 'bwtrace.vt';
	//get_summary_file_prefix()
	return $prefix;
}

/**
 * Lists the daily trace summary files that may be analysed.
 *
 * Files can either be the local daily trace summary files, matching the current value for the summary file prefix
 * or the downloaded / filtered files
 */
function slog_admin_file_options() {
	$dir = slog_admin_get_trace_files_directory();
	$prefix = slog_admin_get_trace_summary_prefix();
	$mask = $prefix . '*';
	//$mask = <input type="text" size="60" name="bw_summary_options[summary_file]" id="bw_summary_options[summary_file]" value="cwiccer" class="">
	//echo $dir;
	$trace_summary_files = slog_admin_get_file_list( $dir, $mask );
	$slog_bloat_dir = bobbcomp::bw_get_option( '_slog_downloads_dir', 'slog_bloat_options' );
	if ( $slog_bloat_dir ) {
		//echo $slog_bloat_dir;
		$slog_bloat_dir = trailingslashit( $slog_bloat_dir );
		$slog_bloat_files=slog_admin_get_file_list( $slog_bloat_dir, '*.*' );
	//print_r( $slog_bloat_files);
	} else {
		$slog_bloat_files = [];
	}
	$file_options = array_merge( $trace_summary_files, $slog_bloat_files );
	return $file_options;
}
/**
 * Lists the available Chart types.
 *
 * @TODO Extend to Stacked Bar and other variations possible using options.
 *
 * @return array
 */

function slog_admin_type_options() {
	$types  = [ 'line' => __( "Line", 'slog' ),
		'bar' => __( 'Bar', 'slog' ),
		'horizontalBar' => __( 'Horizontal bar', 'slog'),
		'pie' => __( 'Pie', 'slog' )
		];
	return $types;
}

/**
 * Return the fields as well as the programmatically supported request types.
 *
 * @return string[]
 */

function slog_admin_report_options() {
	$reports = [ 'request_types' => __( 'Request types', 'slog' ),
		'suri' => __( 'Stripped Request URIs', 'slog' ),
		'suritl' => __( 'Stripped Request URIs Top Level', 'slog'),
		'hooks' => __( 'Hook counts', 'slog' ),
		'remote_IP' => __( 'Remote IP', 'slog' ),
		'elapsed' => __( 'Elapsed', 'slog')
		];
	return $reports;
}

/**
 * Returns the list of display options.
 *
 * Option | Meaning
 * ------ | --------
 * count | Count of the requests in this grouping
 * elapsed | Total elapsed time of the requests in this grouping
 * average | Average elapsed time of the requests in this grouping
 * percentage_count | Percentage of the total requests in this grouping
 * percentage_elapsed | Percentage of the total elapsed time of the requests in this grouping
 * percentage_count_accumulative | Accumulated percentage of the counts
 * percentage_elapsed_accumulative | Accumulated percentage of the total elapsed time
 */
function slog_admin_display_options() {
	$display = [ 'count' => __( 'Count', 'slog')
	, 'elapsed' => __( 'Elapsed', 'slog')
	, 'average' => __( 'Average', 'slog')
	, 'percentage_count' => __( 'Percentage count', 'slog')
	, 'percentage_elapsed' => __( 'Percentage elapsed', 'slog')
	, 'percentage_count_accumulative' => __( 'Accumulated count percentage', 'slog')
	, 'percentage_elapsed_accumulative' => __( 'Accumulated elapsed percentage', 'slog')
	];
	return $display;
}

/**
 * Displays the selected chart.
 *
 * - Options come from `slog_options`.
 * - Data comes from the selected trace file.
 * - We call wp-top12 routines to obtain the raw CSV data
 * - Which is then passed to the [chart] shortcode.
 *
 */

function slog_admin_chart() {
	slog_enable_autoload();
	BW_::p("Admin chart");
	$atts = slog_admin_chart_atts();
	$content = slog_admin_chart_content();
	if ( function_exists( 'sb_chart_block_shortcode') ) {
		//sb_chart_block_shortcode( $atts, $content );
		$output = sb_chart_block_shortcode( $atts, $content, 'chartjs');
		e( $output );
	} else {
		BW_::p( 'Install and activate sb-chart-block');
		//echo 'Install and activate sb-chart-block';
	}
	bw_flush();
}

function slog_admin_chart_atts() {
	$options = get_option( 'slog_options');
	//print_r( $options );
	$atts = [];
	$atts[ 'type' ] = bw_array_get( $options, 'type');
	//$atts['height'] = '400px';
	$atts['class'] = 'none';
	// How do we pass stackBars and other options?
	return $atts;
}

/**
 * Enables autoload processing using shared library classes.
 *
 */
function slog_enable_autoload() {

	$lib_autoload = oik_require_lib( 'oik-autoload');
	if ( $lib_autoload && !is_wp_error( $lib_autoload ) ) {
		oik_autoload( true );
	} else {
		BW_::p( "oik-autoload library not loaded");
	}
}

function slog_admin_chart_content() {
	$options = get_option( 'slog_options');

	// Can we enable autoload processing here?
	// What's the benefit?
	$lib_autoload =oik_require_lib( 'oik-autoload');
	if ( $lib_autoload && !is_wp_error( $lib_autoload ) ) {
		oik_autoload();
	} else {
		BW_::p( "oik-autoload library not loaded");


	}
	bw_flush();

	//oik_require( 'class-slog-reporter.php', 'slog' );
	$slogger = slog_admin_slog_reporter();
	$content = $slogger->run_report( $options );
	//slog_getset_content( $content);
	//$content = "A,B,C\n1,2,3\n4,5,6";
	return $content;
}

function slog_admin_slog_reporter( ) {
	static $slogger = null;
	if ( !$slogger ) {
		$slogger = new Slog_Reporter();
	}
	return $slogger;
}

function slog_admin_table() {
	//BW_::p( "Table" );
	$slogger=slog_admin_slog_reporter();
	$content=$slogger->fetch_table();
	slog_admin_display_table( $content );
}

function slog_admin_display_table( $content ) {
	$content_array = explode( "\n", $content );
	$headings = array_shift( $content_array );
	stag( "table", "widefat" );
    stag( "thead" );
	//$headings = array( __( "Field", "oik-bwtrace" ), __( "Value", "oik-bwtrace" ), __( "Notes", "oik-bwtrace" ) );
	bw_tablerow( explode( ',', $headings ), "tr", "th" );
	etag( "thead" );
	stag( "tbody" );
	foreach ( $content_array as $content ) {
		bw_tablerow( explode( ',', $content ) );
	}
	etag( "tbody" );
	etag( "table" );
	bw_flush();

}
