<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'TrustedXFF' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['TrustedXFF'] = __DIR__ . '/i18n';
	/*wfWarn(
		'Deprecated PHP entry point used for TrustedXFF extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die( 'This version of the TrustedXFF extension requires MediaWiki 1.25+' );
}