<?php

namespace MWStake\RunJobsTrigger\Interval;

use MWStake\RunJobsTrigger\Interval;

class TwiceADay implements Interval {

	/**
	 *
	 * @paramDateTime $currentRunTimestamp
	 * @param array $options
	 * @returnDateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options ) {
		$firstTS = clone $currentRunTimestamp;
		$firstTS->setTime( 1, 0, 0 );

		$secondTS = clone $currentRunTimestamp;
		$secondTS->setTime( 13, 0, 0 );

		if ( $firstTS > $currentRunTimestamp ) {
			return $firstTS;
		}

		if ( $firstTS < $currentRunTimestamp
			&& $currentRunTimestamp < $secondTS ) {
			return $secondTS;
		}

		if ( $currentRunTimestamp > $secondTS ) {
			$firstTS->modify( '+1 day' );
			return $firstTS;
		}
	}
}
