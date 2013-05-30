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
$wgExtensionMessagesFiles['TrustedXFF'] = dirname(__FILE__) . '/TrustedXFF.i18n.php';
$wgHooks['IsTrustedProxy'][] = 'TrustedXFF::onIsTrustedProxy';

class TrustedXFF {
	static $instance;

	var $cdb;

	// FIXME: IPv6 ranges need to be put here for now, there is no
	// trusted-hosts.txt support. The ranges are too large to be expanded with
	// the current CDB system.
	static $ipv6Ranges = array(
		// Opera Turbo
		// Source: net-changes-mini
		'2001:4c28::/32'
	);

	static function onIsTrustedProxy( &$ip, &$trusted ) {
		// Don't want to override hosts that are already trusted
		if ( !$trusted ) {
			$trusted = self::getInstance()->isTrusted( $ip );
		}
		return true;
	}

	static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new TrustedXFF;
		}
		return self::$instance;
	}

	function getCdbHandle() {
		if ( !$this->cdb ) {
			global $wgTrustedXffFile;
			if ( !file_exists( $wgTrustedXffFile ) ) {
				throw new MWException( 'TrustedXFF: hosts file missing. You need to download it.' );
			}
			if ( !function_exists( 'dba_open' ) ) {
				throw new MWException( 'The TrustedXFF extension needs PHP\'s DBA module to work.' );
			}
			$this->cdb = dba_open( $wgTrustedXffFile, 'r-', 'cdb' );
		}
		return $this->cdb;
	}

	function isTrusted( $ip ) {
		$cdb = $this->getCdbHandle();
		// Try single host
		$hex = IP::toHex( $ip );
		$data = dba_fetch( $hex, $cdb );
		if ( $data ) {
			return true;
		}

		// Try IPv6 ranges
		if ( substr( $hex, 0, 2 ) === 'v6' ) {
			foreach ( self::$ipv6Ranges as $range ) {
				list( $start, $end ) = IP::parseRange( $range );
				if ( $hex >= $start && $hex <= $end ) {
					return true;
				}
			}
		}

		return false;
	}
}

