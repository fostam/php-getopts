<?php

namespace Fostam\GetOpts;

use Fostam\GetOpts\Config\Argument;
use Fostam\GetOpts\Config\Option;
use Fostam\GetOpts\Exception\ConfigException;
use Fostam\GetOpts\Exception\ShowHelpException;
use Fostam\GetOpts\Exception\UsageException;
use JetBrains\PhpStorm\NoReturn;
use LogicException;

/**
 * Class Handler
 * @package Fostam\GetOpts
 */
class Handler {
    /** @var Option[] $configOpts */
    private array $configOpts = [];
    /** @var Argument[] $configArgs */
    private array $configArgs = [];

    private string $scriptName = 'php-script';

    private array $result = [];

    private string $helpShort = 'h';
    private string $helpLong = 'help';
    private bool $errorHandling = true;
    private bool $terseUsage = false;
    private string $extraHelp = '';


    /**
     * Parser constructor.
     */
    public function __construct() {
    }

    /**
     * add a new option
     *
     * @throws ConfigException
     */
    public function addOption(string $name, ?Option $opt = null): Option {
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
     */
    public function addArgument($name, ?Argument $arg = null): Argument {
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
     */
    public function setHelpOptionLong(string $str): self {
        if (!Option::validateLong($str)) {
            throw new ConfigException('long help option has invalid format');
        }
        $this->helpLong = $str;
        return $this;
    }

    /**
     * set the short option for help handling
     */
    public function setHelpOptionShort(string $str): self {
        if (!Option::validateShort($str)) {
            throw new ConfigException('short help option must be a single character');
        }
        $this->helpShort = $str;
        return $this;
    }

    /**
     * disabled built-in error handling and throw exceptions instead
     */
    public function disableErrorHandling(): self {
        $this->errorHandling = false;
        return $this;
    }

    /**
     * don't list options in Usage string
     */
    public function enableTerseUsage(): self {
        $this->terseUsage = true;
        return $this;
    }

    /**
     * set additional help text that is appended below the generated help
     */
    public function setExtraHelpText(string $text): self {
        $this->extraHelp = $text;
        return $this;
    }

    /**
     * parse arguments
     * the first element in the array is treated as script name
     *
     * @throws UsageException
     */
    public function parse(?array $inputArgs = null): void {
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
        catch (ShowHelpException) {
            $this->showHelp();
        }

        $this->result = $parser->getResult();
    }

    /**
     * @throws UsageException
     */
    public function get(string $name = ''): mixed {
        if (empty($this->result)) {
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
     * @throws UsageException
     */
    public function getOptions(): array {
        if (empty($this->result)) {
            $this->parse();
        }
        return $this->result[Parser::RESULT_OPTIONS];
    }

    /**
     * @throws UsageException
     */
    public function hasOption(string $option): bool {
        return (array_key_exists($option, $this->getOptions()));
    }

    /**
     * retrieve specific option
     *
     * @throws UsageException
     */
    public function getOption(string $option, mixed $default = null): mixed {
        $options = $this->getOptions();
        return $this->hasOption($option) ? $options[$option] : $default;
    }

    /**
     * is option equal to given value
     *
     * @throws UsageException
     */
    public function isOptionValue(string $option, mixed $value): bool {
        $options = $this->getOptions();
        return $this->hasOption($option) && $options[$option] == $value;
    }

    /**
     * get parsed argument values
     *
     * @throws UsageException
     */
    public function getArguments(): array {
        if (empty($this->result)) {
            $this->parse();
        }
        return $this->result[Parser::RESULT_ARGUMENTS];
    }

    /**
     * retrieve specific argument
     *
     * @throws UsageException
     */
    public function getArgument(string $argument, mixed $default = null): mixed {
        $arguments = $this->getArguments();
        return $this->hasArgument($argument) ? $arguments[$argument] : $default;
    }

    /**
     * @throws UsageException
     */
    public function hasArgument(string $argument): bool {
        return (array_key_exists($argument, $this->getArguments()));
    }

    /**
     * is argument equal to a value
     *
     * @throws UsageException
     */
    public function isArgumentValue(string $argument, mixed $value): bool {
        $arguments = $this->getArguments();
        return isset($arguments[$argument]) && $arguments[$argument] == $value;
    }

    /**
     * get the generated usage string (without script name and EOL)
     */
    public function getUsageString(): string {
        return $this->buildUsageString();
    }

    /**
     * get the generated help text (might be multiple lines, each line terminated by an EOL)
     */
    public function getHelpText(): string {
        return $this->buildHelpText();
    }

    /**
     * get the name of the PHP script
     */
    public function getScriptName(): string {
        return $this->scriptName;
    }

    /**
     * @param UsageException $e
     */
    #[NoReturn] private function handleUsageError(UsageException $e): void {
        $str = $this->scriptName . ': ' . $e->getMessage() . PHP_EOL;
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
     * @throws ConfigException
     */
    private function getCommandLineArgs(?array $args): array {
        if (!is_null($args)) {
            return $args;
        } else if (isset($_SERVER['argv'])) {
            return $_SERVER['argv'];
        } else {
            throw new ConfigException('no options to parse');
        }
    }

    private function buildUsageString(): string {
        $str = $this->scriptName . ' ';

        // options
        if ($this->terseUsage && $this->configOpts) {
            $str .= '[OPTION]... ';
        }
        else {
            foreach ($this->configOpts as $configOpt) {
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
        foreach($this->configArgs as $configArg) {
            $arg = $configArg->get(Argument::NAME);
            if ($arg) {
                $arg = strtoupper($arg);
            }
            else {
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
    #[NoReturn] private function showHelp(): void {
        print 'Usage: ' . $this->getUsageString() . PHP_EOL;
        print $this->buildHelpText();
        exit(0);
    }

    private function buildHelpText(): string {
        $opts = [];
        $maxLeft = 0;
        foreach($this->configOpts as $configOpt) {
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
