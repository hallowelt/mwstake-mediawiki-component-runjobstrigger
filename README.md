## MediaWiki Stakeholders Group - Components
# RunJobsTrigger for MediaWiki

Provides background tasks infrastructure based on MediaWikis `maintenance/runJobs.php`

## Prerequisites

MediaWiki's [`maintenance/runJobs.php` script](ttps://www.mediawiki.org/wiki/Manual:RunJobs.php) must be run periodically by a serverside process (e.g. cronjob on Linux, Scheduled Task on Windows).

The frequency of that job determines the minimum frequency at which handlers can be invoked. It is recommended to trigger this script at least every 15 minutes.

## Use in a MediaWiki extension

Add `"mwstake/mediawiki-component-runjobstrigger": "~1.0"` to the `require` section of your `composer.json`

### Implement a handler

Create a class that implements `MWStake\MediaWiki\Component\RunJobsTrigger\IHandler`. For convenience you may want to derive directly from the abstract base class `MWStake\MediaWiki\Component\RunJobsTrigger\HandlerBase`

In the `getInterval` method you can return any object that implements `MWStake\MediaWiki\Component\RunJobsTrigger\Interval`. There are a couple of predefined intevals available
- `MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceADay`
- `MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceAWeek`
- `MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceEveryHour`
- `MWStake\MediaWiki\Component\RunJobsTrigger\Interval\TwiceADay`

### Register a handler

There are two ways to register a handler:
1. Using the `attribute.mws.runjobtriggerhandlers` registry in `extension.json`
2. Using the hook `MWStakeRunJobsTriggerRegisterHandlers`

On both cases a [ObjectFactory specification](https://www.mediawiki.org/wiki/ObjectFactory) must be provided.

*Example 1: extension.json*

    {
        ...
        "attributes": {
            "mws": {
                "runjobtriggerhandlers": {
                    "my-own-handler": {
                        "class": "\\MediaWiki\Extension\\MyExt\\MyHandler",
                        "services": "MainConfig"
                    }
                }
            }
        }
    }

*Example 2: Hookhandler*

    $GLOBALS['wgHooks']['MWStakeRunJobsTriggerRegisterHandlers'][] = function( &$handlers ) {
        $handlers["my-own-handler"] = [
            'class' => '\\MediaWiki\Extension\\MyExt\\MyHandler,
            'services' => 'MainConfig'
        ]
    }

## Configuration
- `mwsgRunJobsTriggerRunnerWorkingDir`: 
- `mwsgRunJobsTriggerOptions`: 
- `mwsgRunJobsTriggerHandlerRegistry`: 

## Debugging
A debug log can be enabled by putting

    $GLOBALS['wgDebugLogGroups']['runjobs-trigger-runner'] = "/tmp/runjobs-trigger-runner.log";

to your `LocalSettings.php` file