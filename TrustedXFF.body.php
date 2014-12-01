<?php

class TrustedXFF {
	static $instance;

	public $cdb;

	// FIXME: IPv6 ranges need to be put here for now, there is no
	// trusted-hosts.txt support. The ranges are too large to be expanded with
	// the current CDB system.
	static $ipv6Ranges = array(
		// Opera Mini
		// Source: Email 22-May-2013
		'2001:4c28:1::/48',
		'2001:4c28:2000::/36',
		'2001:4c28:3000::/36'
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
			$this->cdb = CdbReader::open( $wgTrustedXffFile );
		}
		return $this->cdb;
	}

	function isTrusted( $ip ) {
		$cdb = $this->getCdbHandle();
		// Try single host
		$hex = IP::toHex( $ip );
		$data = $cdb->get( $hex );
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