<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use DateTime;

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
	public function getStatus( $handler, $currentTimestamp ) {
		$data = $this->readData( $handler );
		if ( in_array( $data['status'], [ static::STATUS_READY, static::STATUS_FAILED ] ) ) {
			$nextRun = new DateTime();
			if ( !empty( $data['nextrun'] ) ) {
				$nextRun = new DateTime( $data['nextrun'] );
				if ( $nextRun > $currentTimestamp ) {
					return static::STATUS_WAITING;
				}
			}
		}

		// Even though the status is actually FAILED, we currently return READY
		// so the handler can be re-run
		// In the future we should probably count the number of failures and report them
		if ( $data['status'] === static::STATUS_FAILED ) {
			return static::STATUS_READY;
		}

		return $data['status'];
	}

	/**
	 * @param IHandler $handler
	 * @param string $status
	 * @param string $message
	 */
	private function setStatus( $handler, $status, $message = '' ) {
		$data = $this->readData( $handler );
		$data['status'] = $status;
		$data['message'] = $message;
		$data['statuschange'] = date( 'Y-m-d H:i:s' );
		$data['nextrun'] = '';

		if ( in_array( $status, [ static::STATUS_READY, static::STATUS_FAILED ] ) ) {
			$currentTimestamp = new DateTime();
			$handlerIntervalOptions = $this->getHandlerIntervalOptions( $handler );
			$nextrunTimestamp = $handler->getInterval()->getNextTimestamp(
				$currentTimestamp,
				$handlerIntervalOptions
			);
			$data['nextrun'] = $nextrunTimestamp->format( 'Y-m-d H:i:s' );
		}

		if ( $status === static::STATUS_RUNNING ) {
			$data['lastrun'] = $data['statuschange'];
		}

		$this->writeData( $handler, $data );
	}

	/**
	 * @param IHandler $handler
	 * @param array $data
	 */
	private function writeData( $handler, $data ) {
		$filename = $this->getFilename( $handler );
		$fileData = json_encode( $data, JSON_PRETTY_PRINT );
		$resource = fopen( $this->getFilename( $handler ), "w+" );
		if ( flock( $resource, LOCK_EX ) ) {
			fwrite( $resource, $fileData );
			flock( $resource, LOCK_UN );
		}
		fclose( $resource );
	}

	/**
	 * @param IHandler $handler
	 * @return string
	 */
	private function getFilename( $handler ) {
		return $this->workingdir . '/' . $handler->getKey() . '.json';
	}

	/**
	 * @param IHandler $handler
	 * @return array
	 */
	private function readData( $handler ) {
		$filename = $this->getFilename( $handler );
		$defaultData = [
			'status' => static::STATUS_READY,
			'message' => '',
			'statuschange' => '',
			'nextrun' => '',
			'lastrun' => '',
		];

		if ( !file_exists( $filename ) ) {
			return $defaultData;
		}

		$fileData = file_get_contents( $filename );
		$data = json_decode( $fileData, true );
		if ( !$data ) {
			return $defaultData;
		}

		return $data + $defaultData;
	}

	/**
	 * @param IHandler $handler
	 * @return array
	 */
	private function getHandlerIntervalOptions( $handler ) {
		$options = [];
		if ( isset( $this->options['*'] ) ) {
			$options = $this->options['*'];
		}
		$handlerKey = $handler->getKey();
		if ( isset( $this->options[$handlerKey] ) ) {
			$options = array_merge(
				$options,
				$this->options[$handlerKey]
			);
		}
		return $options;
	}

	/**
	 * @inheritDoc
	 */
	public function setFailed( $handler, $message = '' ) {
		$this->setStatus( $handler, static::STATUS_FAILED, $message );
	}

	/**
	 * @inheritDoc
	 */
	public function setEnded( $handler, $message = '' ) {
		$this->setStatus( $handler, static::STATUS_READY, $message );
	}

	/**
	 * @inheritDoc
	 */
	public function setRunning( $handler, $message = '' ) {
		$this->setStatus( $handler, static::STATUS_RUNNING, $message );
	}

	/**
	 * @inheritDoc
	 */
	public function clear( $handler ) {
		$filename = $this->getFilename( $handler );
		if ( !is_file( $filename ) || !is_writable( $filename ) ) {
			return;
		}
		unlink( $filename );
	}
}
