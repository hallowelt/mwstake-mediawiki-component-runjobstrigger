<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Interval;

use MWStake\MediaWiki\Component\RunJobsTrigger\Interval;

class OnceADay implements Interval {

	/**
	 * Allows to shift execution of different handlers to avoid load peaks
	 * @var int
	 */
	protected static $instanceCounter = 0;

	/** @var int */
	protected $instanceNumber = 0;

	public function __construct() {
		$this->instanceNumber = static::$instanceCounter;
		static::$instanceCounter++;
	}

	/**
	 *
	 * @param DateTime $currentRunTimestamp
	 * @param array $options
	 * @return DateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options ) {
		$nextTS = clone $currentRunTimestamp;
		$nextTS->setTime( ...$options['basetime'] );

		for ( $i = 0; $i < static::$instanceCounter; $i++ ) {
			if ( $i >= $this->instanceNumber ) {
				break;
			}

			$nextTS->modify( '+15 minutes' );
		}

		if ( $nextTS < $currentRunTimestamp ) {
			$nextTS->modify( '+1 day' );
		}

		return $nextTS;
	}

	/**
	 * Resets the internal instance counter.
	 * THIS IS ONLY FOR UNIT-TESTS! DO NOT USE IT IN PRODUCTION CODE!
	 */
	public static function resetInstanceCounter() {
		static::$instanceCounter = 0;
	}
}
