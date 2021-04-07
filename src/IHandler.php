<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use Status;

interface IHandler {

	/**
	 * @return Status
	 */
	public function run();

	/**
	 * @return Interval
	 */
	public function getInterval();
}
