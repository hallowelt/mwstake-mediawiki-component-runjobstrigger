<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\HandlerFactory;

use Exception;
use MWStake\MediaWiki\Component\RunJobsTrigger\IHandler;
use MWStake\MediaWiki\Component\RunJobsTrigger\IHandlerFactory;

abstract class Base implements IHandlerFactory {

	/**
	 *
	 * @var IHandler
	 */
	protected $currentTriggerHandler = null;

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
}
