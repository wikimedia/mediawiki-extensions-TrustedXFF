<?php

if (!defined('MEDIAWIKI')) {
	die(1);
}

/**
 * Trusted hosts file in CDB format.
 * The file can be generated using generate.php
 *
 * You can download Wikimedia's trusted-xff.cdb from:
 *
 * http://noc.wikimedia.org/conf/trusted-xff.cdb
 *
 * For details, see http://meta.wikimedia.org/wiki/XFF_project
 */
$wgTrustedXffFile = $IP . '/cache/trusted-xff.cdb';


/** Registration */
$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'TrustedXFF',
	'descriptionmsg' => 'trustedxff-desc',
	'author'         => 'Tim Starling',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:TrustedXFF',
);

$wgAutoloadClasses['TrustedXFF'] = __DIR__ . '/TrustedXFF.body.php';
$wgMessagesDirs['TrustedXFF'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['TrustedXFF'] = __DIR__ . '/TrustedXFF.i18n.php';
$wgHooks['IsTrustedProxy'][] = 'TrustedXFF::onIsTrustedProxy';
