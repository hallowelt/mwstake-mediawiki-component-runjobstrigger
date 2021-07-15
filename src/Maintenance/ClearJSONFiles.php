<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Maintenance;

use GlobalVarConfig;
use LoggedUpdateMaintenance;
use MWStake\MediaWiki\Component\RunJobsTrigger\JSONFileStatusManager;
use MWStake\MediaWiki\Component\RunJobsTrigger\ObjectFactory;

class ClearJSONFiles extends LoggedUpdateMaintenance {

	/**
	 *
	 * @return false
	 */
	protected function doDBUpdates() {
		$this->output( "{$this->getUpdateKey()}" );
		$config = new GlobalVarConfig( 'mwsgRunJobsTrigger' );
		$factories = $config->get( 'HandlerFactories' );
		$handlers = [];
		foreach ( $factories as $factorySpec ) {
			/** @var IHandlerFactory */
			$handlerFactory = ObjectFactory::getObjectFromSpec( $factorySpec );
			$handlers = $handlerFactory->processHandlers( $handlers );
		}
		$workingDir = $config->get( 'RunnerWorkingDir' );
		$statusManager = new JSONFileStatusManager( $workingDir );
		foreach ( $handlers as $handler ) {
			$statusManager->clear( $handler );
			$this->output( "." );
		}
		$this->output( "done\n" );
		return false;
	}

	/**
	 *
	 * @return string
	 */
	protected function getUpdateKey() {
		return "mwstake-runjobstrigger-clearrunningjobs";
	}

}
