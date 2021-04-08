<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

class JSONFileStatusManager implements IStatusManager {

	/**
	 * @var string
	 */
	private $workingdir = '';

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @param string $workingdir
	 */
	public function __construct( $workingdir ) {
		$this->workingdir = $workingdir;
	}

	/**
	 * @inheritDoc
	 */
	public function setOptions( $options ) {
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public function getStatus( $handler ) {
		return static::STATUS_ENDED;
	}

	/**
	 * @inheritDoc
	 */
	public function setStatus( $handler, $status, $message = '' ) {
	}

}
