<?php // (C) Copyright Bobbing Wide 2015-2017

/*
Plugin Name: slog
Plugin URI: http://www.oik-plugins.com/oik-plugins/slog
Description: Post process oik-bwtrace daily trace summary reports
Version: 0.0.1
Author: bobbingwide
Author URI: http://www.oik-plugins.com/author/bobbingwide
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
}

function slog_admin_menu() {
	if ( oik_require_lib( "oik-admin" ) ) {
		$hook=add_options_page( "Slog admin", "Slog admin", "manage_options", "slog", "slog_admin_page" );
	}
}

function slog_admin_page() {


}
