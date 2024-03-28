<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use ConfigException;
use DateTime;
use Exception;
use GlobalVarConfig;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\RunJobsTrigger\Job\InvokeRunner;
use Psr\Log\LoggerInterface;

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

	/**
	 * @var IHandler
	 */
	private $currentHandler = null;

	public function execute() {
		$this->logger->info( "Start processing at " . date( 'Y-m-d H:i:s' ) );

		foreach ( $this->handlers as $handler ) {
			$this->currentHandler = $handler;

			if ( $this->shouldRunCurrentHandler() ) {
				$this->runCurrentHandler();
			}
		}

		$this->logger->info( "End processing at " . date( 'Y-m-d H:i:s' ) );
	}

	private function shouldRunCurrentHandler() {
		$status = $this->statusManager->getStatus( $this->currentHandler, new DateTime() );
		if ( $status === IStatusManager::STATUS_RUNNING ) {
			$this->logger->info(
				"Handler '{key}' is already running.",
				[
					'key' => $this->currentHandler->getKey()
				]
			);
			return false;
		}
		if ( $status !== IStatusManager::STATUS_READY ) {
			$this->logger->info(
				"Handler '{key}' has status '{status}' and threfore is not ready.",
				[
					'key' => $this->currentHandler->getKey(),
					'status' => $status
				]
			);
			return false;
		}
		return true;
	}

	private function runCurrentHandler() {
		$this->logger->info( "Start handler '{key}'", [
			'key' => $this->currentHandler->getKey()
		] );

		$start = new DateTime();
		try {
			$this->statusManager->setRunning( $this->currentHandler );
			$status = $this->currentHandler->run();
			if ( !$status->isOK() ) {
				$this->logger->error(
					"There was a error during run of handler for '{key}': {message}", [
						'key' => $this->currentHandler->getKey(),
						'message' => $status->getMessage()->plain()
					]
				);
			}

			$statusMsg = $status->getValue();
			if ( !is_string( $statusMsg ) ) {
				$statusMsg = '';
			}
			$this->statusManager->setEnded( $this->currentHandler, $statusMsg );
		} catch ( Exception $ex ) {
			$msg = $ex->getMessage();
			$this->logger->critical( $msg, [
				'exception' => $ex
			] );

			$this->statusManager->setFailed( $this->currentHandler, $msg );
		}
		$end = new DateTime();

		$handlerRunTime = $end->diff( $start );
		$formattedHandlerRunTime = $handlerRunTime->format( '%Im %Ss' );
		$this->logger->info( "Time: $formattedHandlerRunTime" );
	}

	/**
	 * Called from $wgExtensionFunctions
	 */
	public static function runDeferred() {
		if ( !defined( 'MEDIAWIKI_JOB_RUNNER' ) ) {
			return;
		}

		MediaWikiServices::getInstance()->getJobQueueGroupFactory()
			->makeJobQueueGroup()
			->push( new InvokeRunner() );
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

		$workingDir = $mwsRunJobsTriggerConfig->get( 'RunnerWorkingDir' );
		$statusManager = new JSONFileStatusManager( $workingDir );

		$options = $mwsRunJobsTriggerConfig->get( 'Options' );
		$statusManager->setOptions( $options );

		$runner = new Runner( $handlers, $statusManager, $logger );
		$runner->execute();

		return true;
	}
}
