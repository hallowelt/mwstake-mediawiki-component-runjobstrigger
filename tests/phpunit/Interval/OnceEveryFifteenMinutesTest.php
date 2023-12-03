<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Tests\Interval;

use DateTime;
use MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceEveryQuarterHour;
use PHPUnit\Framework\TestCase;

class OnceEveryFifteenMinutesTest extends TestCase {
	/**
	 * @covers MWStake\RunJobsTrigger\Interval\OnceEveryQuarterHour::getNextTimestamp
	 */
	public function testCurrentDay() {
		$currentTS = new DateTime( '1970-01-01 00:00:00' );
		$expectedNextTS = new DateTime( '1970-01-01 00:15:00' );

		$interval = new OnceEveryQuarterHour();
		$nextTS = $interval->getNextTimestamp( $currentTS );

		$this->assertEquals( $expectedNextTS, $nextTS, 'Should be same day' );
	}

	/**
	 * @covers MWStake\RunJobsTrigger\Interval\OnceEveryQuarterHour::getNextTimestamp
	 */
	public function testNextDay() {
		$currentTS = new DateTime( '1970-01-01 02:00:00' );
		$expectedNextTS = new DateTime( '1970-01-01 02:15:00' );

		$interval = new OnceEveryQuarterHour();
		$nextTS = $interval->getNextTimestamp( $currentTS );

		$this->assertEquals( $expectedNextTS, $nextTS, 'Should be next day' );
	}

	/**
	 * @covers MWStake\RunJobsTrigger\Interval\OnceEveryQuarterHour::getNextTimestamp
	 */
	public function testMultiInstanceSpreading() {
		$currentTS = new DateTime( '1970-01-01 00:00:00' );
		$expectedNextTS1 = new DateTime( '1970-01-01 00:15:00' );
		$expectedNextTS2 = new DateTime( '1970-01-01 00:30:00' );
		$expectedNextTS3 = new DateTime( '1970-01-01 00:45:00' );
		$expectedNextTS4 = new DateTime( '1970-01-01 01:00:00' );

		$interval1 = new OnceEveryQuarterHour();
		$nextTS1 = $interval1->getNextTimestamp( $currentTS );

		$interval2 = new OnceEveryQuarterHour();
		$nextTS2 = $interval2->getNextTimestamp( $nextTS1 );

		$interval3 = new OnceEveryQuarterHour();
		$nextTS3 = $interval3->getNextTimestamp( $nextTS2 );

		$interval4 = new OnceEveryQuarterHour();
		$nextTS4 = $interval4->getNextTimestamp( $nextTS3 );

		$this->assertEquals( $expectedNextTS2, $nextTS2, 'Should be shifted by 15 minutes' );
		$this->assertEquals( $expectedNextTS3, $nextTS3, 'Should be shifted by 30 minutes' );
		$this->assertEquals( $expectedNextTS4, $nextTS4, 'Should be shifted by 45 minutes' );
		$this->assertEquals( $expectedNextTS2, $nextTS2, 'Should be shifted by 60 minutes' );
	}

	/**
	 * @covers MWStake\RunJobsTrigger\Interval\OnceEveryQuarterHour::getNextTimestamp
	 */
	public function testBasetimeOverride() {
		$currentTS = new DateTime( '1970-01-01 02:00:00' );
		$expectedNextTS = new DateTime( '1970-01-01 02:15:00' );

		$interval = new OnceEveryQuarterHour();
		$nextTS = $interval->getNextTimestamp( $currentTS, [
			'basetime' => [ 5, 0, 0 ]
		] );

		$this->assertEquals( $expectedNextTS, $nextTS, 'Should have respect configured basetime' );
	}
}
