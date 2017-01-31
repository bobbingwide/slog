<?php // (C) Copyright Bobbing Wide 2015-2017

/**
 * Syntax: oikwp getvt.php [startdate [enddate]] host=blah
 * 
 * @TODO Where startdate and enddate are currently hardcoded
 * @TODO Use the directory structure to determine which urls to access
 * @TODO Start from the most recent date found
 * 
 * 
 */
 
function get_url( $host, $date ) {
  $url = "http://$host/bwtrace.vt.$date";
	return( $url );
}

/**
 * Fetch the vt file if necessary
 *
 * @param string $host
 * @param string $date
 *
 */

function maybe_get_vt( $host, $date ) {
	$need_to_fetch = check_vt( $host, $date );
	if ( $need_to_fetch ) {
		$content = get_vt( $host, $date );
		save_vt( $host, $date, $content );
	}		
}

/** 
 * Return the target file name
 */ 
function get_filename( $host, $date ) {
	return( "vt2017/$host/$date.vt" );
}

/**
 * Check if the file already exists
 * 
 */ 
function check_vt( $host, $date ) {
	$need_to_fetch = true;
	$filename = get_filename( $host, $date ); 
	if ( file_exists( $filename ) && $fs = filesize( $filename ) ) {
		$need_to_fetch = false;
	} else {
		$date2 = substr( $date, 4 );
		$filename2 = get_filename( $host, $date2 );
		if ( file_exists( $filename2 ) && $fs = filesize( $filename2 ) ) {
			rename(	$filename2, $filename );
			$need_to_fetch = false;
		}
	}
	if ( !$need_to_fetch ) {
		echo "$date $filename $fs already downloaded" . PHP_EOL;
	}
	return( $need_to_fetch );
}


/**
 * Gets bwtrace.vt URL
 *
 * @param string $url - the URL to fetch
 * @return string|false - false if the file does not exist or we get a 404 page
 */
function get_contents( $url ) {
  $result = @file_get_contents( $url );
	if ( !$result !== false ) {
		if ( 0 === strpos( $result, "<!" ) ) {		// It looks like a 404 page
			$result = false;
		}
	}
	return( $result );
}
		
	


 
/**
 * Retrieve a bwtrace.vt.date file for the given date.id
 * 
 * @param string $host
 * @param string $date - in form mmdd or mmdd.n where n is an integer starting from 2
 * @return string the file contents 
 */
function get_vt( $host, $date ) {
  //$result = bw_remote_get2( $url );
	$url = get_url( $host, $date );
	$result = get_contents( $url );
	if ( false === $result ) {
		$date2 = substr( $date, 4 ); 
		$url = get_url( $host, $date2 );
		$result = get_contents( $url );
	}
  //echo $result;
  echo "$date $host: " . strlen( $result ) . PHP_EOL;
  return( $result );
}

/**
 * Save the file
 * 
 * Note: hardcoded target directory
 * 
 * @param string $host - the domain name
 * @param string $date - the file date ( expected format yyyymmdd )
 * @param string $content
 */
function save_vt( $host, $date, $content ) {
	@unlink( "vt2017/$host/$date.vt" );
	$date2 = substr( $date, 4 );
	@unlink( "vt2017/$host/$date2.vt" );
  file_put_contents( "vt2017/$host/$date.vt", $content ); 
}



oik_require( "wp-batch-remote.php", "play" );

oik_require( "includes/oik-remote.inc" ); 
 
//  "oik-plugins.biz" and oik-plugins.com are now the same
// "oik-plugins.uk" now also oik-plugins.com

	
/* 
bigram.co.uk,,95,
bobbingwide.co.uk,y,,
y bobbingwide.com,,508,
bobbingwide.uk,y,,
bobbingwidewebdesign.com,,163,
cookie-cat.co.uk,,81,
cwiccer.com,,250,
y herbmiller.me,,275,
y oik-plugins.co.uk,,361,
y oik-plugins.com,,,
oik-plugins.eu,,535,
y wp-a2z.org,,1454,
 wp-pompey.org.uk,,186,
*/

$hosts = array( "oik-plugins.com"
              , "oik-plugins.co.uk"
							, "herbmiller.me"
							, "bobbingwide.com"
						//	, "wp-a2z.org"
							,	"bigram.co.uk"
						//	, "bobbingwide.co.uk"
						//	, "bobbingwide.uk
						//  , "bobbingwide.org.uk"
							, "bobbingwidewebdesign.com"
							, "cookie-cat.co.uk"
							, "cwiccer.com"
							, "oik-plugins.eu"
							, "wp-pompey.org.uk"
              );

$multisites = array( "wp-a2z.org" => 8
                   );							
							
							
//$hosts = array( "herbmiller.me" ); 
 					
//$dates = array( "0320", "0321", "0322", "0323", "0324", "0325", "0326", "0327", "0328", "0329", "0330", "0331" );
//$dates = array( "0401", "0402", "0403", "0404", "0405", "0406", "0407", "0408", "0409", "0410" );
//$dates = array( "0411", "0412", "0413", "0414", "0415", "0416", "0417", "0418", "0419", "0420" );
//$dates = array( "0421", "0422", "0423", "0424", "0425", "0426", "0427", "0428", "0429", "0430" );
//$dates = array( "0501", "0502", "0503", "0504", "0505" ); // , "0426", "0427", "0428", "0429", "0430" );

//$hosts = array( "bobbingwide.org.uk" );

$dates = array();

$startdate = oik_batch_query_value_from_argv( 1, null );
echo "Start: $startdate" . PHP_EOL;
$startdate = strtotime( $startdate );
$enddate = oik_batch_query_value_from_argv( 2, null );
if ( $enddate ) {
	$enddate = strtotime( $enddate );
} else {
	$enddate = time();
}

echo "End: $enddate" . PHP_EOL;
$host = oik_batch_query_value_from_argv( "host", null );
if ( $host ) {
	$hosts = bw_as_array( $host );
}

//$enddate = time();
//$enddate = strtotime( "2016-07-27" );

//$startdate = strtotime( "2015-06-01" );
//$enddate = strtotime( "2015-07-0" );

//echo "start: $startdate" ;
//echo "end: $enddate";

for ( $thisdate = $startdate; $thisdate<= $enddate; $thisdate+= 86400 ) {
	$dates[] = date( "Ymd", $thisdate); 
}
echo " Start:" . reset( $dates) . PHP_EOL;
echo "End: " . end( $dates ) . PHP_EOL;

/** 
 * Fetch and save the bwtrace.vt.mmdd file for each of the selected hosts
 */
if ( true ) {

  foreach ( $dates as $date ) {
    foreach ( $hosts as $host ) {
			maybe_get_vt( $host, $date );
    }
  }  
}

/** 
 * Fetch and save the bwtrace.vt.mmdd.suffix file for each of the selected hosts
 */
if ( true ) {

  foreach ( $dates as $date ) {
		foreach ( $multisites as $host => $count_sites ) {
			echo "$host: $count_sites" . PHP_EOL;
			
      $content = get_vt( $host, $date );
      save_vt( $host, $date, $content );
			
			for ( $count = 2; $count <= $count_sites; $count++ ) {
				$datedotn = "$date.$count";
				maybe_get_vt( $host, $datedotn );
				//$content = get_vt( $host, $datedotn );
				//save_vt( $host, $datedotn, $content );
			}
			
		}
  }  
}

/**
 * Process each of the files from the hosts
 */
oik_require( "vt.php", "play" );

ini_set('memory_limit','2048M');

foreach ( $dates as $date ) {

  foreach ( $hosts as $host ) {
    process_file( "vt2017/$host/$date.vt" );
  }
	
	foreach ( $multisites as $host => $count_sites ) {
		process_file( "vt2017/$host/$date.vt" );
		for ( $count = 2; $count <= $count_sites; $count++ ) {
			process_file( "vt2017/$host/$date.$count.vt" );
		}
	}
			
}  
