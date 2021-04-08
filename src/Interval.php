<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

interface Interval {

	/**
	 *
	 * @param DateTime $currentRunTimestamp
	 * @param array $options
	 * @return DateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options );
}
