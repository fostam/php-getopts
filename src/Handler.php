<?php

namespace Fostam\GetOpts;

use Fostam\GetOpts\Config\Argument;
use Fostam\GetOpts\Config\Option;
use Fostam\GetOpts\Exception\ConfigException;
use Fostam\GetOpts\Exception\ShowHelpException;
use Fostam\GetOpts\Exception\UsageException;
use LogicException;

/**
 * Class Handler
 * @package Fostam\GetOpts
 */
class Handler {
    /** @var Option[] $configOpts */
    private $configOpts = [];
    /** @var Argument[] $configArgs */
    private $configArgs = [];

    private $scriptName = 'php-script';

    private $result;

    private $helpShort = 'h';
    private $helpLong = 'help';
    private $errorHandling = true;
    private $terseUsage = false;
    private $extraHelp = '';


    /**
     * Parser constructor.
     */
    public function __construct() {
    }

    /**
     * add a new option
     *
     * @param string $name
     * @param Option $opt
     * @return Option
     * @throws ConfigException
     */
    public function addOption($name, Option $opt = null) {
        if (isset($this->configOpts[$name]) || isset($this->configArgs[$name])) {
            throw new ConfigException('option/argument already set: ' . $name);
        }

        if (is_null($opt)) {
            $opt = new Option();
        }

        $this->configOpts[$name] = $opt;
        return $opt;
    }

    /**
     * add a new argument
     *
     * @param $name
     * @param Argument $arg
     * @return Argument
     * @throws ConfigException
     */
    public function addArgument($name, Argument $arg = null) {
        if (isset($this->configOpts[$name]) || isset($this->configArgs[$name])) {
            throw new ConfigException('option/argument already set: ' . $name);
        }

        if (is_null($arg)) {
            $arg = new Argument();
        }

        $this->configArgs[$name] = $arg;
        return $arg;
    }

    /**
     * set the long option for help handling
     *
     * @param string $str
     * @return $this
     */
    public function setHelpOptionLong($str) {
        if (!Option::validateLong($str)) {
            throw new ConfigException('long help option has invalid format');
        }
        $this->helpLong = $str;
        return $this;
    }

    /**
     * set the short option for help handling
     *
     * @param string $str
     * @return $this
     */
    public function setHelpOptionShort($str) {
        if (!Option::validateShort($str)) {
            throw new ConfigException('short help option must be a single character');
        }
        $this->helpShort = $str;
        return $this;
    }

    /**
     * disabled built-in error handling and throw exceptions instead
     *
     * @return $this
     */
    public function disableErrorHandling() {
        $this->errorHandling = false;
        return $this;
    }

    /**
     * don't list options in Usage string
     *
     * @return $this
     */
    public function enableTerseUsage() {
        $this->terseUsage = true;
        return $this;
    }

    /**
     * set additional help text that is appended below the generated help
     *
     * @param string $text
     * @return $this
     */
    public function setExtraHelpText($text) {
        $this->extraHelp = $text;
        return $this;
    }

    /**
     * parse arguments
     * the first element in the array is treated as script name
     *
     * @param array $inputArgs
     * @throws UsageException
     */
    public function parse($inputArgs = null) {
        $args = $this->getCommandLineArgs($inputArgs);
        if (count($args)) {
            $this->scriptName = array_shift($args);
        }

        // build help option
        $helpOpt = false;
        if ($this->helpLong) {
            $helpOpt = new Option();
            $helpOpt->long($this->helpLong);
        }
        if ($this->helpShort) {
            if (!$helpOpt) {
                $helpOpt = new Option();
            }
            $helpOpt->short($this->helpShort);
        }
        if ($helpOpt) {
            $this->configOpts[Parser::HELP_OPTION_NAME] = $helpOpt;
        }

        try {
            $parser = new Parser($this->configOpts, $this->configArgs);
            $parser->parse($args);
        }
        catch (UsageException $e) {
            if ($this->errorHandling) {
                $this->handleUsageError($e);
            }
            else {
                // re-throw exception
                throw $e;
            }
        }
        catch (ShowHelpException $e) {
            $this->showHelp();
        }

        $this->result = $parser->getResult();
    }

    /**
     * @param string $name
     * @return array
     */
    public function get($name = '') {
        if (is_null($this->result)) {
            $this->parse();
        }

        if (!$name) {
            return array_merge($this->result[Parser::RESULT_OPTIONS], $this->result[Parser::RESULT_ARGUMENTS]);
        }

        if (array_key_exists($name, $this->result[Parser::RESULT_OPTIONS])) {
            return $this->result[Parser::RESULT_OPTIONS][$name];
        }

        if (array_key_exists($name, $this->result[Parser::RESULT_ARGUMENTS])) {
            return $this->result[Parser::RESULT_ARGUMENTS][$name];
        }

        throw new LogicException('option/argument not defined: ' . $name);
    }

    /**
     * get the parsed options and their values
     *
     * @return array
     * @throws LogicException
     */
    public function getOptions() {
        if (is_null($this->result)) {
            $this->parse();
        }
        return $this->result[Parser::RESULT_OPTIONS];
    }

    /**
     * get parsed argument values
     *
     * @return array
     * @throws LogicException
     */
    public function getArguments() {
        if (is_null($this->result)) {
            $this->parse();
        }
        return $this->result[Parser::RESULT_ARGUMENTS];
    }

    /**
     * get the generated usage string (without script name and EOL)
     *
     * @return string
     */
    public function getUsageString() {
        return $this->buildUsageString();
    }

    /**
     * get the generated help text (might be multiple lines, each line terminated by an EOL)
     *
     * @return string
     */
    public function getHelpText() {
        return $this->buildHelpText();
    }

    /**
     * get the name of the PHP script
     *
     * @return string
     */
    public function getScriptName() {
        return $this->scriptName;
    }

    /**
     * @param UsageException $e
     */
    private function handleUsageError(UsageException $e) {
        $str = '';
        $str .= $this->scriptName . ': ' . $e->getMessage() . PHP_EOL;
        $str .= 'Usage: ' . $this->getUsageString() . PHP_EOL;
        if ($this->helpShort || $this->helpLong) {
            $str .= "Try '{$this->scriptName} ";
            $str .= ($this->helpLong ? '--' . $this->helpLong : '-' . $this->helpShort);
            $str .= "' for more information." . PHP_EOL;
        }

        // try to print to STDERR
        $stderr = @fopen('php://stderr', 'w');
        if ($stderr !== false) {
            fputs($stderr, $str);
            fclose($stderr);
        }
        else {
            // fallback to STDOUT
            print $str;
        }

        exit(2);
    }

    /**
     * @param $args
     * @return array
     * @throws ConfigException
     */
    private function getCommandLineArgs($args) {
        if (!is_null($args)) {
            if (is_array($args)) {
                return $args;
            } else {
                throw new ConfigException('arguments must be an array');
            }
        } else if (isset($_SERVER['argv'])) {
            return $_SERVER['argv'];
        } else {
            throw new ConfigException('no options to parse');
        }
    }

    /**
     * @return string
     */
    private function buildUsageString() {
        $str = $this->scriptName . ' ';

        // options
        if ($this->terseUsage && $this->configOpts) {
            $str .= '[OPTION]... ';
        }
        else {
            foreach ($this->configOpts as $name => $configOpt) {
                $opt = '';
                if ($configOpt->get(Option::SHORT)) {
                    $opt .= '-' . $configOpt->get(Option::SHORT);
                    if ($configOpt->get(Option::LONG)) {
                        $opt .= '|';
                    }
                }

                if ($configOpt->get(Option::LONG)) {
                    $opt .= '--' . $configOpt->get(Option::LONG);
                }

                if ($configOpt->get(Option::ARGUMENT)) {
                    $opt .= ' ' . strtoupper($configOpt->get(Option::ARGUMENT));
                }

                if (!$configOpt->get(Option::REQUIRED)) {
                    $opt = '[' . $opt . ']';
                }

                if ($configOpt->get(Option::MULTIPLE)) {
                    $opt .= '...';
                }

                $str .= $opt . ' ';
            }
        }

        // arguments
        foreach($this->configArgs as $name => $configArg) {
            $arg = strtoupper($configArg->get(Argument::NAME));
            if (!$arg) {
                $arg = 'ARGUMENT';
            }

            if (!$configArg->get(Argument::REQUIRED)) {
                $arg = '[' . $arg . ']';
            }

            if ($configArg->get(Option::MULTIPLE)) {
                $arg .= '...';
            }

            $str .= $arg . ' ';
        }

        return rtrim($str);
    }

    /**
     *
     */
    private function showHelp() {
        print 'Usage: ' . $this->getUsageString() . PHP_EOL;
        print $this->buildHelpText();
        exit(0);
    }

    /**
     * @return string
     */
    private function buildHelpText() {
        $opts = [];
        $maxLeft = 0;
        foreach($this->configOpts as $name => $configOpt) {
            $left = '';
            if ($configOpt->get(Option::SHORT)) {
                $left .= '-' . $configOpt->get(Option::SHORT);
                if ($configOpt->get(Option::LONG)) {
                    $left .= ', ';
                }
            }

            if ($configOpt->get(Option::LONG)) {
                $left .= '--' . $configOpt->get(Option::LONG);
            }

            if ($configOpt->get(Option::ARGUMENT)) {
                $left .= '=' . strtoupper($configOpt->get(Option::ARGUMENT));
            }

            $len = strlen($left);
            if ($len > $maxLeft) {
                $maxLeft = $len;
            }

            $opts[] = [
                'left' => $left,
                'right' => $configOpt->get(Option::DESCRIPTION),
            ];
        }

        $str = '';
        foreach($opts as $opt) {
            $str .= '  ' . str_pad($opt['left'], $maxLeft) . '    ' . $opt['right'] . PHP_EOL;
        }

        if ($this->extraHelp) {
            $str .= PHP_EOL . $this->extraHelp;
        }

        return $str;
    }
}
