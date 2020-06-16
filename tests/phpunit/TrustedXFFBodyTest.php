<?php

use MediaWiki\Extensions\TrustedXFF\TrustedXFF;

class TrustedXFFBodyTest extends MediaWikiTestCase {

	protected function setUp() : void {
		parent::setup();
		TrustedXFF::$instance = null;
	}

	/**
	 * @covers MediaWiki\Extensions\TrustedXFF\TrustedXFF::getFilePathInternal
	 */
	public function testGetFilePathInternal() {
		$this->setMwGlobals( 'wgTrustedXffFile', null );
		$this->assertStringEndsWith(
			'/cache/trusted-xff.cdb', TrustedXFF::getFilePathInternal() );

		$this->setMwGlobals( 'wgTrustedXffFile', '/tmp/foo.php' );
		$this->assertEquals( '/tmp/foo.php', TrustedXFF::getFilePathInternal() );
	}

	/**
	 * @covers MediaWiki\Extensions\TrustedXFF\TrustedXFF::isTrusted
	 */
	public function testMissingXffFileIsHandledGracefully() {
		$ip = '127.0.0.2';
		$trusted = false;

		TrustedXFF::onIsTrustedProxy( $ip, $trusted );

		$this->assertFalse( $trusted );
	}

}
