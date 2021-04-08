<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

class JSONFileStatusManager implements IStatusManager {

	/**
	 * @var string
	 */
	private $workingdir = '';

	/**
	 * @param string $workingdir
	 */
	public function __construct( $workingdir ) {
		$this->workingdir = $workingdir;
	}

	/**
	 * @inheritDoc
	 */
	public function getStatus( $handler ) {
	}

	/**
	 * @inheritDoc
	 */
	public function setStatus( $handler, $status, $message ) {
	}

}
