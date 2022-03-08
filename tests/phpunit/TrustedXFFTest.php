<?php

use MediaWiki\Extension\TrustedXFF\TrustedXFF;

class TrustedXFFTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setup();
		TrustedXFF::$instance = null;
	}

	/**
	 * @covers MediaWiki\Extension\TrustedXFF\TrustedXFF::isTrusted
	 * @covers MediaWiki\Extension\TrustedXFF\TrustedXFF::getInstance
	 * @covers MediaWiki\Extension\TrustedXFF\TrustedXFF::onIsTrustedProxy
	 */
	public function testMissingXffFileIsHandledGracefully() {
		$ip = '127.0.0.2';
		$trusted = false;

		TrustedXFF::onIsTrustedProxy( $ip, $trusted );

		$this->assertFalse( $trusted );
	}

}
