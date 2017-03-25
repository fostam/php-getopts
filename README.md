# fostam/getopts

Flexible PHP command line argument parser with automated help message generation and validation support.

## Install
The easiest way to install GetOpts is by using [composer](https://getcomposer.org/): 

```
$> composer require fostam/getopts
```

## Features
- Flexible and intuitive configuration
- Supports long/short options, option arguments, defaults, incrementable/negatable options etc.
- Non-option argument handling
- Automated usage error handling and help text generation
- Option / argument validation

## Usage

```php
<?php

include "vendor/autoload.php";

$getopts = new Fostam\GetOpts\Handler();

$getopts->addOption('verboseLevel')
    ->short('v')
    ->long('verbose')
    ->description('increase verbosity')
    ->incrementable();

$getopts->addArgument('outputFile')
    ->name('output-file')
    ->required();
    
$getopts->parse();

// show parsed options and arguments for demonstration purposes
var_dump($getopts->getOptions(), $getopts->getArguments());
```

Depending on the given options and arguments, the output could look like this (`$>` is the command line prompt).

**Option and argument values:**
```
$> php test.php -vvv hello
array(1) {
  ["verboseLevel"]=>
  int(3)
}
array(1) {
  ["outputFile"]=>
  string(5) "hello"
}
```

**Help text:**
```
$> php test.php -h
Usage: test.php [-v] OUTPUT-FILE
  -v    increase verbosity
```
**Missing arguments:**
```
$> php test.php
test.php: missing arguments
Usage: test.php [-v] OUTPUT-FILE
Try 'test.php --help' for more information
```

## Option Configuration
To add a new option to the GetOpts handler, the `addOption()` method is called. It takes the internal name for that option as argument. This internal name is later used to reference the option value in the parse result.

```php
$option = $handler->addOption('inputFile');
```

`addOption()` returns a new `Option` object that can be configured further by calling methods. Each configuration method again returns the option object itself, so that the calls can be chained.

If mismatching option configurations are called or naming constraints are not met, an `OptionConfigException` exception is thrown.

### Short Name
```php
$option->short('f');
```

This sets the short name for that option. Only a single alphanumeric character is allowed for the short name. In this example, the flag `-f` would match this option.

Note that different short options can be passed both individually and collapsed, e.g. `-a -b -c` or `-abc`, or any combination in between.

### Long Name
```php
$option->long('filename');
```

The long name for an option can consist of one or more alphanumeric characters, including `_`and `-`. In this example, passing `--filename` would match the option.

For each option, either the long name, the short name or both need to be configured.

### Description
For the automatic help text generation, an optional description can be given:
```php
$option->description('input file with data to be processed');
```

This help text would be generated for that option:
```
  -f, --filename=INPUT-FILE       input file with data to be processed
```

### Option Argument
```php
$option->short('f')->argument('input-file');
```

Specifies that this option requires an additional argument, e.g. `-f myfile.txt`.

The name that is passed to `argument()` is used for the automatic *Usage* string/help text generation. It is used in it's uppercase version.

Parsing will fail if the argument is missing.

If the option has a long name, the argument can be passed both with and without `=`, i.e. either `--file file1.txt` or `--file=file1.txt`.

### Multiple Occurrences
Options that require an argument can be configured so that they can be given multiple times. In that case the option will always return an `array` as value, even if given only once.
```php
$option->short('f')->argument('input-file')->multiple();
```

A call passing `-f file1.txt -f file2.txt` would result in this result array:
```php
array(2) {
  [0]=>
  string(9) "file1.txt"
  [1]=>
  string(9) "file2.txt"
}
```

### Incrementable
If the value of an option should be incremented each time it is provided, it can be set to incrementable:
```php
$handler->addOption('verboseLevel')->short('v')->incrementable();
```

`-v -v -v` or `-vvv` would thus result in the value `3` for the `verboseLevel` option.

The default increment of `1` can be changed by passing an integer to `incrementable()`:
```php
$handler->addOption('verboseLevel')->short('v')->incrementable(5);
```

Incrementable options cannot have option arguments (i.e. `incrementable()` and `argument()`/`multiple()` are mutual exclusive).

### Required
```php
$option->required();
```

This will make the option mandatory. Parsing will fail if it is not provided.

### Default Value
By default, an option that is not provided will be `null` in the result array. This behaviour can be changed by configuring an optional default value:
```php
$handler->addOption('inputFile')->short('f')->defaultValue('file1.txt');
$handler->addOption('maxValue')->short('m')->defaultValue(5);
```

### Negatable
Flag options (i.e. options without argument and not *incrementable*) can be made negatable when they have a long name. To negate the option, the long name is prefixed with `no`.
```php
$option->long('test')->negatable();
```

For `--test`, the result value will be `true` as usual, but for `--notest`, the result value will be `false`.
This is particularly useful in combination with a *default value* of `true`.

### Validation
An optional validator function can be specified for an option:
```php
$option->validator(function($value) {
    if ($value < 0 || $value > 50) {
        return false;
    }
    return true;
});
```

`validator()` takes any `callable` as option, e.g. a function, a closure, a class/object method etc.
The callable is passed the option value as argument and must return either `true` or `false`, depending on the validation result.

If any option validator returns `false`, option parsing fails.

## Argument Configuration
Argument configuration works similarly to option configuration. To add an argument, the handler's `addArgument()` method is called, returning an `Argument` object:
```php
$option = $handler->addArgument('outputFile');
```

Like `Option`, the `Argument` configuration methods are chainable.

If mismatching argument configurations are called or naming constraints are not met, an `ArgumentConfigException` exception is thrown.

### Name
```php
$arg->name('output-file');
```
This name is used for creating the automated *Usage* string. If not provided, `"ARGUMENT"` is used as default. If you are relying on the automated *Usage* string generation, you should always configure `name`, so that the created text is user-friendly.

### Multiple Occurrences
An argument can be configured to be passable multiple times. To avoid ambiguous configurations, only the **last** argument can be configured that way.
```php
$arg->name('output-file')->multiple();
```
In the result array, this argument will always return an `array`.

### Default Value
Non-mandatory arguments that are omitted will be `null` in the result object. When configuring a *default value*, this value is returned instead:
```php
$arg->name('output-file')->defaultValue('file.txt');
```

### Required
```php
$arg->name('output-file')->required();
```
This will make the argument mandatory. To avoid ambiguity, a *required* argument cannot be preceded by non-mandatory arguments.

### Validation
Argument validation works in the same way as option validation:
```php
$arg->validator(function($value) {
    if ($value < 0 || $value > 50) {
        return false;
    }
    return true;
});
```
The given `callable` is passed the argument value as input and must return `true` or `false`.

## Parse Results
### Options
```php
$optionArray = $handler->getOptions();
```

Each option that has been configured will be present as **key** in the result array, regardless whether it was given or not.
The **value** of each option depends on the configuration:
- if not given at all, the value is `null`, or the optional *default value* if configured
- options that don't require an argument are `boolean`, i.e. when given they will be `true`
- *negatable* options will be set to `false` when given in their negated form
- *incrementable* options are returned as `integer`
- *option arguments* are always returned as `string`

### Arguments
```php
$argumentArray = $handler->getArguments();
```
Each argument that has been configured will be present as **key** in the result array, regardless whether it was given or not.
The **value** of each argument is `null` if not given at all, or the optional *default value* if configured. Otherwise, the value is a `string`.

### Script Name
The script name is the name of the PHP script that has been executed:
```
$> php myscript.php -v file.txt
```

```php
$scriptName = $handler->getScriptName();  // would return "myscript.php"
```

### Parse Errors
If the input argument parsing fails, e.g. because requirement constraints have not been met or argument validators have returned `false`, argument processing is stopped immediately and the following steps are executed:
- an error message telling what went wrong is printed to STDERR
- the *Usage* string is created and printed to STDERR
- a hint for the help text option is printed to STDERR, unless help text generation has been turned off
- script execution is stopped with `exit(2)`, resulting in a command return code of `2`
- thus, no code beyond the `$handler->parse()` call is executed

If required, the automated error handling can be turned off. See *Advanced Features* for details.

### Help Text
When the default help option (short `-h` or long `--help`) is given, argument processing is stopped immediately and the following steps are executed:
- the *Usage* string is created and printed to STDOUT
- the help text is created and printed to STDOUT
- script execution is stopped with `exit(0)`, resulting in a command return code of `0`
- thus, no code beyond the `$handler->parse()` call is executed

The short and long help option names can be customized or deactivated completely. See *Advanced Features* for details.

## Advanced Features
### Input Arguments
By default, input arguments are taken from `$_SERVER['argv']`. If you don't want that - e.g. because you are using a framework that provides script arguments by other means - you can override the default behaviour and pass an optional custom input argument array to the handler's `parse()` method.
```php
$handler->parse([
    'myscript.php',
    '-v',
    '-f',
    'file.txt'
]);
```
The array must consist of strings, where each string represents exactly one argument to the script. The arguments are taken from the array by it's internal order; the array's keys are ignored.

**IMPORTANT:** the first element of the array must be the script name of the executed PHP script. This is the value returned by `$handler->getScriptName()`. If you don't have or need the script name, put an empty string as first element.

### Error Handling
If you don't want the automated error handling to take over, e.g. because you want to keep control about when your script is exiting, or you want to provide error messages in a different language, you can disable the default behaviour:
```php
$handler->disableErrorHandling();
```
When disabled, the `$handler->parse()` method throws one of the following `UsageException` exceptions in case of a parse error:
- `UnrecognizedOptionException`: an unconfigured option has been provided
- `MissingOptionsException`: one or more *required* options have not been provided
- `InvalidOptionException`: the validator for an option has returned `false`
- `MissingOptionArgumentException`: the argument for an option has not been provided
- `MissingArgumentsException`: one or more *required* arguments have not been provided
- `TooManyArgumentsException`: more arguments have been passed than configured
- `InvalidArgumentException`: the validator for an argument has returned `false`

The exception's message is the error text that is printed when using the automated error handling.
Some of the exceptions have additional methods to retrieve data that can be used to create more detailed custom error messages.

You can still get the automated *Usage* string:
```php
$handler->getUsageString();
```

### Help Options
An additional custom help text can be set that is printed below the automated help text. The custom text should be terminated with a line break (`PHP_EOL`).
```php
$handler->setExtraHelpText($txt);
```

When a lot of options have been configured, the *Usage* string can become quite long. If you rather want a short *Usage* string without option details, the terse *Usage* string can be enabled. Options will then be represented by `"[OPTION] ..."` in the string.
```php
$handler->enableTerseUsage();
```

With the following handler methods, the options of the in-built help function can be changed:
```php
$handler->setHelpOptionLong('help');
$handler->setHelpOptionShort('h');
```
By setting both values to the empty string or `false`, the help text feature can be disabled. In that case, the help hint on parse errors will no longer be shown, either.

You can still access the generated help text:
```php
$handler->getHelpText();
```

## License

fostam/GetOpts is published under the [MIT](https://opensource.org/licenses/mit-license.php) license. See [License File](LICENSE) for more information.
