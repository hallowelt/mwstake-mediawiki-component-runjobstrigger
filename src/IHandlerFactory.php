<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger;

interface IHandlerFactory {

	/**
	 * @param IHandler[] $handlers
	 * @return IHandler[]
	 * @throws Exception E.g. in case a IHandler object could not be constructed
	 */
	public function processHandlers( $handlers );
}
