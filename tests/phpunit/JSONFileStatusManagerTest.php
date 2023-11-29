<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Tests;

use DateTime;
use MWStake\MediaWiki\Component\RunJobsTrigger\IHandler;
use MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceEveryHour;
use MWStake\MediaWiki\Component\RunJobsTrigger\IStatusManager;
use MWStake\MediaWiki\Component\RunJobsTrigger\JSONFileStatusManager;
use PHPUnit\Framework\TestCase;

class JSONFileStatusManagerTest extends TestCase {

	/** @var string */
	private $workingDir = '';

	public function setUp(): void {
		$this->workingDir = sys_get_temp_dir() . '/' . microtime();
		mkdir( $this->workingDir, 0777, true );
		$testFileDir = __DIR__ . '/data/';
		$testFileNames = scandir( $testFileDir );
		foreach ( $testFileNames as $testFileName ) {
			$testFilePath = $testFileDir . $testFileName;
			if ( is_file( $testFilePath ) ) {
				copy( $testFilePath, $this->workingDir . "/$testFileName" );
			}
		}
	}

	/**
	 * @param string $key
	 * @return IHandler
	 */
	private function newMockHandler( $key ) {
		$mockHandler = $this->createMock( IHandler::class );
		$mockHandler->method( 'getKey' )->willReturn( $key );
		$mockHandler->method( 'getInterval' )->willReturn( new OnceEveryHour() );

		return $mockHandler;
	}

	/**
	 * @covers MWStake\MediaWiki\Component\RunJobsTrigger\JSONFileStatusManager::getStatus
	 */
	public function testGetStatus() {
		$manager = new JSONFileStatusManager( $this->workingDir );
		$handlerReady = $this->newMockHandler( 'jsonfile-statusmanager-getstatus-test1' );

		$datetimeReady = new DateTime( '2021-01-02 01:00:00' );
		$status = $manager->getStatus( $handlerReady, $datetimeReady );
		$this->assertEquals( IStatusManager::STATUS_READY, $status );

		$datetimeWaiting = new DateTime( '2021-01-01 02:00:00' );
		$status = $manager->getStatus( $handlerReady, $datetimeWaiting );
		$this->assertEquals( IStatusManager::STATUS_WAITING, $status );

		$handlerRunning = $this->newMockHandler( 'jsonfile-statusmanager-getstatus-test2' );
		$datetimeReady = new DateTime( '2021-01-02 01:00:00' );
		$status = $manager->getStatus( $handlerRunning, $datetimeReady );
		$this->assertEquals( IStatusManager::STATUS_RUNNING, $status );
	}

	/**
	 * @covers MWStake\MediaWiki\Component\RunJobsTrigger\JSONFileStatusManager::setRunning
	 */
	public function testSetRunning() {
		$manager = new JSONFileStatusManager( $this->workingDir );
		$handlerReady = $this->newMockHandler( 'jsonfile-statusmanager-setrunning-test1' );

		$datetimeReady = new DateTime( '2021-01-02 01:00:00' );
		$status = $manager->getStatus( $handlerReady, $datetimeReady );
		$this->assertEquals( IStatusManager::STATUS_READY, $status );

		$manager->setRunning( $handlerReady );
		$status = $manager->getStatus( $handlerReady, $datetimeReady );
		$this->assertEquals( IStatusManager::STATUS_RUNNING, $status );

		$actualFileData = $this->getDataFromFile( 'jsonfile-statusmanager-setrunning-test1' );
		$this->assertEquals( IStatusManager::STATUS_RUNNING, $actualFileData['status'] );
	}

	/**
	 * @covers MWStake\MediaWiki\Component\RunJobsTrigger\JSONFileStatusManager::setEnded
	 */
	public function testSetEnded() {
		$manager = new JSONFileStatusManager( $this->workingDir );
		$handler = $this->newMockHandler( 'jsonfile-statusmanager-setended-test1' );

		$manager->setEnded( $handler );
		$actualFileData = $this->getDataFromFile( 'jsonfile-statusmanager-setrunning-test1' );
		$this->assertEquals( IStatusManager::STATUS_READY, $actualFileData['status'] );

		$actualFileData = $this->getDataFromFile( 'jsonfile-statusmanager-setended-test1' );
		$this->assertEquals( date( 'Y-m-d H:00:00', time() + 3600 ), $actualFileData['nextrun'] );
	}

	/**
	 * @covers MWStake\MediaWiki\Component\RunJobsTrigger\JSONFileStatusManager::setFailed
	 */
	public function testSetFailed() {
		$manager = new JSONFileStatusManager( $this->workingDir );
		$handler = $this->newMockHandler( 'jsonfile-statusmanager-setfailed-test1' );

		$manager->setFailed( $handler );
		$actualFileData = $this->getDataFromFile( 'jsonfile-statusmanager-setfailed-test1' );
		$expectedNextRun = date( 'Y-m-d H:00:00', time() + 3600 );
		$this->assertEquals( $expectedNextRun, $actualFileData['nextrun'] );

		$datetimeReady = new DateTime( $expectedNextRun );
		$status = $manager->getStatus( $handler, $datetimeReady );
		$this->assertEquals( IStatusManager::STATUS_READY, $status );

		$actualFileData = $this->getDataFromFile( 'jsonfile-statusmanager-setfailed-test1' );
		$this->assertEquals( IStatusManager::STATUS_FAILED, $actualFileData['status'] );
	}

	private function getDataFromFile( $handlerKey ) {
		return json_decode( file_get_contents( $this->workingDir . "/$handlerKey.json" ), true );
	}
}
