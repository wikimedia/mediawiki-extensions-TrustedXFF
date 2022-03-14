<?php

namespace MediaWiki\Extension\TrustedXFF;

use Wikimedia\IPSet;
use Wikimedia\IPUtils;

class TrustedXFF {
	/**
	 * @internal For tests only
	 */
	public static $instance;

	// FIXME: IPv6 ranges need to be put here for now, there is no
	// trusted-hosts.txt support. The ranges were too large to be expanded with
	// the old CDB system.
	private const IPV6_RANGES = [
		// Opera Mini
		// Source: Email 22-May-2013
		'2001:4c28:1::/48',
		'2001:4c28:2000::/36',
		'2001:4c28:3000::/36'
	];

	/** @var IPSet */
	private $ipv6Set;

	/** @var array */
	private $hosts;

	/**
	 * @codeCoverageIgnore
	 * @param array $hosts List of IPv4 IPs
	 */
	private function __construct( array $hosts ) {
		$this->hosts = $hosts;
		$this->ipv6Set = new IPSet( self::IPV6_RANGES );
	}

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
	 * @codeCoverageIgnore
	 * @return TrustedXFF
	 */
	public static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new TrustedXFF(
				require dirname( __DIR__ ) . '/trusted-hosts.php'
			);
		}
		return self::$instance;
	}

	/**
	 * @param string $ip
	 * @return bool
	 */
	public function isTrusted( $ip ) {
		// Try single host
		$hex = IPUtils::toHex( $ip );
		$data = $this->hosts[ $hex ] ?? null;
		if ( $data ) {
			return true;
		}

		// Try IPv6 ranges
		if ( strpos( $hex, 'v6' ) === 0 ) {
			return $this->ipv6Set->match( $ip );
		}

		return false;
	}
}
