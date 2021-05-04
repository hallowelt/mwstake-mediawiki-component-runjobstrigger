<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\HandlerFactory;

use Config;
use Hooks;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\RunJobsTrigger\ObjectFactory;

class Hook extends Base {

	/**
	 * @var Config
	 */
	private $config = null;

	/**
	 * @param Config|null $config
	 */
	public function __construct( $config = null ) {
		$this->config = $config;

		// MediaWiki 1.31 B/C
		if ( $config === null ) {
			$this->config = MediaWikiServices::getInstance()->getMainConfig();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function processHandlers( $handlers ) {
		$mwVersion = $this->config->get( 'Version' );
		$handlerSpecs = [];
		if ( version_compare( $mwVersion, '1.35', '>=' ) ) {
			$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
			$hookContainer->run( 'MWStakeRunJobsTriggerRegisterHandlers', [ &$handlerSpecs ] );
		} else {
			Hooks::run( 'MWStakeRunJobsTriggerRegisterHandlers', [ &$handlerSpecs ] );
		}
		foreach ( $handlerSpecs as $handlerId => $handlerSpec ) {
			$handlers[$handlerId] = ObjectFactory::getObjectFromSpec( $handlerSpec );
		}

		return $handlers;
	}
}
