<?php

// Init the Composer autoloader
require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/vendor/autoload.php';

$files = [
	'coverage.serialized',
	'multisite-coverage.serialized',
];

foreach ( $files as $filename ) {
	// See PHP_CodeCoverage_Report_PHP::process
	// @var PHP_CodeCoverage
	$cov = include 'test-coverage/' . $filename;
	if ( isset( $codeCoverage ) ) {
		$codeCoverage->filter()->addFilesToWhitelist( $cov->filter()->getWhitelist() );
		$codeCoverage->merge( $cov );
	} else {
		$codeCoverage = $cov;
	}
}

print "\nGenerating code coverage report in HTML format ...";

$writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade(
	'UTF-8',
	false, // 'reportHighlight'
	35, // 'reportLowUpperBound'
	70 // 'reportHighLowerBound'
);

$writer->process( $codeCoverage, 'test-coverage' );

print " done\n";
print "See test-coverage/index.html\n";
