<?php

namespace MWStake;

use MWStake\RunJobsTrigger\Interval\OnceADay;

abstract class RunJobsTrigger implements IRunJobsTrigger {

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
	 * @var INotifier
	 */
	protected $notifier = null;

	/**
	 * @paramConfig $config
	 * @paramWikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param INotifier $notifier
	 * @return IRunJobsTrigger
	 */
	public static function factory( $config, $loadBalancer, $notifier ) {
		$className = static::class;
		return new $className( $config, $loadBalancer, $notifier );
	}

	/**
	 *
	 * @paramConfig $config
	 * @paramWikimedia\Rdbms\LoadBalancer $loadBalancer
	 * @param INotifier $notifier
	 */
	public function __construct( $config, $loadBalancer, $notifier ) {
		$this->config = $config;
		$this->loadBalancer = $loadBalancer;
		$this->notifier = $notifier;
	}

	/**
	 *
	 * @returnStatus
	 */
	public function run() {
		return $this->doRun();
	}

	/**
	 * @returnStatus
	 */
	abstract protected function doRun();

	/**
	 *
	 * @return RunJobsTrigger\Interval
	 */
	public function getInterval() {
		return new OnceADay();
	}
}
