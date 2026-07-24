<?php

namespace MWStake\MediaWiki\Component\RunJobsTrigger\Hook\LoadExtensionSchemaUpdates;

use MediaWiki\Installer\DatabaseUpdater;
use MWStake\MediaWiki\Component\RunJobsTrigger\Maintenance\ClearJSONFiles as Maintenance;

class ClearJSONFiles {

	/**
	 * @param DatabaseUpdater $updater
	 * @return bool
	 */
	public static function callback( $updater ) {
		$provider = new static( $updater );
		return $provider->process();
	}

	/** @var DatabaseUpdater */
	private $updater;

	/**
	 * @param DatabaseUpdater $updater
	 */
	public function __construct( $updater ) {
		$this->updater = $updater;
	}

	/**
	 * @return void
	 */
	public function process() {
		return $this->doProcess();
	}

	protected function doProcess() {
		$this->updater->addPostDatabaseUpdateMaintenance( Maintenance::class );
	}

}
