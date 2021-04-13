<?php

/**
 * Class Slog_Search_Form
 * @copyright (C) Copyright Bobbing Wide 2021
 * @package slog
 */


class Slog_Search_Form {

    private $slog_admin = null;

    private $files = [];
    private $search;

    function __construct( $slog_admin ) {
        $this->slog_admin = $slog_admin;

    }

    function get_form_fields() {
        $this->search = bw_array_get( $_REQUEST, '_slog_search', '');
        $this->files = bw_array_get( $_REQUEST, '_slog_download_files', []);


    }

    function search_form() {
        p( "Search form");
        //bw_flush();
        $this->get_form_fields();
        $file_options = $this->slog_admin->get_file_list();
        bw_form();
        stag( 'table', 'form-table' );
        BW_::bw_textfield( '_slog_search', 20, __('Search string', 'slog'), $this->search );
        BW_::bw_select( "_slog_download_files", __('Trace files', 'slog') , $this->files,
            [ '#options' => $file_options, '#optional' => true, '#multiple' => 20 ] );
        etag( "table" );
        e( isubmit( "_slog_action[_slog_search]", __( 'Search', 'slog' ), null ) );
        etag( "form" );
        bw_flush();

    }

    function search_files() {
        foreach( $this->files as $key => $file ) {
            $this->search_file( $file );
        }
    }

    function search_file( $file ) {
        $full_file_name = $this->get_full_file_name( $file );
        $contents = $this->load_file( $full_file_name );
        $this->file_link( $file, $full_file_name );
        $this->file_contents_count( $contents );
        $this->search_contents( $contents );
    }

    /**
     * Attempts to create file link.
     *
     * Note: This might not be accessible if the trace file directory is not under the document root.
     * We also have to cater for subdirectory installs.
     *
     * @param $file
     * @param $full_file_name
     */
    function file_link( $file, $full_file_name ) {
        $url = site_url();
        $abspath = wp_normalize_path( ABSPATH);
        $relative = str_replace( $abspath, '/', $full_file_name );
        $url .= $relative;
        alink( null, $url, $file  );
    }

    function file_contents_count( $contents ) {
        p( count( $contents ));
    }

    function load_file( $file ) {
        $contents = file( $file );
        return $contents;
    }

    function get_full_file_name( $file ) {
        $filename = $this->slog_admin->get_downloads_filename( $file );
        if ( !file_exists( $filename  ) ) {
         p( "Missing file:" . $filename );
        }
        return $filename;

    }

    function search_contents( $contents ) {
        $found = 0;
        foreach ( $contents as $index => $line ) {
            $pos = strpos( $line, $this->search );
            if ( false !== $pos ) {
                $found++;

                e( $found );
                e( ' ');
                e( $index );
                e( ' ');
                e( esc_html( $line ) );
                br();
            }
        }
    }




    function search_results() {
        if ( empty( $this->search) ) {
            p("Please specify search string");
            return;
        }
       p( "Searching for..." . $this->search );


        sol();
        foreach (  $this->files as $key => $file ) {
            li( $file );
        }
        eol();

        $this->search_files();




    }

    function process() {
        p( "Processing..." );
        $this->get_form_fields();

    }

}