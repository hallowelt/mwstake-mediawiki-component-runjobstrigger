<?php

namespace MWStake\RunJobsTrigger\Job;

use MWStake\RunJobsTriggerRunner;
use Title;
use Job;
use ConfigException;

class RunRunJobsTriggerRunner extends Job {
	/**
	 * Constructor
	 */
	public function __construct() {
		$dummyTitle = Title::newFromText( 'RunJobsTriggerRunner' );
		parent::__construct( 'runRunJobsTriggerRunner', $dummyTitle );
	}

	/**
	 * Run the job
	 * @return bool Success
	 * @throws ConfigException
	 */
	public function run() {
		return RunJobsTriggerRunner::run();
	}
}
