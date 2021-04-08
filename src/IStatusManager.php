<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

interface IStatusManager {

	const STATUS_RUNNING = 'running';
	const STATUS_ENDED = 'ended';
	const STATUS_FAILED = 'failed';

	/**
	 * @param IHandler $handler
	 * @return string One of IStatusManager:STATUS_* values
	 */
	public function getStatus( $handler );

	/**
	 * @param IHandler $handler
	 * @param string $status One of IStatusManager:STATUS_* values
	 * @param string $message Arbitrary message to persist
	 * @return string
	 */
	public function setStatus( $handler, $status, $message = '' );
}
