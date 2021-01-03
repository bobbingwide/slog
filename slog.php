<?php // (C) Copyright Bobbing Wide 2015-2020

/*
Plugin Name: slog
Plugin URI: https://www.bobbingwide.com/oik-plugins/slog
Description: Post process oik-bwtrace daily trace summary reports
Version: 0.0.1
Author: bobbingwide
Author URI: http://www.bobbingwide.com/about-bobbing-wide
Text Domain: slog
Domain Path: /languages/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2015-2017 Bobbing Wide (email : herb@bobbingwide.com )

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
	add_action( 'init', 'slog_init', 11 );
	add_action( 'admin_menu', 'slog_admin_menu', 11 );
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
	oik_lib_fallback( dirname( __FILE__ ) . '/libs' );
	add_action( 'admin_init', 'slog_options_init' );
}

function slog_admin_enqueue_scripts() {
	if ( function_exists( 'pompey_chart_enqueue_scripts') ) {
		pompey_chart_enqueue_scripts();
	}
}

function slog_admin_menu() {
	if ( oik_require_lib( "oik-admin" ) ) {
		$hook=add_options_page( "Slog admin", "Slog admin", "manage_options", "slog", "slog_admin_page" );
	}
}

/**
 * Slog admin page.
 * - Form
 * - Chart
 * - CSV Table
 */

function slog_admin_page() {
	BW_::oik_menu_header( __( "Slog", "slog" ), "w70pc" );
	BW_::oik_box( null, null, __( "Form", "slog" ) , "slog_admin_form" );
	BW_::oik_box( null, null, __( "Chart", "slog" ), "slog_admin_chart" );
	BW_::oik_box( null, null, __( "CSV table", "slog" ), "slog_admin_table" );
	oik_menu_footer();
	bw_flush();
}


/**
 * Register bw_trace_options
 *
 * Init plugin options to white list our options
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
	bw_form( "options.php" );
	$options = get_option('slog_options');
	stag( 'table class="form-table"' );
	bw_flush();
	settings_fields('slog_options_options');

	$report_options = slog_admin_report_options();
	$type_options = slog_admin_type_options();
	$display_options = slog_admin_display_options();

	BW_::bw_textfield_arr( 'slog_options', __( 'File', 'slog' ), $options, 'file', 60 );
	BW_::bw_select_arr( 'slog_options', __( 'Report type', 'slog' ), $options, 'report', array( "#options" => $report_options ) );
	BW_::bw_select_arr( 'slog_options', __( 'Chart type', 'slog' ), $options, 'type', array( "#options" => $type_options ) );
	BW_::bw_select_arr( 'slog_options', __( "Display", 'slog' ), $options, 'display', array( "#options" => $display_options ) );
	BW_::bw_textfield_arr( 'slog_options', __( 'Having', 'slog'), $options, 'having', 10 );

	etag( "table" );
	BW_::p( isubmit( "ok", __( "Save settings", 'slog' ), null, "button-primary" ) );
	etag( "form" );
	bw_flush();
}

function slog_admin_type_options() {
	$types  = [ 'line' => __( "Line", 'slog' ),
		'bar' => __( 'Bar', 'slog' ),
		'pie' => __( 'Pie', 'slog' )
		];
	return $types;
}

function slog_admin_report_options() {
	$reports = [ 'request_types' => 'Request types',
		'suri' => 'Stripped Request URIs',
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
 * percentage | Percentage of elapsed time of the requests in this grouping
 * accum | Accumulated percentage of the requests
 */
function slog_admin_display_options() {
	$display = [ 'count' => __( 'Count', 'slog')
	, 'elapsed' => __( 'Elapsed', 'slog')
	, 'percentage' => __( 'Percentage', 'slog')
	, 'accum' => __( 'Accumulated percentage', 'slog')
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

	BW_::p("Admin chart");
	$atts = slog_admin_chart_atts();
	$content = slog_admin_chart_content();
	if ( function_exists( 'pompey_chart_chart_shortcode') ) {
		//pompey_chart_enqueue_scripts();
		//$atts = slog_admin_chart_atts();
		//$content = slog_admin_chart_content();
		slog_admin_chart_display( $atts, $content );

	} else {
		BW_::p( 'Install and activate pompey-chart');
	}
	bw_flush();
}

function slog_admin_chart_atts() {
	$options = get_option( 'slog_options');
	//print_r( $options );
	$atts = [];
	$atts[ 'type' ] = bw_array_get( $options, 'type');
	// How do we pass stackBars and other options?
	return $atts;
}

function slog_admin_chart_content() {
	$options = get_option( 'slog_options');
	oik_require( 'class-slog-reporter.php', 'slog' );
	$slogger = new Slog_Reporter();
	$content = $slogger->run_report( $options );

	//$content = "A,B,C\n1,2,3\n4,5,6";
	return $content;
}

function slog_admin_chart_display( $atts, $content ) {
	$output = pompey_chart_chart_shortcode( $atts, $content, 'chart');
	e( $output );
}

function slog_admin_table() {
	BW_::p("Admin table");
	bw_flush();
}
