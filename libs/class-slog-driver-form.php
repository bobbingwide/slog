<?php

/**
 * @package slog
 * @copyright (C) Copyright Bobbing Wide 2021
 *
 * Class Slog_Driver_Form
 */


class Slog_Driver_Form {

    function __construct() {

    }

    function driver_form() {
        wp_enqueue_script( 'driver', oik_url( 'js/slog-driver.js', 'slog'), null  );
        stag( "table", 'wide-fat' );
        BW_::bw_textfield( 'url', 60, __('URL', 'slog'), home_url(), null, null, [ '#type' => 'url' ] );
        BW_::bw_textfield( 'limit', 10, __('Requests', 'slog'), 10, null, null, [ '#type' => 'number'] );
        etag( "table");
        e( '<button class=""button-primary" onClick="slogDriver()"  >Run requests</button>' );

    }

    /**
     * Area for the driver's results
     */
    function driver_results() {
        sdiv();
        sol( null, 'results');
        eol();
        ediv();
    }
}