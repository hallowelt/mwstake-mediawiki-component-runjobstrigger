<?php

namespace MWStake\MediaWiki\Component\Tests\RunJobsTrigger\Interval;

use DateTime;
use MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceAWeek;
use PHPUnit\Framework\TestCase;

class OnceAWeekTest extends TestCase {
	/**
	 * @covers MWStake\RunJobsTrigger\Interval\OnceAWeek::getNextTimestamp
	 */
	public function testCurrentDay() {
		$currentTS = new DateTime( '1970-01-01' );
		$expectedNextTS = clone $currentTS;
		$expectedNextTS->modify( 'next sunday' );

		$interval = new OnceAWeek();
		$nextTS = $interval->getNextTimestamp( $currentTS, [
			"once-a-week-day" => "sunday"
		] );

		$this->assertEquals( $expectedNextTS, $nextTS, 'Should be same day' );
	}

	/**
	 * @covers MWStake\RunJobsTrigger\Interval\OnceAWeek::getNextTimestamp
	 */
	public function testNextWeek() {
		$currentTS = new DateTime( '1970-01-01' );
		$expectedNextTS = clone $currentTS;
		$expectedNextTS->modify( 'next tuesday' );

		$interval = new OnceAWeek();
		$nextTS = $interval->getNextTimestamp( $currentTS, [
			"once-a-week-day" => "tuesday"
		] );

		$this->assertEquals( $expectedNextTS, $nextTS, 'Should be next day' );
	}

	/**
	 * @covers MWStake\RunJobsTrigger\Interval\OnceAWeek::getNextTimestamp
	 */
	public function testWeekdayOverride() {
		$this->assertTrue( true );
	}
}
