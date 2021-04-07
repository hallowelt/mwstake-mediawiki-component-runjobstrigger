<?php

namespace MWStake\RunJobsTrigger\Interval;

use MWStake\RunJobsTrigger\Interval;

class OnceEveryHour implements Interval {

	/**
	 *
	 * @paramDateTime $currentRunTimestamp
	 * @param array $options
	 * @returnDateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options ) {
		$nextTS = clone $currentRunTimestamp;
		$nextTS->modify( '+1 hour' );
		$nextTS->setTime( $nextTS->format( 'H' ), 0, 0 );

		return $nextTS;
	}
}
