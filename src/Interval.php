<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use DateTime;

interface Interval {

	/**
	 * @param DateTime $currentRunTimestamp
	 * @param array $options
	 * @return DateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options );
}
