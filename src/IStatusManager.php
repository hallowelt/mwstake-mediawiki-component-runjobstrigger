<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use DateTime;

interface IStatusManager {

	public const STATUS_WAITING = 'waiting';
	public const STATUS_READY = 'ready';
	public const STATUS_RUNNING = 'running';
	public const STATUS_FAILED = 'failed';

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

	/**
	 * @param IHandler $handler
	 * @return void
	 */
	public function clear( $handler );
}
