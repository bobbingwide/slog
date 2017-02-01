<?php // (C) Copyright Bobbing Wide 2015-2017

/**
 * If we get invoked at the top level then process the file
 */
if ( isset( $argc) && $argc >= 2 ) {
  $file = $argv[1];
  process_file( $file );
} else {  
  $file = "bwtrace.vt.0314";
}
//echo $file . PHP_EOL;


/**
 * Load the file as a CSV
 * 
 * @param string $file - the fully qualified file name
 * @return string - the file contents
 */
function readcsv( $file ) {
  $content_array = file( $file );
  return( $content_array );
}

/**
 * Process the incoming CSV file
 *
 * Process the CSV to produce summaries by:
 * URI - ignoring the query args
 * top level ( tl ) URI 
 * 
 * @TODO - broken down by hour if necessary
 * 
 * Produce the output as files in the subdirectories identified by the host name part of the file
 * 
 * 
 * @param string $file - the fully qualified file name
 * 
 */
function process_file( $file ) {
  $array = readcsv( $file );
  //echo count( $array ) . PHP_EOL; 
  $count = 0;
  $summary = new Summary;
  foreach ( $array as $transline ) {
    $trans = new Trans( $transline );
    //print_r( $trans );
    //echo $trans->suri;
    //echo PHP_EOL;
    $summary->add( $trans );
    $summary->addtl( $trans );
		$summary->addTotal( $trans );
    //gobang();
  }
  //echo $file . PHP_EOL;
  $path = pathinfo( $file, PATHINFO_DIRNAME );
  $date = pathinfo( $file, PATHINFO_FILENAME );
  //echo $date . PHP_EOL;
  //echo $path . PHP_EOL;
  $summary->dump( $path, $date );
	$summary->apptotal( $path, $date );
}

/**
 * 
 Array
(
    [0] => /oik-plugins/oik/?bwscid1=4&bwscid2=8&bwscid4=3&bwscid5=7
    [1] =>
    [2] => 13.834431
    [3] => 5.3.29
    [4] => 3160
    [5] => 4041
    [6] => 483
    [7] => 44
    [8] => 398
    [9] => 57
    [10] => 28
    [11] => 14
    [12] => 129
    [13] => 1.07188820839
    [14] =>
    [15] => 13.830200
    [16] => 2015-03-14T00:00:01+00:00
)


 * 0- request URI
 * 1 - AJAX action
 * 2 - elapsed ( final figure )
 * 3 - PHP version
 * 4 - PHP functions
 * 5 - User functions
 * 6 - Classes
 * 7 - Plugins
 * 8 - Files
 * 9 - Registered Widgets
 * 10 - Post types
 * 11 - Taxonomies
 * 12 - Queries
 * 13 - Query time
 * 14 - Trace records
 * 15 - Elapsed
 * 16 - Date - ISO 8601 date 
 *
 * OR it may end
 * 
 * 14 - Trace records
 * 15 - Remote address ( IP address )
 * 16 - Elapsed
 * 17 - Date - ISO 8601 date 
*/

class Trans {
  private $trans = null;
  public $uri;
  public $action;
  public $final ; // $this->trans[2];
  public $phpver; // $this->trans[3];
  public $phpfns; // $this->trans[4];
  public $userfns; // $this->trans[5];
  public $classes; // $this->trans[6];
  public $plugins; // $this->trans[7];
  public $files ; // $this->trans[8];
  public $widgets; // $this->trans[9];
  public $types ; // $this->trans[10];
  public $taxons; // $this->trans[11];
  public $queries; // $this->trans[12];
  public $qelapsed; // $this->trans[13];
  public $traces; // $this->trans[14];
	public $remote_IP; // $this->trans[15];
  public $elapsed; // $this->trans[15] or 16
  public $isodate; //$this->trans[16] or 17;
  
  public $suri;
  public $qparms;
  
  public function __construct( $transline ) {
    $this->trans = str_getcsv( $transline );
    $this->uri = $this->trans[0];
    $this->action = $this->trans[1];
    $this->final  = $this->trans[2];
    $this->phpver = $this->trans[3];
    $this->phpfns = $this->trans[4];
    $this->userfns= $this->trans[5];
    $this->classes= $this->trans[6];
    $this->plugins= $this->trans[7];
    $this->files  = $this->trans[8];
    $this->widgets= $this->trans[9];
    $this->types  = $this->trans[10];
    $this->taxons = $this->trans[11];
    $this->queries = $this->trans[12];
    $this->qelapsed = $this->trans[13];
    $this->traces = $this->trans[14];
		
		$this->remote_IP = $this->trans[15];
		$IP_parts = explode( '.', $this->remote_IP );
		if ( count( $IP_parts ) != 4 ) {
			$this->remote_IP = null;
      $this->elapsed = $this->trans[15];
			$this->isodate= $this->trans[16];
		} else {
		  $this->elapsed = $this->trans[16];
			$this->isodate = $this->trans[17];
		}
    
    list( $this->suri, $this->qparms, $blah ) = explode( "?", $this->uri . "???" , 3 );
    list( $blah, $this->suritl, $blah2 ) = explode( "/", $this->suri . "///", 3 ); 
    
     
  }

}

class Summary {
  public $suris;
  public $toplevel;
	public $total_time;
	public $count_trans;
  
  function __construct() {
    $this->suris =  array();
    $this->toplevel = array();
  }
  
  /**
   * Add the URI
   */
  function add( $trans ) {
    if ( !isset( $this->suris[ $trans->suri ] ) ) {
      $this->suris[ $trans->suri ] = array( $trans->suri, $trans->final, 1, $trans->queries, $trans->qelapsed );
    } else {
      $this->suris[ $trans->suri ][1] += $trans->final;
      $this->suris[ $trans->suri ][2]++;
      $this->suris[ $trans->suri ][3] += $trans->queries;
      $this->suris[ $trans->suri ][4] += $trans->qelapsed;
    }
  }
  
  /** 
   * Add top level URI
   */
  function addtl( $trans ) {
    if ( !isset( $this->toplevel[ $trans->suritl ] ) ) {
      $this->toplevel[ $trans->suritl ] = array( $trans->suritl, $trans->final, 1, $trans->queries, $trans->qelapsed );
    } else {
      $this->toplevel[ $trans->suritl ][1] += $trans->final;
      $this->toplevel[ $trans->suritl ][2]++;
      $this->toplevel[ $trans->suritl ][3] += $trans->queries;
      $this->toplevel[ $trans->suritl ][4] += $trans->qelapsed;
      
    }
  }
	
	/**
	 * Add the total elapsed for the transactions so far
	 */ 
	function addTotal( $trans ) {
		$this->total_time += $trans->final;
		$this->count_trans++;
	}
	
	/**
	 * Append the total elapsed, count and average for the date to "total.csv"
	 *
	 */
	function appTotal( $path, $date ) {
		if ( $this->count_trans ) {
			$average = ( $this->total_time / $this->count_trans );
		} else {
			$average = 0.0;
		}
	  $array = array(  $date, $this->total_time, $this->count_trans, $average );
		$line = implode( ",", $array );
		$line .= PHP_EOL; 
    $file = fopen( "$path/total.csv", "a" );
		fwrite( $file, $line );
    fclose( $file );
	}
  
  /**
   * Write the output CSV files
   */
  
  function dump( $path, $date ) {
    
    $head = "Average,URI $path $date,total,count,queries,qavg,qelapsed,qelavge" . PHP_EOL;
    echo $head;
    if ( file_exists( "$path/sum-$date.csv" ) ) { 
      unlink( "$path/sum-$date.csv" );
    }  
    $file = fopen( "$path/sum-$date.csv", "a" );
    fwrite( $file, $head );
    foreach ( $this->suris as $suris ) {
      if ( $suris[2] > 0 ) {
        //$this->report( $suris );
        $line = $this->as_csv( $suris );
        fwrite( $file, $line );
      }
    }
    fclose( $file );
    
    /** Top level */
    echo $head;
    
    if ( file_exists( "$path/tl-$date.csv" ) ) { 
      unlink( "$path/tl-$date.csv" );
    }
    $file = fopen( "$path/tl-$date.csv", "a" );
    fwrite( $file, $head );
    foreach ( $this->toplevel as $suris ) {
      if ( $suris[2] > 0 ) {
        //$this->report( $suris );
        $line = $this->as_csv( $suris );
        fwrite( $file, $line );
      }
    }
    fclose( $file );
  }
  
  function report( $suris ) {
    $total = $suris[1];
    $count = $suris[2];
    $avge = $total / $count;
    $queries = $suris[3];
    $qavg = $queries / $count;
    $qelapsed = $suris[4];
    $qeavg = $qelapsed / $count;
    echo sprintf( "% 3.6f,", $avge );
    echo $suris[0];
    echo ",";
    echo $total;
    
    echo ",";
    echo $count;
    echo ",";
    echo $queries;
    echo ",";
    echo (int) $qavg;
    echo ",";
    echo $qelapsed;
    echo ",";
    echo $qeavg;
    echo PHP_EOL;
  }
  
  
  function as_csv( $suris ) {
    $total = $suris[1];
    $count = $suris[2];
    $avge = $total / $count;
    $queries = $suris[3];
    $qavg = $queries / $count;
    $qelapsed = $suris[4];
    $qeavg = $qelapsed / $count;
    
    $line =  sprintf( "% 3.6f,", $avge );
    
    $line .=  $suris[0];
    $line .=  ",";
    $line .=  $total;
    
    $line .=  ",";
    $line .=  $count;
    $line .=  ",";
    $line .=  $queries;
    $line .=  ",";
    $line .=  (int) $qavg;
    $line .=  ",";
    $line .=  $qelapsed;
    $line .=  ",";
    $line .=  $qeavg;
    $line .=  PHP_EOL;
    return( $line );
  }
  
  function write( $file, $line ) {
    
  
  }
  
}  

