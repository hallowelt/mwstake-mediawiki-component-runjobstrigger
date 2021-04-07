<?php

namespace MWStake;

use MWStake\RunJobsTrigger\Job\RunRunJobsTriggerRunner;
use MediaWiki\Logger\LoggerFactory;
use MWStake\RunJobsTrigger\JSONFileBasedRunConditionChecker;
use ConfigException;
use DateTime;
use JobQueueGroup;

class RunJobsTriggerRunner {

	/**
	 *
	 * @var IRegistry
	 */
	protected $registry = null;

	/**
	 *
	 * @varConfig
	 */
	protected $config = null;

	/**
	 *
	 * @varWikimedia\Rdbms\LoadBalancer
	 */
	protected $loadBalancer = null;

	/**
	 *
	 * @varPsr\Log\LoggerInterface
	 */
	protected $logger = null;

	/**
	 *
	 * @var INotifier
	 */
	protected $notifier = null;

	/**
	 *
	 * @var RunJobsTrigger\IRunConditionChecker
	 */
	protected $runConditionChecker = null;

	/**
	 *
	 * @var IRunJobsTrigger
	 */
	protected $currentTriggerHandler = null;

	/**
	 *
	 * @param IRegistry $registry
	 * @paramPsr\Log\LoggerInterface $logger
	 * @param RunJobsTrigger\IRunConditionChecker $runConditionChecker
	 * @paramConfig $config
	 * @paramWikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param INotifier $notifier
	 */
	public function __construct( $registry, $logger, $runConditionChecker, $config,
		$loadBalancer, $notifier ) {
		$this->registry = $registry;
		$this->logger = $logger;
		$this->runConditionChecker = $runConditionChecker;
		$this->config = $config;
		$this->loadBalancer = $loadBalancer;
		$this->notifier = $notifier;
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
					$this->loadBalancer,
					$this->notifier
				]
			);

			$this->checkHandlerInterface( $regKey );
			if ( $this->shouldRunCurrentHandler( $regKey ) ) {
				$this->logger->info( "Running handler for '$regKey'" );
				$start = new DateTime();
				try {
					$this->runCurrentHandler( $regKey );
				} catch (Exception $ex ) {
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
	 * @throwsException
	 */
	protected function checkHandlerInterface( $regKey ) {
		$doesImplementInterface =
			$this->currentTriggerHandler instanceof IRunJobsTrigger;

		if ( !$doesImplementInterface ) {
			throw new Exception(
				"RunJobsTriggerHanlder factory '$regKey' did not return "
					. "'IRunJobsTrigger' instance!"
			);
		}
	}

	/**
	 * @param string $regKey
	 * @throwsException
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

		JobQueueGroup::singleton()->push(
			new RunRunJobsTriggerRunner()
		);
	}

	/**
	 * Runs the runner immediately
	 *
	 * @return bool
	 * @throws ConfigException
	 */
	public static function run() {
		$services =MWStake\Services::getInstance();

		$registry = new ExtensionAttributeBasedRegistry(
			'MWStakeFoundationRunJobsTriggerRegistry'
		);

		$logger = LoggerFactory::getInstance( 'runjobs-trigger-runner' );

		$runConditionChecker = new JSONFileBasedRunConditionChecker(
			newDateTime(),
			BSDATADIR,
			$logger,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);

		$runner = newMWStake\RunJobsTriggerRunner(
			$registry,
			$logger,
			$runConditionChecker,
			$services->getConfigFactory()->makeConfig( 'bsg' ),
			$services->getDBLoadBalancer(),
			$services->getService( 'BSNotificationManager' )->getNotifier()
		);

		$runner->execute();

		return true;
	}
}
