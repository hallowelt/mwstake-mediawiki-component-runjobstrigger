<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Tests\Interval;

use MWStake\MediaWiki\Component\RunJobsTrigger\IHandler;
use MWStake\MediaWiki\Component\RunJobsTrigger\IStatusManager;
use MWStake\MediaWiki\Component\RunJobsTrigger\JSONFileStatusManager;
use PHPUnit\Framework\TestCase;

class JSONFileStatusManagerTest extends TestCase {

	private $workingDir = '';

	public function setUp() : void {
		$this->workingDir = sys_get_temp_dir() . '/' . time();
		mkdir( $this->workingDir, 0777, true );
	}

	/**
	 * @param string $key
	 * @return IHandler
	 */
	private function newMockHandler( $key ) {
		$mockHandler = $this
			->createMock( IHandler::class )
			->expects( $this->atLeastOnce() )
			->method( 'getKey' )
			->willReturn( $key );

		return $mockHandler;
	}

	/**
	 * @covers JSONFileStatusManager::setStatus
	 */
	public function testSetStatus() {
		$manager = new JSONFileStatusManager( $this->workingDir );
		$handler = $this->newMockHandler( 'test1' );

		$this->assertFileNotExists( $this->workingDir . '/test1.json' );
		$manager->setStatus( $handler, IStatusManager::STATUS_RUNNING );
		$this->assertFileExists( $this->workingDir . '/test1.json' );
	}
}
