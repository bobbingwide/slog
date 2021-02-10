<?php
/*
Plugin Name: slog
Depends: oik-bwtrace
Plugin URI: https://www.bobbingwide.com/oik-plugins/slog
Description: Analyse oik-bwtrace daily trace summary reports
Version: 1.2.1
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
slog_plugin_loaded();

/**
 * Initialisation when slog plugin file loaded
 */
function slog_plugin_loaded() {
	add_action( "init", "slog_init", 22 );
	//add_action( 'init', 'slog_block_init' );
	add_action( "oik_loaded", "slog_oik_loaded" );
	//add_action( 'slog_loaded', 'slog_slog_loaded');
	//add_action( "oik_add_shortcodes", "slog_oik_add_shortcodes" );
	//add_action( "admin_notices", "slog_activation" );
	add_action( 'admin_menu', 'slog_admin_menu', 11 );
	//add_action( 'admin_menu', 'slog_admin_menu', 11 );
	add_action( 'admin_init', 'slog_options_init' );
	add_action( 'admin_enqueue_scripts', 'slog_admin_enqueue_scripts' );

}

/**
 * Implement the "init" action for slog
 *
 * Even though "oik" may not yet be loaded, let other plugins know that we've been loaded.
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
	oik_init();
	slog_enable_autoload();


	do_action( "slog_loaded" );
}

function slog_enable_autoload() {
	$lib_autoload=oik_require_lib( 'oik-autoload' );
	if ( $lib_autoload && ! is_wp_error( $lib_autoload ) ) {
		oik_autoload( true );
	} else {
		BW_::p( "oik-autoload library not loaded" );
		gob();
	}
}

/**
 * If slog's been loaded then we should be able to display slog's admin page.
 * We just need to get involved in the autoloading.
 */
function slog_slog_loaded() {
	$libs = oik_lib_fallback( dirname( __FILE__ ) . '/libs' );
	oik_init();
	//print_r( $libs );
	slog_enable_autoload();
	add_action( 'admin_menu', 'slog_admin_menu', 11 );
	add_action( 'admin_init', 'slog_options_init' );
}

/**
 * Implement the "oik_loaded" action for slog
 *
 * Now it's safe to use oik APIs to register the slog shortcode
 * but it's not necessary until we actually come across a shortcode
 */
function slog_oik_loaded() {
	bw_load_plugin_textdomain( "slog" );
}



/**
 * Dependency checking for slog
 *
 * Version | Dependent
 * ------- | ---------
 *
 */
function slog_activation() {
	static $plugin_basename = null;
	if ( !$plugin_basename ) {
		$plugin_basename = plugin_basename(__FILE__);
		add_action( "after_plugin_row_slog/slog.php", "slog_activation" );
		if ( !function_exists( "oik_plugin_lazy_activation" ) ) {
			require_once( "admin/oik-activation.php" );
		}
	}
	$depends = "oik-bwtrace:3.1";
	oik_plugin_lazy_activation( __FILE__, $depends, "oik_plugin_plugin_inactive" );
}

/**
 * Note: slog is dependent upon oik-bwtrace which itself uses & delivers the shared library files we need.
 * If neither slog nor oik-bwtrace are active then we can't do anything.
 */
function slog_admin_menu() {
	if ( function_exists( 'oik_require_lib' ) ) {
		if ( oik_require_lib( "oik-admin" ) ) {
			$hook=add_options_page( "Slog admin", "Slog admin", "manage_options", "slog", "slog_admin_page" );
		} else {
			//bw_trace2( "Slog admin not possible");
		}
		add_action( "admin_print_styles-settings_page_slog", "slog_enqueue_styles" );
	} else {
		echo "Oops";
	}
}

function slog_enqueue_styles() {
	wp_register_style( 'slog', oik_url( 'slog.css', 'slog' ), false );
	wp_enqueue_style( 'slog' );
}

function slog_admin_enqueue_scripts() {
	if ( function_exists( 'sb_chart_block_enqueue_scripts') ) {
		sb_chart_block_enqueue_scripts();
	}

}

/**
 * Slog admin page.
 */

function slog_admin_page() {
	// If slog implements autoload will it find Slog-Bloat's classes?
	if ( class_exists( 'Slog_Admin')) {
		$slog_admin_page=new Slog_Admin();
		$slog_admin_page->process();
	} else {
		BW_::p( __( 'Error: Slog_Admin class could not be loaded', 'slog' ) );
		bw_flush();
		bw_trace2();
	}


}

/**
 * Register slog_options
 *
 */
function slog_options_init(){
	$args = [ 'sanitize_callback' => 'slog_options_validate' ] ;
	register_setting( 'slog_options_options', 'slog_options', $args );
}

function slog_options_validate( $input ) {
	return $input;
}