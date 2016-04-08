<?php

// Entry point protection
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is an extension to MediaWiki and cannot be run standalone.' );
}

/**
 * Trusted hosts file in CDB format.
 * The file can be generated using generate.php
 *
 * You can download Wikimedia's trusted-xff.cdb from:
 *
 * https://noc.wikimedia.org/conf/trusted-xff.cdb
 *
 * For details, see https://meta.wikimedia.org/wiki/XFF_project
 */
$wgTrustedXffFile = $IP . '/cache/trusted-xff.cdb';

// Register extension
$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'TrustedXFF',
	'descriptionmsg' => 'trustedxff-desc',
	'author'         => 'Tim Starling',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:TrustedXFF',
	'license-name'   => 'GPL-2.0+'
);

// Load class
$wgAutoloadClasses['TrustedXFF'] = __DIR__ . '/TrustedXFF.body.php';

// I18n files
$wgMessagesDirs['TrustedXFF'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['TrustedXFF'] = __DIR__ . '/TrustedXFF.i18n.php';

// Load hook
$wgHooks['IsTrustedProxy'][] = 'TrustedXFF::onIsTrustedProxy';
