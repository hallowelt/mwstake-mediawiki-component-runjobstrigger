<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use DateTime;

interface IStatusManager {

	const STATUS_WAITING = 'waiting';
	const STATUS_READY = 'ready';
	const STATUS_RUNNING = 'running';
	const STATUS_FAILED = 'failed';

	/**
	 * @param IHandler $handler
	 * @param DateTime $currentTimestamp
	 * @return string One of IStatusManager:STATUS_* values
	 */
	public function getStatus( $handler, $currentTimestamp );

	/**
	 * @param IHandler $handler
	 * @param string $message Arbitrary message to persist
	 * @return void
	 */
	public function setRunning( $handler, $message = '' );

	/**
	 * @param IHandler $handler
	 * @param string $message Arbitrary message to persist
	 * @return void
	 */
	public function setFailed( $handler, $message = '' );

	/**
	 * @param IHandler $handler
	 * @param string $message Arbitrary message to persist
	 * @return void
	 */
	public function setEnded( $handler, $message = '' );

	/**
	 * In form of
	 * [
	 *     '*' => [
	 *         ...
	 *     ],
	 *     'concrete-handler-key' => [
	 *         ...
	 *     ]
	 * ]
	 * @param array $options
	 * @return void
	 */
	public function setOptions( $options );
}
