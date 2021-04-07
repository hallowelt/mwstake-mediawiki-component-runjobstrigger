<?php

namespace MWStake;

interface IRunJobsTrigger {

	/**
	 * @return Status
	 */
	public function run();

	/**
	 * @return RunJobsTrigger\Interval
	 */
	public function getInterval();
}
