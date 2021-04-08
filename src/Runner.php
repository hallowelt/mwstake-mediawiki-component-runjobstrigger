<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use ConfigException;
use DateTime;
use Exception;
use GlobalVarConfig;
use JobQueueGroup;
use MediaWiki\Logger\LoggerFactory;
use MWStake\MediaWiki\Component\RunJobsTrigger\Job\InvokeRunner;
use Psr\Log\LoggerInterface;
use Wikimedia\ObjectFactory;

class Runner {

	/**
	 *
	 * @var LoggerInterface
	 */
	private $logger = null;

	/**
	 * @var IHandler[]
	 */
	private $handlers = [];

	/**
	 * @var IStatusManager
	 */
	private $statusManager = null;

	/**
	 * @param IHandler[] $handlers
	 * @param IStatusManager $statusManager
	 * @param LoggerInterface $logger
	 */
	public function __construct( $handlers, $statusManager, $logger ) {
		$this->handlers = $handlers;
		$this->statusManager = $statusManager;
		$this->logger = $logger;
	}

	public function execute() {
		$this->logger->info( "Start processing at " . date( 'Y-m-d H:i:s' ) );
		foreach ( $this->handlers as $handler ) {
			if ( $this->statusManager->getStatus( $handler ) !== IStatusManager::STATUS_RUNNING ) {
				$this->logger->info( "Running handler for '{key}'", [
					'key' => $handler->getKey()
				] );

				$start = new DateTime();
				try {
					$status = $handler->run();
				} catch ( Exception $ex ) {
					$msg = $ex->getMessage();
					$this->logger->critical( $msg, [
						'exception' => $ex
					] );

					$this->statusManager->setStatus(
						$handler, IStatusManager::STATUS_FAILED, $msg
					);
				}
				$end = new DateTime();

				$handlerRunTime = $end->diff( $start );
				$formattedHandlerRunTime = $handlerRunTime->format( '%Im %Ss' );
				$this->logger->info( "Time: $formattedHandlerRunTime" );

				if ( !$status->isOK() ) {
					$this->logger->error(
						"There was a error during run of handler for '{key}': {message}", [
							'key' => $handler->getKey(),
							'message' => $status->getMessage()->plain()
						]
					);
				}

				$statusMsg = $status->getValue();
				if ( !is_string( $statusMsg ) ) {
					$statusMsg = '';
				}
				$this->statusManager->setStatus(
					$handler, IStatusManager::STATUS_ENDED, $statusMsg
				);
			} else {
				$this->logger->info(
					"Skipped run of handler for '{key}' due to status check",
					[
						'key' => $handler->getKey()
					]
				);
			}
		}
	}

	/**
	 * Called from $wgExtensionFunctions
	 */
	public static function runDeferred() {
		if ( !defined( 'MEDIAWIKI_JOB_RUNNER' ) ) {
			return;
		}

		JobQueueGroup::singleton()->push( new InvokeRunner() );
	}

	/**
	 * Runs the runner immediately
	 *
	 * @return bool
	 * @throws ConfigException
	 */
	public static function run() {
		$mwsRunJobsTriggerConfig = new GlobalVarConfig( 'mwsgRunJobsTrigger' );
		$logger = LoggerFactory::getInstance( 'runjobs-trigger-runner' );

		$handlerFactories = $mwsRunJobsTriggerConfig->get( 'HandlerFactories' );
		$handlers = [];
		foreach ( $handlerFactories as $handlerFactorySpec ) {
			/** @var IHandlerFactory */
			$handlerFactory = ObjectFactory::getObjectFromSpec( $handlerFactorySpec );
			$handlers = $handlerFactory->processHandlers( $handlers );
		}

		$workingDir = $mwsRunJobsTriggerConfig->get( 'mwsgRunJobsTriggerRunnerWorkingDir' );
		$statusManager = new JSONFileStatusManager( $workingDir );

		$runner = new Runner( $handlers, $statusManager, $logger );
		$runner->execute();

		return true;
	}
}
