<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\HandlerFactory;

use Config;
use GlobalVarConfig;
use MWStake\MediaWiki\Component\RunJobsTrigger\ObjectFactory;

class GlobalVars extends Base {

	/**
	 * @var Config
	 */
	private $config = null;

	/**
	 * @param Config|null $config
	 */
	public function __construct( $config = null ) {
		$this->config = $config;
		if ( $config === null ) {
			$this->config = new GlobalVarConfig( 'mwsgRunJobsTrigger' );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function processHandlers( $handlers ) {
		$handlerSpecs = $this->config->get( 'HandlerRegistry' );
		foreach ( $handlerSpecs as $handlerId => $handlerSpec ) {
			$handlers[$handlerId] = ObjectFactory::getObjectFromSpec( $handlerSpec );
		}

		return $handlers;
	}
}
