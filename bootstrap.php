<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_RUNJOBSTRIGGER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_RUNJOBSTRIGGER_VERSION', '1.0.0' );

$GLOBALS['wgRunJobsTriggerOptions'] = [
	"*" => [
		"basetime" => [ 1, 0, 0 ],
		"once-a-week-day" => "sunday"
	]
];

$GLOBALS['wgJobClasses']['runRunJobsTriggerRunner']
	= "\\MWStake\\MediaWiki\\Component\\RunJobsTrigger\\Job\\RunRunJobsTriggerRunner";

$GLOBALS['wgExtensionFunctions'][]
	= "\\MWStake\\MediaWiki\\Component\\RunJobsTrigger\\Runner::runDeferred";