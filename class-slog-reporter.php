<?php

//ini_set('memory_limit','1572M');

$plugin = "wp-top12";
oik_require( "libs/class-vt-stats.php", $plugin );
oik_require( "libs/class-vt-row-basic.php", $plugin );
oik_require( "libs/class-object-sorter.php", $plugin );
oik_require( "libs/class-object.php", $plugin );
oik_require( "libs/class-object-grouper.php", $plugin );
oik_require( "libs/class-csv-merger.php", $plugin );

oik_require( 'class-narrator.php', 'oik-i18n');




class Slog_Reporter {

	public $file;
	public $report;
	public $type;
	public $display;
	public $having;

	public $narrator;
	public function __construct() {
		$this->narrator = Narrator::instance();
	}

	public function run_report( $options ) {
		$this->parse_options( $options );
		if ( $this->validate_file() ) {
			$stats = new VT_stats();
			$stats->set_file( $this->file );
			$stats->set_report( $this->report );
			$stats->set_display( $this->display );
			if ( $this->having ) {
				$stats->set_having( $this->having );
			}
			$content = $stats->run_report();
		} else {
			$content="A,B,C\n1,2,3\n4,5,6";
		}
		return $content;
	}

	/**
	 * Set options values.
	 * @param $options
	 */

	public function parse_options( $options ) {
		$this->file = $options['file'];
		$this->report = $options['report'];
		$this->type = $options['type'];	// We probably don't need this.
		$this->display = $options['display'];
		$this->having = $options['having'];
		$this->validate_file();
	}

	public function validate_file() {
		if ( !file_exists( $this->file )) {
			$this->narrator->narrate( 'Missing file', $this->file );
			return false;
		}
		return true;

	}

}