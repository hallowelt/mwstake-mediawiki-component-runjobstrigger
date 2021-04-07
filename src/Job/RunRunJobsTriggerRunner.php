<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Job;

use Title;
use Job;
use ConfigException;
use MWStake\MediaWiki\Component\RunJobsTrigger\Runner;

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
		return Runner::run();
	}
}
