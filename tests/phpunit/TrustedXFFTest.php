<?php

use MediaWiki\Extension\TrustedXFF\TrustedXFF;

class TrustedXFFTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setup();
		TrustedXFF::$instance = null;
	}

	public function provideIPs() {
		return [
			[ '64.12.96.1', true, "IPv4 address trusted because it's in the AOL range 64.12.96.0/19" ],
			[ '127.0.0.2', false, 'IPv4 address not trusted' ],
			[ '2001:4c28:1::1', true, "IPv6 address trusted because it's in the Opera Mini range 2001:4c28:1::/48" ],
			[ '2001:4c28:2::1', false, 'IPv6 address not trusted' ],
		];
	}

	/**
	 * @dataProvider provideIPs
	 *
	 * @covers MediaWiki\Extension\TrustedXFF\TrustedXFF::isTrusted
	 * @covers MediaWiki\Extension\TrustedXFF\TrustedXFF::getInstance
	 * @covers MediaWiki\Extension\TrustedXFF\TrustedXFF::onIsTrustedProxy
	 */
	public function testisTrusted( $ip, $expectedTrusted, $msg ) {
		$trusted = false;
		TrustedXFF::onIsTrustedProxy( $ip, $trusted );

		$this->assertEquals( $expectedTrusted, $trusted, $msg );
	}

}
