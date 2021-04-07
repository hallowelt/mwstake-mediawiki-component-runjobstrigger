<?php

namespace MWStake\RunJobsTrigger\Interval;

use MWStake\RunJobsTrigger\Interval;

class OnceAWeek implements Interval {

	/**
	 *
	 * @paramDateTime $currentRunTimestamp
	 * @param array $options
	 * @returnDateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options ) {
		$nextSunday = clone $currentRunTimestamp;
		$nextSunday->modify( 'next ' . $options['once-a-week-day'] );
		return $nextSunday;
	}
}
