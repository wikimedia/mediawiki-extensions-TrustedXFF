<?php

use MediaWiki\Extension\TrustedXFF\TrustedXFF;

class TrustedXFFBodyTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setup();
		TrustedXFF::$instance = null;
	}

	/**
	 * @covers MediaWiki\Extension\TrustedXFF\TrustedXFF::isTrusted
	 */
	public function testMissingXffFileIsHandledGracefully() {
		$ip = '127.0.0.2';
		$trusted = false;

		TrustedXFF::onIsTrustedProxy( $ip, $trusted );

		$this->assertFalse( $trusted );
	}

}
