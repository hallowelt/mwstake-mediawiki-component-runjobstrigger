<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use ConfigException;
use DateTime;
use Exception;
use JobQueueGroup;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\RunJobsTrigger\Job\InvokeRunner;
use Psr\Log\LoggerInterface;
use Wikimedia\Rdbms\LoadBalancer;

class Runner {

	/**
	 *
	 * @var  IRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @var LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @var LoggerInterface
	 */
	protected $logger = null;

	/**
	 *
	 * @var  IRunJobsTrigger
	 */
	protected $currentTriggerHandler = null;

	/**
	 *
	 * @param IRegistry $registry
	 * @param LoggerInterface $logger
	 * @param Config $config
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( $registry, $logger, $config, $loadBalancer ) {
		$this->registry = $registry;
		$this->logger = $logger;
		$this->config = $config;
		$this->loadBalancer = $loadBalancer;
	}

	public function execute() {
		$this->logger->info( "Start processing at " . date( 'Y-m-d H:i:s' ) );
		$factoryKeys = $this->registry->getAllKeys();
		foreach ( $factoryKeys as $regKey ) {
			$factoryCallback = $this->registry->getValue( $regKey );
			$this->currentTriggerHandler = call_user_func_array(
				$factoryCallback,
				[
					$this->config,
					$this->loadBalancer
				]
			);

			$this->checkHandlerInterface( $regKey );
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
	}

	/**
	 *
	 * @param string $regKey
	 * @return bool
	 */
	protected function shouldRunCurrentHandler( $regKey ) {
		return $this->runConditionChecker->shouldRun(
			$this->currentTriggerHandler, $regKey
		);
	}

	/**
	 *
	 * @param string $regKey
	 * @throws Exception
	 */
	protected function checkHandlerInterface( $regKey ) {
		$doesImplementInterface =
			$this->currentTriggerHandler instanceof IHandler;

		if ( !$doesImplementInterface ) {
			throw new Exception(
				"Handler factory '$regKey' did not return "
					. "'IHandler' instance!"
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
		$services = MediaWikiServices::getInstance();

		$registry = new ExtensionAttributeBasedRegistry(
			'MWStakeFoundationRunJobsTriggerRegistry'
		);

		$logger = LoggerFactory::getInstance( 'runjobs-trigger-runner' );

		$runner = new Runner(
			$registry,
			$logger,
			$services->getMainConfig(),
			$services->getDBLoadBalancer()
		);

		$runner->execute();

		return true;
	}
}
