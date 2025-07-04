<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Job;

use Job;
use MediaWiki\Config\ConfigException;
use MediaWiki\Title\Title;
use MWStake\MediaWiki\Component\RunJobsTrigger\Runner;

class InvokeRunner extends Job {

	/**
	 * Constructor
	 */
	public function __construct() {
		$dummyTitle = Title::newFromText( 'RunJobsTriggerRunner' );
		parent::__construct( 'invokeRunner', $dummyTitle );
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
