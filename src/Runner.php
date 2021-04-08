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
	protected $logger = null;

	/**
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct( $logger ) {
		$this->logger = $logger;
	}

	public function execute() {
		$this->logger->info( "Start processing at " . date( 'Y-m-d H:i:s' ) );
			if ( $this->shouldRunCurrentHandler( $regKey ) ) {
				$this->logger->info( "Running handler for '$regKey'" );
				$start = new DateTime();
				try {
					$this->runCurrentHandler( $regKey );
				} catch ( Exception $ex ) {
					$message = $ex->getMessage();
					$message .= "\n";
					$message .= $ex->getTraceAsString();
					$this->logger->critical( $message );
				}
				$end = new DateTime();
				$handlerRunTime = $end->diff( $start );
				$formattedHandlerRunTime = $handlerRunTime->format( '%Im %Ss' );
				$this->logger->info( "Time: $formattedHandlerRunTime" );
			} else {
				$this->logger->info(
					"Skipped run of handler for '$regKey' due to"
					. " run-condition-check"
				);
			}
	}

	/**
	 * @param string $regKey
	 * @throws Exception
	 */
	protected function runCurrentHandler( $regKey ) {
		$status = $this->currentTriggerHandler->run();
		if ( $status->isOK() ) {
			$this->logger->info(
				"Successfully ran handler for '$regKey'"
			);
		} else {
			$messageText = $status->getMessage()->plain();
			$this->logger->error(
				"There was a error during run of handler for '$regKey':"
				. "\n$messageText"
			);
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

		$runner = new Runner( $logger );

		$runner->execute();

		return true;
	}
}
