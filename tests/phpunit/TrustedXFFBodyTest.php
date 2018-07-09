<?php

class TrustedXFFBodyTest extends MediaWikiTestCase {

	protected function setUp() {
		parent::setup();
		TrustedXFF::$instance = null;
	}

	/**
	 * @covers TrustedXFF::onRegistration
	 */
	public function testXffFileHasASaneDefaultOnRegistrationt() {
		global $wgTrustedXffFile;

		$this->setMwGlobals( 'wgTrustedXffFile', null );
		TrustedXFF::onRegistration();
		$this->assertStringEndsWith(
			'/cache/trusted-xff.cdb', $wgTrustedXffFile );
	}

	/**
	 * @covers TrustedXFF::isTrusted
	 */
	public function testMissingXffFileIsHandledGracefully() {
		$ip = '127.0.0.2';
		$trusted = false;

		TrustedXFF::onIsTrustedProxy( $ip, $trusted );

		$this->assertFalse( $trusted );
	}

}
