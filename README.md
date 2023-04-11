## MediaWiki Stakeholders Group - Components
# RunJobsTrigger for MediaWiki

Provides an infrastructure to execute background tasks based on MediaWiki's `maintenance/runJobs.php`.

**This code is meant to be used within the MediaWiki framework. Do not attempt to use it outside of MediaWiki.**

## Prerequisites

MediaWiki's [`maintenance/runJobs.php` script](https://www.mediawiki.org/wiki/Manual:RunJobs.php) must be run periodically by a serverside process (*e.g.* in a cronjob on Linux or Scheduled Task on Windows).

The frequency of that job determines the minimum frequency at which handlers can be invoked. It is recommended to invoke `maintenance/runJobs.php` every 15 minutes at a minimum.

## Use in a MediaWiki extension

Add `"mwstake/mediawiki-component-runjobstrigger": "~2.0"` to the `require` section of your `composer.json` file.

Since 2.0 explicit initialization is required. This can be achived by
- either adding `"callback": "mwsInitComponents"` to your `extension.json`/`skin.json`
- or calling `mwsInitComponents();` within you extensions/skins custom `callback` method

See also [`mwstake/mediawiki-componentloader`](https://github.com/hallowelt/mwstake-mediawiki-componentloader).

### Implement a handler

Create a class that implements `MWStake\MediaWiki\Component\RunJobsTrigger\IHandler`. For convenience, you may want to implement a subclass of the abstract base class `MWStake\MediaWiki\Component\RunJobsTrigger\Handler`

In the `getInterval` method you can return any object that implements `MWStake\MediaWiki\Component\RunJobsTrigger\Interval`. There are a few predefined intevals available:
- `MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceADay`
- `MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceAWeek`
- `MWStake\MediaWiki\Component\RunJobsTrigger\Interval\OnceEveryHour`
- `MWStake\MediaWiki\Component\RunJobsTrigger\Interval\TwiceADay`

### Register a handler

There are two ways to register a handler:
1. Using the `mwsgRunJobsTriggerHandlerRegistry` GlobalVars configuraton
2. Using the hook `MWStakeRunJobsTriggerRegisterHandlers`

In both cases, an [ObjectFactory specification](https://www.mediawiki.org/wiki/ObjectFactory) must be provided.

*Example 1: GlobalVars*
```php
$GLOBALS['mwsgRunJobsTriggerHandlerRegistry']['my-own-handler'] = [
    'class' => '\\MediaWiki\Extension\\MyExt\\MyHandler',
    'services' => [ 'MainConfig' ]
];
```
*Example 2: Hookhandler*
```php
$GLOBALS['wgHooks']['MWStakeRunJobsTriggerRegisterHandlers'][] = function( &$handlers ) {
    $handlers["my-own-handler"] = [
        'class' => '\\MediaWiki\Extension\\MyExt\\MyHandler',
        'services' => [ 'MainConfig' ]
    ];
    return true;
};
```

## Configuration
- `mwsgRunJobsTriggerRunnerWorkingDir`: Where to store data during execution. Defaults to the [operating system's temp dir](https://php.net/sys_get_temp_dir).
- `mwsgRunJobsTriggerOptions`: Timing options for particular handlers.
- `mwsgRunJobsTriggerHandlerRegistry`: Add your own trigger handlers.

## Configuration examples

### Using MediaWiki’s temporary directory to store data during execution

Suppose an administrator wants to ensure that they can ensure any temporary files are created in MediaWiki’s temporary directory rather than somewhere else.  They could do this by adding the following to their `LocalSettings.php`:

```php
$GLOBALS['mwsgRunJobsTriggerRunnerWorkingDir'] = $wgTmpDirectory;
```

### Changing the timing options

A wiki administrator could add the following to their `LocalSettings.php` to have `OnceAWeek` tasks run on Friday instead of Sunday (by default):

```php
$GLOBALS['mwsgRunJobsTriggerOptions']['*']['once-a-week-day'] = 'friday';
```

## Debugging
A debug log can be enabled by adding

```php
$GLOBALS['wgDebugLogGroups']['runjobs-trigger-runner'] = "/tmp/runjobs-trigger-runner.log";
```

to your `LocalSettings.php` file
