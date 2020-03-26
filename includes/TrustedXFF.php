<?php

use Cdb\Reader as CdbReader;
use Wikimedia\IPSet;
use Wikimedia\IPUtils;

class TrustedXFF {
	public static $instance;

	public $cdb;

	// FIXME: IPv6 ranges need to be put here for now, there is no
	// trusted-hosts.txt support. The ranges are too large to be expanded with
	// the current CDB system.
	public static $ipv6Ranges = [
		// Opera Mini
		// Source: Email 22-May-2013
		'2001:4c28:1::/48',
		'2001:4c28:2000::/36',
		'2001:4c28:3000::/36'
	];

	public static function onRegistration() {
		global $wgTrustedXffFile, $IP;
		if ( $wgTrustedXffFile === null ) {
			$wgTrustedXffFile = $IP . '/cache/trusted-xff.cdb';
		}
	}

	/**
	 * @var IPSet|null
	 */
	private static $ipv6Set;

	/**
	 * @param string &$ip
	 * @param bool &$trusted
	 * @return bool
	 */
	public static function onIsTrustedProxy( &$ip, &$trusted ) {
		// Don't want to override hosts that are already trusted
		if ( !$trusted ) {
			$trusted = self::getInstance()->isTrusted( $ip );
		}
		return true;
	}

	/**
	 * @return TrustedXFF
	 */
	private static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new TrustedXFF;
		}
		return self::$instance;
	}

	/**
	 * @return CdbReader|CdbReader\Hash
	 * @throws MWException
	 */
	private function getCdbHandle() {
		global $wgTrustedXffFile;

		if ( !file_exists( $wgTrustedXffFile ) ) {
			$message = "Trusted XFF file not found (\$wgTrustedXffFile = $wgTrustedXffFile)";
			wfDebugLog( 'TrustedXFF', $message );
			throw new MWException( $message );
		}

		if ( !$this->cdb ) {
			if ( pathinfo( $wgTrustedXffFile, PATHINFO_EXTENSION ) === 'php' ) {
				$this->cdb = new CdbReader\Hash( include $wgTrustedXffFile );
			} else {
				$this->cdb = CdbReader::open( $wgTrustedXffFile );
			}
		}

		return $this->cdb;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	private function isTrusted( $ip ) {
		try {
			$cdb = $this->getCdbHandle();
		} catch ( MWException $e ) {
			return false;
		}

		// Try single host
		$hex = IPUtils::toHex( $ip );
		$data = $cdb->get( $hex );
		if ( $data ) {
			return true;
		}

		// Try IPv6 ranges
		if ( substr( $hex, 0, 2 ) === 'v6' ) {
			if ( !self::$ipv6Set ) {
				self::$ipv6Set = new IPSet( self::$ipv6Ranges );
			}

			return self::$ipv6Set->match( $ip );
		}

		return false;
	}
}
