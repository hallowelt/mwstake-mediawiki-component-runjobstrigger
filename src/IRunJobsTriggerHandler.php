<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use MWStake\MediaWiki\Component\RunJobsTrigger\Interval;
use Status;

interface IRunJobsTriggerHandler {

	/**
	 * @return Status
	 */
	public function run();

	/**
	 * @return Interval
	 */
	public function getInterval();
}
