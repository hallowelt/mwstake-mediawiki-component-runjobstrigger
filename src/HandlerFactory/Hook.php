<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\HandlerFactory;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\RunJobsTrigger\ObjectFactory;

class Hook extends Base {

	/** @var Config */
	private $config;

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
		$handlerSpecs = [];
		MediaWikiServices::getInstance()->getHookContainer()->run(
			'MWStakeRunJobsTriggerRegisterHandlers',
			[ &$handlerSpecs ]
		);
		foreach ( $handlerSpecs as $handlerId => $handlerSpec ) {
			$handlers[$handlerId] = ObjectFactory::getObjectFromSpec( $handlerSpec );
		}

		return $handlers;
	}
}
