<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Interval;

use MWStake\MediaWiki\Component\RunJobsTrigger\Interval;

class TwiceADay implements Interval {

	/**
	 *
	 * @param DateTime $currentRunTimestamp
	 * @param array $options
	 * @return DateTime
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
