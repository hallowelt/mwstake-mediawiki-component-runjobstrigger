<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Interval;

use MWStake\MediaWiki\Component\RunJobsTrigger\Interval;

class OnceEveryQuarterHour implements Interval {

	/**
	 *
	 * @param DateTime $currentRunTimestamp
	 * @param array $options
	 * @return DateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options = [] ) {
		$nextTS = clone $currentRunTimestamp;
		$nextTS->modify( '+15 Minutes' );
		$nextTS->setTime( $nextTS->format( 'H' ), $nextTS->format( 'i' ), 0 );

		return $nextTS;
	}
}
