<?php

/**
 * @copyright (C) Copyright Bobbing Wide 2021-2023
 * @package slog
 * Class Slog_Admin
 */


class Slog_Admin {

	private $action=null;

	private $slog_summary_file;
	private $slog_download_file;
	private $slog_filtered_file;

	/**
	 * Up to 12 files for comparison.
	 *
	 * Suppose it should be 13 if we want to compare against 'vanilla'
     * Let's make it 15.
	 * @var
	 */
	private $slog_files;

	private $slog_downloads_dir;
	private $slog_remote_url;
	private $slog_request_filters;
	private $slog_filter_rows;
	private $slog_filter_url;
	private $slog_interval;
	private $slog_elapsed_limit;

	private $reports_form;
	private $driver_form;
	private $search_form;

	function __construct() {
		$this->get_options();
	}

	function get_options() {
		$options = get_option('slog_options');
		$this->slog_downloads_dir = null;
		$this->slog_remote_url = null;
		$this->slog_filter_rows = false;
		if ( false !== $options ) {
			//print_r( $options );
			$this->slog_downloads_dir=$options['_slog_downloads_dir'];
			$this->slog_remote_url   =$options['_slog_remote_url'];
			$this->slog_request_filters = isset( $options['_slog_request_filters'] ) ? $options['_slog_request_filters'] : $this->get_request_types();
			$this->slog_filter_rows = isset( $options['_slog_filter_rows'] ) ? $options['_slog_filter_rows'] : false;
			$this->slog_filter_url = $options['_slog_filter_url'];
			$this->slog_interval = isset( $options['_slog_interval'] ) ? $options['_slog_interval'] : 1;
			$this->slog_elapsed_limit = isset( $options['_slog_elapsed_limit'] ) ? $options['_slog_elapsed_limit'] : 1.5;
		}
	}

	/**
	 * Returns the possible request types.
	 *
	 * Doesn't need to be the full set. Just the ones that we're likely to filter on.
	 *
	 * - method: GET, POST, HEAD. But not DELETE?
	 * - BOT, CLI - when applicable
	 * - FE, ADMIN, AJAX, REST, CLI or spam
	 */
	function get_request_types() {
		$request_types = [];
		$request_types['GET-FE'] = 'GET-FE';
		$request_types['GET-BOT-FE'] = 'GET-BOT-FE';
		$request_types['GET-CLI-FE'] = 'GET-CLI-FE';
		$request_types['GET-ADMIN'] = 'GET-ADMIN';
		$request_types['GET-BOT-ADMIN'] = 'GET-BOT-ADMIN';
		$request_types['GET-AJAX'] = 'GET-AJAX';
		$request_types['GET-BOT-AJAX'] = 'GET-BOT-AJAX';
		$request_types['GET-REST'] = 'GET-REST';
		$request_types['GET-CLI'] = 'GET-CLI';
		$request_types['GET-404'] = 'GET-404';
		$request_types['GET-spam'] = 'GET-spam';
		$request_types['HEAD-FE'] = 'HEAD-FE';
		$request_types['POST-FE'] = 'POST-FE';
		$request_types['POST-BOT-FE'] = 'POST-BOT-FE';
		$request_types['POST-CLI-FE'] = 'POST-CLI-FE';
		$request_types['POST-ADMIN'] = 'POST-ADMIN';
		$request_types['POST-ADMIN'] = 'POST-ADMIN';
		$request_types['POST-AJAX'] = 'POST-AJAX';
		$request_types['POST-REST'] = 'POST-REST';
		$request_types['POST-CLI'] = 'POST-CLI';
		$request_types['POST-spam'] = 'POST-spam';
		return $request_types;
	}

	function get_downloads_filename( $file ) {
		$filename = $this->slog_downloads_dir . $file;
		return $filename;
	}

	function get_slog_download_file() {
		return $this->slog_download_file;
	}

	function get_slog_filter_rows() {
		return $this->slog_filter_rows;
	}

	function get_slog_filter_url() {
	    return $this->slog_filter_url;
    }

	function get_interval() {
	    return $this->slog_interval;
    }

    function get_elapsed_limit() {
	    return $this->slog_elapsed_limit;
    }


	function process() {
		add_filter( "bw_nav_tabs_slog", [ $this, "nav_tabs" ], 10, 2);
		add_action( 'slog_nav_tab_compare', [ $this, "nav_tab_compare"] );
		add_action( 'slog_nav_tab_download', [ $this, "nav_tab_download"] );
		add_action( 'slog_nav_tab_filter', [ $this, "nav_tab_filter"] );
		add_action( 'slog_nav_tab_reports', [$this, "nav_tab_reports"] );
        add_action( 'slog_nav_tab_driver', [ $this, "nav_tab_driver"] );
        add_action( 'slog_nav_tab_search', [$this, "nav_tab_search"] );
        add_action( 'slog_nav_tab_settings', [ $this, "nav_tab_settings"] );
        // @TODO Convert to shared library?
		//oik_require( "includes/bw-nav-tab.php" );
		BW_::oik_menu_header( __( "Slog", "slog" ), 'w100pc' );
		$tab = BW_nav_tab::bw_nav_tabs( "reports", "Reports" );
		do_action( "slog_nav_tab_$tab" );
		oik_menu_footer();
		bw_flush();

	}

	function nav_tab_compare() {
		BW_::oik_box(null, null, __('Results', 'slog'), [$this, 'process_request'] );
		BW_::oik_box( null, null, __( "Compare results", "slog" ), [ $this, "compare_form" ] );
	}

	function nav_tab_download() {
		BW_::oik_box(null, null, __('Results', 'slog'), [$this, 'process_request'] );
		BW_::oik_box( null, null, __( "Form", "slog" ), [ $this, "download_form" ] );
	}

	function nav_tab_filter() {
		BW_::oik_box(null, null, __('Results', 'slog'), [$this, 'process_request'] );
		BW_::oik_box( null, null, __( "Form", "slog" ), [ $this, "filter_form" ] );
	}

	/**
	 * Displays the slog reports form, chart and table as required.
	 *
	 * The Slog reports are implemented in a separate class.
	 * If the Run report button hasn't been selected then the Chart and Table are not displayed.
	 * These should also not be displayed if there's a problem during process_requests
	 * or if the filtered file is empty.
	 * So continue_processing should only be true when the action has been chosen.
	 */

	function nav_tab_reports() {
		$this->reports_form = new Slog_Reports_Form( $this );
		//$reports_form->display_chart();
		//$reports_form->display_table();
		$this->process_request();
		//BW_::oik_box(null, null, __('Form', 'slog'), [$this, 'process_request'] );
		BW_::oik_box( null, null, __( 'Form', 'slog'), [ $this->reports_form, 'display_form'] );

		if ( $this->reports_form->get_continue_processing() ) {
			if ( $this->reports_form->validate_file() ) {
				BW_::oik_box( null, null, __( 'Chart', "slog" ), [ $this->reports_form, 'display_chart' ] );
				BW_::oik_box( null, null, __( 'Table', "slog" ), [ $this->reports_form, 'display_table' ] );
			}
		}
	}

	function nav_tab_settings() {
		BW_::oik_box( null, null, __( "Settings form", "slog" ), [ $this, "settings_form" ] );
	}

	function nav_tab_driver() {
        $this->driver_form = new Slog_Driver_Form( $this );
	    BW_::oik_box( null, null, __( 'Driver', 'slog'), [ $this->driver_form, "driver_form"] );
	    BW_::oik_box( null, null, __( 'Results', 'slog'), [ $this->driver_form, 'driver_results'] );
        BW_::oik_box( null, null, __( "Daily Trace Summary", 'slog' ), "oik_trace_summary" );

    }

    function nav_tab_search() {
        $this->search_form = new Slog_Search_Form( $this );
        $this->process_request();

        BW_::oik_box( null, null, __( 'Results', 'slog'), [ $this->search_form, 'search_results'] );
        BW_::oik_box( null, null, __( 'Search', 'slog'), [ $this->search_form, "search_form"] );
    }

    /**
	 * Implements bw_nav_tabs_slog filter.
	 *
	 * @TODO - the filter functions should check global $pagenow before adding any tabs - to support multiple pages using this logic
	 */
	function nav_tabs(  $nav_tabs, $tab ) {
		$nav_tabs['reports'] = 'Reports';
		$nav_tabs['compare'] = 'Compare';
		$nav_tabs['download'] = 'Download';
		$nav_tabs['filter'] = 'Filter';
		$nav_tabs['driver'] = 'Driver';
		$nav_tabs['search'] = 'Search';
		$nav_tabs['settings'] = 'Settings';
		return $nav_tabs;
	}

	function process_request() {
		$this->validate_downloads_dir();
		$this->validate_slog_summary_file();
		$this->validate_slog_download_file();
		$this->validate_slog_filtered_file();
		$this->validate_slog_files();
		$this->validate_slog_filter_rows();
		$this->validate_slog_filter_url();
		$this->validate_slog_interval();
		$this->validate_slog_elapsed_limit();
		$this->perform_action();

	}

	function validate_downloads_dir() {
		$downloads_dir = bw_array_get( $_REQUEST, "_slog_downloads_dir", $this->slog_downloads_dir );
		$downloads_dir = str_replace( "\\", "/", $downloads_dir );
		$downloads_dir = trailingslashit( $downloads_dir );
		$this->slog_downloads_dir = $downloads_dir;
	}

	function ccyymmdd_date() {
		return bw_format_date( null, 'Ymd');
	}

	function validate_slog_summary_file() {
		$slog_summary_file      =bw_array_get( $_REQUEST, "_slog_summary_file", 'bwtrace.vt.' . $this->ccyymmdd_date() );
		$this->slog_summary_file=$slog_summary_file;
	}

	/**
	 * Validates the _slog_download_file field
	 *
	 * Should use WordPresses validate_file() to avoid directory traversal.
	 */
	function validate_slog_download_file() {
		$slog_download_file=bw_array_get( $_REQUEST, '_slog_download_file', 'bwtrace.vt.' . $this->ccyymmdd_date() );
		// @TODO perform some sort of validate_file() logic.

		$this->slog_download_file=$slog_download_file;
	}

	function validate_slog_filtered_file() {
		$slog_filtered_file=bw_array_get( $_REQUEST, '_slog_filtered_file', 'filtered.csv' );
		// @TODO perform some sort of validate_file() logic.
		$this->slog_filtered_file=$slog_filtered_file;
	}

	function validate_slog_filter_rows() {
		$slog_filter_rows = bw_array_get( $_REQUEST, '_slog_filter_rows', $this->slog_filter_rows );
		$this->slog_filter_rows = $slog_filter_rows;
	}

    /**
     * Validates the URL for filtering when running compares
     *
     */
    function validate_slog_filter_url() {
        $slog_filter_url = bw_array_get( $_REQUEST, '_slog_filter_url', $this->slog_filter_url );
        $this->slog_filter_url = trim( $slog_filter_url );
    }

	function validate_slog_interval() {
        $slog_interval = bw_array_get( $_REQUEST, '_slog_interval', $this->slog_interval );
        if ( $slog_interval <= 0 ) {
            $slog_interval = 10;
        }
        $this->slog_interval = $slog_interval;
    }

    function validate_slog_elapsed_limit() {
        $slog_elapsed_limit = bw_array_get( $_REQUEST, '_slog_elapsed_limit', $this->slog_elapsed_limit );
        if ( $slog_elapsed_limit <= 0 ) {
            $slog_elapsed_limit = 1;
        }
        $this->slog_elapsed_limit = $slog_elapsed_limit;
    }

	/**
	 * Validates the selection of files to be compared.
	 *
	 * The slog_files array contains the basename of the file.
	 */
	function validate_slog_files() {
		$this->slog_files = [];
		for ( $i = 0; $i < 15; $i++ ) {
			$file = bw_array_get( $_REQUEST, "_slog_file_$i", null );
			//echo $file;
			$filename = $this->get_downloads_filename( $file );
			//echo $filename;
			if ( $file && file_exists( $filename ) ) {
				$this->slog_files[] = $file;
			} else {
				$this->slog_files[] = null;
			}
		}
	}

	function get_file_list() {
		$file_options = [];
		$files = glob( $this->slog_downloads_dir . '*' );
		foreach ( $files as $file ) {
			$basename = basename( $file );
			$file_options[$basename] = $basename;
		}
		//print_r( $files );
		return $file_options;
	}

	function compare_form() {
		bw_form();
		stag( 'table', 'form-table' );
		//bw_flush();
		$fileoptions = $this->get_file_list();
		for ( $i = 0; $i< 15; $i++ ) {
			$label = sprintf( __( 'Compare %1s', 'slog'), $i+1 );
			//BW_::bw_textfield( "_slog_file_$i", 60, $label , $this->slog_files[$i] );
			BW_::bw_select( "_slog_file_$i", $label,  $this->slog_files[$i], [ '#options' => $fileoptions, '#optional' => true ] );
			//BW_::bw_textfield( '_slog_file_1', 60, __( 'Compare 2' ), $this->slog_file[1] );
		}
        BW_::bw_textfield( '_slog_filter_url', 60, __( 'Filter URL', 'slog') , $this->slog_filter_url );
        BW_::bw_textfield( '_slog_interval', 2, __('Elapsed chart interval (hundredths)', 'slog'), $this->slog_interval );
        BW_::bw_textfield( '_slog_elapsed_limit', 2, __('Elapsed limit (seconds)', 'slog'), $this->slog_elapsed_limit );
		etag( "table" );
		e( isubmit( "_slog_action[_slog_compare]", __( 'Compare results', 'slog')));
		etag( "form" );
		bw_flush();

	}

	function download_form() {
		bw_form();
		stag( 'table', 'form-table' );
		BW_::bw_textfield( '_slog_remote_url', 60, __( 'Remote URL trace files directory', 'slog'), $this->slog_remote_url );
		BW_::bw_textfield( '_slog_summary_file', 60, __( 'Trace summary file', 'slog' ), $this->slog_summary_file );
		BW_::bw_textfield( '_slog_downloads_dir', 60, __( 'Local downloads directory', 'slog'), $this->slog_downloads_dir );
		BW_::bw_textfield( '_slog_download_file', 60, __( 'Downloaded file name', 'slog' ), $this->slog_download_file );
		etag( "table" );
		e( isubmit( "_slog_action[_slog_download]", __( "Download daily trace summary", 'slog' ), null, "button-primary" ) );
		etag( "form" );
		bw_flush();
	}

	function filter_form() {
		bw_form();
		stag( 'table', 'form-table' );
		// This should be a select list
		//BW_::bw_textfield( '_slog_download_file', 60, __( 'Downloaded file', 'slog' ), $this->slog_download_file );
		$fileoptions = $this->get_file_list();
		arsort( $fileoptions );
		BW_::bw_select( "_slog_download_file", __('Downloaded file', 'slog') , $this->slog_download_file, [ '#options' => $fileoptions, '#optional' => true ] );
		BW_::bw_textfield( '_slog_filtered_file', 60, __( 'Filtered file', 'slog' ), $this->slog_filtered_file );
		bw_tablerow( ["Request types" , implode( ',', $this->slog_request_filters)]) ;
		etag( "table" );
		e( isubmit( "_slog_action[_slog_filter]", __( 'Filter downloaded file', 'slog' ), null ) );
		etag( "form" );
		bw_flush();
	}

	/**
	 *
	 */
	function reports_form() {
		$this->reports_form->get_form_fields();
		//$this->reports_form->display_form();
		//$this->reports_form->display_chart();
		//$this->reports_form->display_table();
	}

	/**
	 * Maintains the slog settings.
	 *
	 */
	function settings_form() {
		bw_form('options.php');
		$options = get_option('slog_options');
		stag( 'table', 'form-table' );
		bw_flush();
		settings_fields('slog_options_options');
		BW_::bw_textfield_arr( 'slog_options', __('Elapsed chart interval (hundredths)', 'slog'), $options, '_slog_interval', 2 );
        BW_::bw_textfield_arr( 'slog_options', __('Elapsed limit (seconds)', 'slog'), $options, '_slog_elapsed_limit', 2 );

        BW_::bw_textfield_arr( 'slog_options', __( 'Remote URL trace files directory', 'slog' ), $options, '_slog_remote_url', 60 );
		BW_::bw_textfield_arr( 'slog_options', __( 'Download files directory', 'slog' ), $options, '_slog_downloads_dir', 60 );
		bw_checkbox_arr( 'slog_options', __('Automatically filter rows', 'slog'), $options, '_slog_filter_rows' );
		BW_::bw_textfield_arr( 'slog_options', __( 'Filter URL', 'slog') , $options, '_slog_filter_url', 60 );
		//BW_::bw_textfield_arr( 'slog_options', __( 'Filtered files directory', 'slog' ), $options, '_slog_filtered_dir', 60 );
		$request_types = $this->get_request_types();
		$args = [ '#options' => $request_types, '#multiple' => count( $request_types ) ];
		//print_r( $this->slog_request_filters );
		//print_r( $args );
		BW_::bw_select_arr( 'slog_options', __('Request types', 'slog'), $options,'_slog_request_filters', $args );
		etag( "table" );
		BW_::p( isubmit( "ok", __( "Save settings", 'slog' ), null, "button-primary" ) );
		etag( "form" );
		bw_flush();
	}

	/**
	 * Performs the chosen action
	 *
	 * @TODO Add nonce checking.
	 */
	function perform_action() {
		$action=bw_array_get( $_REQUEST, '_slog_action', null );

		if ( $action ) {
			$command=key( $action );
			$action =bw_array_get( $action, '_slog_download', null );
			if ( $action ) {
				BW_::p( "Performing:" . $action . $command );
			}
			switch ( $command ) {
				case '_slog_download':
					$this->slog_download();
					break;
				case '_slog_filter':
					$this->slog_filter();
					break;
				case '_slog_compare':
					$this->slog_compare();
					break;
				case '_slog_reports':
					$this->slog_reports();
					break;
                case '_slog_search':
                    $this->slog_search();
                    break;
				default:
					BW_::p( "Action $action not yet implemented" );
			}
		}


	}

	function slog_download() {
		$this->get_vt();
		$this->save_vt();
	}

	/**
	 * Downloads the chosen daily trace summary report.
	 *
	 * This assumes that the file is available for download.
	 */
	function get_vt() {
		$target_url = $this->slog_remote_url . '/' . $this->slog_summary_file;
		$this->file_contents=file_get_contents( $target_url );
		BW_::p( "File size:" . strlen( $this->file_contents ) );
	}

	function save_vt() {
		$written=file_put_contents( $this->get_downloads_filename( $this->slog_download_file ), $this->file_contents );
		BW_::p( "Download:" . $this->slog_download_file );
		BW_::p( "Written:" . $written );
	}

	/**
	 * Loads the download_file and filters the get requests for non-bots.
	 * writes the output to
	 */
	function slog_filter() {

		//$this->slog_admin_steps();
		$vt_stats = new VT_stats();
		$vt_stats->set_file( $this->get_downloads_filename( $this->slog_download_file ) );
		// Originally hardcoded.
		//$this->request_type_filters = [ 'GET-FE' => true, 'GET-BOT-FE' => false ];
		//$this->slog_request_filters
		$vt_stats->set_request_type_filters( $this->slog_request_filters );
		/** Hardcoded for now. xxx represents unknown */
		$vt_stats->set_http_response_filters( ['200', '302', 'xxx']);
		$vt_stats->load_file();
		$vt_stats->filter();
		$vt_stats->write_filtered( $this->get_downloads_filename( $this->slog_filtered_file ) );

	}

	/**
	 * Displays the results from two or more runs.
	 *
	 * - For each file selected produce the accumulated count percentage report grouping by elapsed time interval.
	 * - Merge the results into one CSV
	 * - Display the chart
	 *
	 */
	function slog_compare() {
		BW_::p( 'Comparing results...' );
		//print_r( $this->slog_files );
		$contents = [];
		$slogger= $this->slog_admin_slog_reporter();
		$slogger->set_request_type_filters( $this->slog_request_filters);
		$slogger->set_http_response_filters( ['200', '302', 'xxx' ] );
		foreach ( $this->slog_files as $file ) {
			if ( $file ) {
				$options=$this->get_reporter_options( $file );
				$contents[$file] = $slogger->run_report( $options );
			}
		}

		if ( count( $contents ) > 1 ) {
			$csv = $this->get_merged_contents( $contents );
			$this->display_chart( $csv );
			$this->display_table( $csv );
            $this->display_copy_to_clipboard( $csv );
		} else {
			// Display individual reports.
			foreach ( $contents as $content ) {
				$this->display_chart( $content );
			}
		}
	}

	/**
	 * Displays the
	 */
	function slog_reports() {
		//BW_::p( "Slog reports go here");
		//$this->reports_form = new Slog_Reports_Form( $this );
		$this->reports_form->get_form_fields();

		$this->reports_form->set_continue_processing();

	}

	function slog_search() {

	    $this->search_form->process();

    }


	function get_merged_contents( $contents ) {
		//print_r( $contents );
		$log_files = implode(',', $this->slog_files);
		$log_files = rtrim( $log_files, ',');
		$csv = "Elapsed," . $log_files . "\n";

		if ( count( $contents ) > 1 ) {
			$merger=new CSV_merger();
			$merger->set_echo( false );
			foreach ( $contents as $key=>$content ) {
				//$content=str_replace( '<', '', $content );
				//$content=str_replace( '>', '', $content );
				$merger->append_csv( $content );
			}
			//$merger->ksort();
			$csv.=$merger->report();
		}
		return $csv;

	}

	function display_chart( $content ) {
		if ( function_exists( 'sb_chart_block_shortcode') ) {
			//sb_chart_block_shortcode( $atts, $content );
			$options = []; // Defaults to line chart.
			$output=sb_chart_block_shortcode( $options, $content, 'chartjs' );
			e( $output );
		}

		//e( $content );
	}

	/**
	 * Displays the CSV as a table.
	 *
	 * Just use oik-bbw/csv logic!
	 * @param $content
	 */
	function display_table( $content ) {
		$content_array = explode( "\n", $content );
		$heading = array_shift( $content_array );
		$heading = str_replace( '.', ' ', $heading);
		$headings = str_getcsv( $heading );
		stag( 'table', 'wide-fat' );
		stag( 'thead');
		bw_tablerow( $headings, 'tr', 'th' );
		etag( 'thead');
		stag( 'tbody');
		foreach ( $content_array as $line ) {
		    //echo $line;
			$tablerow = str_getcsv( $line );
			for ( $i=1; $i<count( $tablerow); $i++ ) {
			    $tablerow[$i] = number_format( $tablerow[$i], 6);
            }
            //bw_trace2( $tablerow, "tablerow", false );
			bw_tablerow( $tablerow );
		}
		etag( 'tbody');
		etag( 'table');
		bw_flush();
	}

	function get_reporter_options( $file ) {
		$options['file'] = $this->get_downloads_filename( $file );
		$options['report'] = 'elapsed';
		$options['report_title'] = __('Elapsed', 'slog');
		$options['type'] = 'line';
		$options['display'] = 'percentage_count_accumulative';
		$options['display_title'] = __('Percentage count accumulative', 'slog');
		$options['having'] = '';
		$options['filter'] = $this->slog_filter_rows;
		$options['filter_url'] = $this->slog_filter_url;
		$options['interval'] = $this->slog_interval;
        $options['elapsed_limit'] = $this->slog_elapsed_limit;
		return $options;
	}


	function slog_admin_steps() {
		$steps  =[];
		$steps[]="Step | Routine |Input | Output";
		$steps[]="1. Save daily trace summary from the live site. | slog getvt | bwtrace.vt.ccyymmdd[.site] | original.csv";
		$steps[]="2. Use slog to analyze requests | slog calls slog? | original.csv";
		$steps[]="3. Extract sensible GET requests with reasonable responses | slog filter  | original.csv | filtered.csv";
		$steps[]="4. Reset daily trace summary on test site | oik-bwtrace";
		$steps[]="5. Run vt-driver.php for filtered.csv against the test site| vt-driver | filtered.csv |";
		$steps[]="6. Download trace file for vanilla.csv | slog getvt | bwtrace.vt.ccyymmdd[.site] | vanilla.csv";
		$steps[]="7. Compare filtered.csv vs vanilla.csv | slog compare | filtered.csv & vanilla.csv | control";
		$steps[]="8. Make an adjustment on the test site - eg activate/deactivate a plugin";
		$steps[]="9. Reset daily trace summary on test site | oik-bwtrace";
		$steps[]="10. Run vt-driver.php for filtered.csv against the test site | vt-driver | filtered.csv";
		$steps[]="11. Download trace file for adjust-1.csv   | slog getvt | bwtrace.vt.ccyymmdd[.site] | adjust-1.csv";
		$steps[]="12. Compare vanilla.csv vs adjust-1.csv | slog compare | vanilla.csv & adjust-1.csv | result-1";
		$td     ='th';
		stag( 'table', 'form-table' );
		foreach ( $steps as $index=>$step ) {
			$cols=explode( '|', $step );
			bw_tablerow( $cols, 'tr', $td );
			$td='td';
		}
		etag( 'table' );
	}

	function slog_admin_slog_reporter( ) {
		static $slogger = null;
		if ( !$slogger ) {
			$slogger = new Slog_Reporter();
		}
		if ( !$slogger ) {
			p( "Can't load Slog_Reporter");
		}
		return $slogger;
	}

    function display_copy_to_clipboard( $content ) {
        //BW_::bw_textarea( 'csv',80,'CSV data', $content, 10, ['class' => 'hidden'] );
        wp_enqueue_script( 'copy2clip', oik_url( 'js/slog-copy2clip.js', 'slog'), null  );
        $extras = kv( "id", "slog-csv" );
        $extras .= kv( 'style', 'display:none;');
        e( iarea( 'csv', 80, $content, 10, $extras ) );
        e( '<button class=""button-secondary" onClick="slogCopyToClipboard()"  >Copy CSV to clipboard</button>' );

        //     bw_textarea( $name, $len, $text, $value, $rows=10, $args=null ) {
    }

}




