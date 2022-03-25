<?php

namespace MediaWiki\Extension\TrustedXFF;

use Wikimedia\IPSet;

class TrustedXFF {
	/**
	 * @internal For tests only
	 * @var TrustedXFF
	 */
	public static $instance;

	/** @var IPSet */
	private $ipSet;

	/**
	 * @codeCoverageIgnore
	 * @param array $ips List of IPs and IP ranges
	 */
	private function __construct( array $ips ) {
		$this->ipSet = new IPSet( $ips );
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
		return $this->ipSet->match( $ip );
	}
}
