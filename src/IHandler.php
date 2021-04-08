<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use Status;

interface IHandler {

	/**
	 * @return string
	 */
	public function getKey();

	/**
	 * @return Status
	 */
	public function run();

	/**
	 * @return Interval
	 */
	public function getInterval();
}
