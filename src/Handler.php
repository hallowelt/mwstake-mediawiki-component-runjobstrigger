<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

use MediaWiki\Config\Config;
use MediaWiki\Status\Status;
use MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceADay;
use Wikimedia\Rdbms\LoadBalancer;

abstract class Handler implements IHandler {

	/** @var Config */
	protected $config;

	/** @var LoadBalancer */
	protected $loadBalancer;

	/**
	 * @param Config $config
	 * @param LoadBalancer $loadBalancer
	 * @return IHandler
	 */
	public static function getInstance( $config, $loadBalancer ) {
		$className = static::class;
		return new $className( $config, $loadBalancer );
	}

	/**
	 * @param Config $config
	 * @param LoadBalancer $loadBalancer
	 */
	public function __construct( $config, $loadBalancer ) {
		$this->config = $config;
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 * @return Status
	 */
	public function run() {
		return $this->doRun();
	}

	/**
	 * @return Status
	 */
	abstract protected function doRun();

	/**
	 * @return Interval
	 */
	public function getInterval() {
		return new OnceADay();
	}

	/**
	 * @inheritDoc
	 */
	public function getKey() {
		return str_replace( '\\', '-', strtolower( static::class ) );
	}
}
