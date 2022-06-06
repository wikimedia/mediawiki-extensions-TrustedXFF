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
	 * @param IPSet $set
	 */
	private function __construct( IPSet $set ) {
		$this->ipSet = $set;
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
				IPSet::newFromJson(
					file_get_contents( dirname( __DIR__ ) . '/trusted-hosts.json' )
				)
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
