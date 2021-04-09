<?php

if ( defined( 'MWSTAKE_MEDIAWIKI_COMPONENT_RUNJOBSTRIGGER_VERSION' ) ) {
	return;
}

define( 'MWSTAKE_MEDIAWIKI_COMPONENT_RUNJOBSTRIGGER_VERSION', '1.0.0' );

$GLOBALS['mwsgRunJobsTriggerOptions'] = [
	"*" => [
		"basetime" => [ 1, 0, 0 ],
		"once-a-week-day" => "sunday"
	]
];

$GLOBALS['mwsgRunJobsTriggerHandlerRegistry'] = [];

$GLOBALS['mwsgRunJobsTriggerHandlerFactories'] = [
	'globalvars-config' => [
		'class' => "\\MWStake\\MediaWiki\\Component\\RunJobsTrigger\\HandlerFactory\\GlobalVars"
	],
	'hook' => [
		'class' => "\\MWStake\\MediaWiki\\Component\\RunJobsTrigger\\HandlerFactory\\Hook",
		'services' => [ 'MainConfig' ]
	]
];

$GLOBALS['mwsgRunJobsTriggerRunnerWorkingDir'] = sys_get_temp_dir();

$GLOBALS['wgJobClasses']['invokeRunner']
	= "\\MWStake\\MediaWiki\\Component\\RunJobsTrigger\\Job\\InvokeRunner";

$GLOBALS['wgExtensionFunctions'][]
	= "\\MWStake\\MediaWiki\\Component\\RunJobsTrigger\\Runner::runDeferred";
