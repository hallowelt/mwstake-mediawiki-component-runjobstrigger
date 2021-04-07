<?php

namespace MWStake\RunJobsTrigger;

interface Interval {
	/**
	 *
	 * @paramDateTime $currentRunTimestamp
	 * @param array $options
	 * @returnDateTime
	 */
	public function getNextTimestamp( $currentRunTimestamp, $options );
}
