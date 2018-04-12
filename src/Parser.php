<?php

namespace Fostam\GetOpts;

use Fostam\GetOpts\Config\Argument;
use Fostam\GetOpts\Config\Option;
use Fostam\GetOpts\Exception\ArgumentConfigException;
use Fostam\GetOpts\Exception\InvalidArgumentException;
use Fostam\GetOpts\Exception\InvalidOptionException;
use Fostam\GetOpts\Exception\MissingArgumentsException;
use Fostam\GetOpts\Exception\MissingOptionArgumentException;
use Fostam\GetOpts\Exception\MissingOptionsException;
use Fostam\GetOpts\Exception\ShowHelpException;
use Fostam\GetOpts\Exception\TooManyArgumentsException;
use Fostam\GetOpts\Exception\UnrecognizedOptionException;

/**
 * Class Parser
 * @package Fostam\GetOpts
 */
class Parser {
    const HELP_OPTION_NAME = '__HELP';
    const RESULT_OPTIONS   = 'options';
    const RESULT_ARGUMENTS = 'arguments';

    /** @var Option[] $configOpts */
    private $configOpts = [];
    /** @var Argument[] $configArgs */
    private $configArgs = [];

    private $usedName = [];
    private $options = [];
    private $arguments = [];
    private $argumentsRaw = [];

    private $optsShort = [];
    private $optsLong = [];


    /**
     * Parser constructor.
     * @param $opts
     * @param $args
     */
    public function __construct($opts = [], $args = []) {
        $this->configOpts = $opts;
        $this->configArgs = $args;
    }

    /**
     * @param array $args
     */
    public function parse($args) {
        $this->collectShortLong();
        $this->processOptions($args);
        $this->validateOptions();
        $this->validateArguments();
    }

    /**
     * @return array
     * @internal param $options
     * @internal param $arguments
     */
    public function getResult() {
        return [
            self::RESULT_OPTIONS   => $this->options,
            self::RESULT_ARGUMENTS => $this->arguments,
        ];
    }

    /**
     * @param $args
     * @throws MissingOptionArgumentException
     * @throws ShowHelpException
     * @throws UnrecognizedOptionException
     */
    private function processOptions($args) {
        // process options
        $lastOpt = false;
        $optsFinished = false;
        $expectParam = false;

        // iterate through the array with "for" instead of "foreach"(safer
        // manipulation of the array while looping over it)
        $args = array_values($args);
        $numArgs = count($args);
        for($optNr=0; $optNr<$numArgs; $optNr++) {
            $arg = $args[$optNr];
            if ($optsFinished) {
                $this->argumentsRaw[] = $arg;
                continue;
            }

            if ($expectParam) {
                // option param
                if ($this->configOpts[$lastOpt]->get(Option::MULTIPLE)) {
                    if (empty($this->options[$lastOpt]) || !is_array($this->options[$lastOpt])) {
                        $this->options[$lastOpt] = [];
                    }
                    $this->options[$lastOpt][] = $arg;
                } else {
                    $this->options[$lastOpt] = $arg;
                }
                $lastOpt = false;
                $expectParam = false;
            } else if ($arg == '--') {
                // end of option processing
                $optsFinished = true;
            } else if ($arg == '-') {
                // treat single hyphen as argument
                $this->argumentsRaw[] = $arg;
                $lastOpt = false;
            } else if (substr($arg, 0, 2) == '--') {
                // long opt
                $opt = substr($arg, 2, strlen($arg) - 2);

                // handle option arguments appended with equal
                $argSplit = explode('=', $opt);
                if (isset($argSplit[1])) {
                    $opt = $argSplit[0];
                    array_splice($args, $optNr+1, 0, $argSplit[1]);
                    $numArgs++;
                }

                $negated = false;
                if (substr($opt, 0, 2) == 'no') {
                    $optNeg = substr($opt, 2, strlen($arg) - 2);
                    if (isset($this->optsLong[$optNeg]) && $this->configOpts[$this->optsLong[$optNeg]]->get(Option::NEGATABLE)) {
                        $opt = $optNeg;
                        $negated = true;
                    }
                }

                if (!isset($this->optsLong[$opt])) {
                    throw new UnrecognizedOptionException($arg);
                }

                if ($this->optsLong[$opt] == self::HELP_OPTION_NAME) {
                    throw new ShowHelpException();
                }

                $lastOpt = $this->optsLong[$opt];
                $expectParam = $this->addOption($lastOpt, $arg, $negated);
            } else if (substr($arg, 0, 1) == '-') {
                // short opt(s)
                $opts = substr($arg, 1, strlen($arg) - 1);

                for ($i = 0; $i < strlen($opts); $i++) {
                    $opt = $opts[$i];

                    if (!isset($this->optsShort[$opt])) {
                        throw new UnrecognizedOptionException($arg);
                    }

                    if ($this->optsShort[$opt] == self::HELP_OPTION_NAME) {
                        throw new ShowHelpException();
                    }

                    $lastOpt = $this->optsShort[$opt];
                    $expectParam = $this->addOption($lastOpt, $arg);
                }
            } else {
                // argument
                $this->argumentsRaw[] = $arg;
                $lastOpt = false;
            }
        }

        if ($expectParam) {
            throw new MissingOptionArgumentException($this->usedName[$lastOpt]);
        }
    }

    /**
     * @param string $option
     * @param string $value
     * @param bool $negated
     * @return bool
     */
    private function addOption($option, $value, $negated = false) {
        $this->usedName[$option] = $value;
        $expectParam = true;
        if ($this->configOpts[$option]->get(Option::INCREMENTABLE)) {
            if (!isset($this->options[$option])) {
                $this->options[$option] = 0;
            }
            $this->options[$option] += $this->configOpts[$option]->get(Option::INCREMENTABLE);
            $expectParam = false;
        } else if (!$this->configOpts[$option]->get(Option::ARGUMENT)) {
            // option is a flag
            $this->options[$option] = !$negated;
            $expectParam = false;
        }

        return $expectParam;
    }

    /**
     * @throws InvalidOptionException
     */
    private function validateOptions() {
        // validate values and fill unspecified options with default values
        foreach($this->configOpts as $name => $configOpt) {
            if ($name === self::HELP_OPTION_NAME) {
                continue;
            }

            if (!isset($this->options[$name])) {
                if ($configOpt->get(Option::REQUIRED)) {
                    throw new MissingOptionsException();
                }

                $this->options[$name] = $configOpt->get(Option::DEFAULT_VALUE);
            }
            else {
                // validate
                if ($configOpt->get(Option::MULTIPLE)) {
                    $values = $this->options[$name];
                }
                else {
                    $values = [$this->options[$name]];
                }

                foreach($values as $value) {
                    if ($configOpt->get(Option::VALIDATOR)) {
                        /** @var callable $validator */
                        $validator = $configOpt->get(Option::VALIDATOR);
                        if (!$validator($value)) {
                            throw new InvalidOptionException($this->usedName[$name], $value);
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingArgumentsException
     * @throws TooManyArgumentsException
     */
    private function validateArguments() {
        $nonRequiredSet = false;
        $multipleSet = false;

        $arguments = $this->argumentsRaw;
        foreach($this->configArgs as $name => $configArg) {
            if (!$configArg->get(Argument::REQUIRED)) {
                $nonRequiredSet = true;
            }
            else if ($nonRequiredSet) {
                throw new ArgumentConfigException('required arguments cannot follow non-required arguments');
            }

            if ($configArg->get(Argument::MULTIPLE)) {
                $multipleSet = true;
            }
            else if ($multipleSet) {
                throw new ArgumentConfigException("only the last argument can be 'multiple'");
            }

            $arg = array_shift($arguments);
            if (is_null($arg)) {
                if ($configArg->get(Argument::REQUIRED)) {
                    throw new MissingArgumentsException();
                }
                else {
                    // argument processing has finished; fill with default value
                    $this->arguments[$name] = $configArg->get(Argument::DEFAULT_VALUE);
                }
            }
            else {
                // validate
                if ($configArg->get(Argument::VALIDATOR)) {
                    /** @var callable $validator */
                    $validator = $configArg->get(Option::VALIDATOR);
                    if (!$validator($arg)) {
                        throw new InvalidArgumentException($arg);
                    }
                }

                if ($multipleSet) {
                    $this->arguments[$name] = [$arg];
                }
                else {
                    $this->arguments[$name] = $arg;
                }
            }
        }

        // if arguments are left, check if the last configured argument is 'multiple'
        if ($arguments) {
            if (isset($name) && isset($configArg) && $configArg->get(Argument::MULTIPLE)) {
                $this->arguments[$name] = array_merge($this->arguments[$name], $arguments);
            }
            else {
                throw new TooManyArgumentsException();
            }
        }
    }

    /**
     * collect short/long opts
     */
    private function collectShortLong() {
        foreach ($this->configOpts as $name => $configOpt) {
            if ($configOpt->get(Option::LONG)) {
                $this->optsLong[$configOpt->get(Option::LONG)] = $name;
            }
            if ($configOpt->get(Option::SHORT)) {
                $this->optsShort[$configOpt->get(Option::SHORT)] = $name;
            }
        }
    }
}
