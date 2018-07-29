<?php

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../..';
}
require "$IP/maintenance/commandLine.inc";

use Cdb\Exception as CdbException;
use Cdb\Writer as CdbWriter;

/**
 * Emulates Cdb\Writer, but generates a PHP file that returns
 * an associative array, instead.
 */
class StaticArrayWriter {
	public function __construct( $filename ) {
		$this->filename = $filename;
		$this->data = [];
	}

	public static function open( $filename ) {
		return new self( $filename );
	}

	public function set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	public function close() {
		$header = sprintf(
			"<?php\n// Generated by %s/%s on %s\n",
			basename( __DIR__ ),
			basename( __FILE__ ),
			gmdate( 'c' )
		);
		$code = $header .  'return ' . var_export( $this->data, true ) . ";\n";
		$code = preg_replace( '/(\d+ \=\>| (?=\())/i', '', $code );
		$code = preg_replace( "/^ +/m", "\t", $code );
		$code = str_replace( [ 'array(', ');' ], [ '[', '];' ], $code );
		file_put_contents( $this->filename, $code );
	}
}

$inFileName = 'trusted-hosts.txt';

$inFile = fopen( $inFileName, 'r' );
if ( !$inFile ) {
	echo "Unable to open input file \"$inFileName\"\n";
	exit( 1 );
}

if ( isset( $args[0] ) ) {
	$target = $args[0];
} elseif ( isset( $wgTrustedXffFile ) ) {
	$target = $wgTrustedXffFile;
} else {
	echo "The TrustedXffFile extension is not enabled. " .
		"Try specifing the target output file name on the command line!\n";
	exit( 1 );
}
try {
	$outFile = pathinfo( $target, PATHINFO_EXTENSION ) === 'php'
		? StaticArrayWriter::open( $target )
		: CdbWriter::open( $target );
} catch ( CdbException $e ) {
	echo "Unable to open output file \"$target\"\n";
	exit( 1 );
}

$lineNum = 0;
$numHosts = 0;
$ranges = [];
$names = [];

while ( !feof( $inFile ) ) {
	$line = fgets( $inFile );
	$lineNum++;
	if ( $line === false ) {
		break;
	}
	// Remove comment
	$hashPos = strpos( $line, '#' );
	if ( $hashPos !== false ) {
		$line = substr( $line, 0, $hashPos );
	}
	// Strip spaces
	$line = trim( $line );

	if ( $line == '' ) {
		// Comment or blank line
		continue;
	}

	list( $start, $end ) = IP::parseRange( $line );
	if ( $start === false ) {
		// Try DNS
		$names[] = [ $lineNum, $line ];
		continue;
	}
	$ranges[] = [ $lineNum, $start, $end ];
}

echo count( $names ) . " DNS queries to do...\n";
foreach ( $names as $i => $nameInfo ) {
	list( $lineNum, $name ) = $nameInfo;
	$ips = gethostbynamel( $name );
	if ( $ips === false ) {
		echo "Not a valid host or IP address on line $lineNum: $name\n";
	} else {
		foreach ( $ips as $ip ) {
			$hex = IP::toHex( $ip );
			$ranges[] = [ $lineNum, $hex, $hex ];
		}
	}
	showProgress( $i, count( $names ) );
	// Don't DoS the recursor
	usleep( 10000 );
}
echo "\n";

echo "Creating database...\n";
foreach ( $ranges as $i => $range ) {
	list( $lineNum, $start, $end ) = $range;

	if ( $start === $end ) {
		// Single host
		$outFile->set( $start, true );
		$numHosts++;
		showProgress( $i, count( $ranges ) );
		continue;
	}

	// Find the longest common prefix in the range
	for ( $length = strlen( $start ); $length > 0; $length-- ) {
		if ( substr( $start, 0, $length ) === substr( $end, 0, $length ) ) {
			break;
		}
	}
	if ( $length == 0 || ( substr( $start, 0, 3 ) == 'v6-' && $length == 3 ) ) {
		echo "Range too big on line $lineNum\n";
		continue;
	}
	$prefix = substr( $start, 0, $length );
	$suffixLength = strlen( $start ) - $length;
	$startNum = floatval( base_convert( substr( $start, $length ), 16, 10 ) );
	$endNum = floatval( base_convert( substr( $end, $length ), 16, 10 ) );
	if ( $endNum - $startNum > 8192 ) {
		echo "Range too big on line $lineNum\n";
		echo "TrustedXFF has not yet been optimised for large ranges.\n";
		continue;
	}
	if ( $endNum > pow( 2, 52 ) ) {
		// This is unreachable unless someone tweaks some constants above
		echo "Loss of precision in floating point number, will cause infinite loop.\n";
		continue;
	}
	for ( $j = $startNum; $j <= $endNum; $j++ ) {
		$hex = strtoupper( base_convert( $j, 10, 16 ) );
		$hex = str_pad( $hex, $suffixLength, '0', STR_PAD_LEFT );
		$outFile->set( $prefix . $hex, true );
		$numHosts++;
	}
	showProgress( $i, count( $ranges ) );
}
echo "\n";

$outFile->close();
echo "$numHosts hosts listed\n";

function showProgress( $current, $total ) {
	$length = 50;
	$dots = intval( ( $current + 1 ) / $total * $length );
	printf( "%6.2f%%  [" .
		str_repeat( '=', $dots ) . str_repeat( '.', $length - $dots ) . "]\r",
		( $current + 1 ) / $total * 100 );
}
