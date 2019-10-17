<?php

namespace Fostam\GetOpts\Config;

use Fostam\GetOpts\Exception\OptionConfigException;

/**
 * Class Option
 * @package Fostam\GetOpts\Config
 */
class Option {
    const REQUIRED      = 'REQUIRED';
    const SHORT         = 'SHORT';
    const LONG          = 'LONG';
    const ARGUMENT      = 'ARGUMENT';
    const MULTIPLE      = 'MULTIPLE';
    const INCREMENTABLE = 'INCREMENTABLE';
    const NEGATABLE     = 'NEGATABLE';
    const DESCRIPTION   = 'DESCRIPTION';
    const DEFAULT_VALUE = 'DEFAULT_VALUE';
    const VALIDATOR     = 'VALIDATOR';

    private $config = [
        self::REQUIRED      => null,
        self::SHORT         => null,
        self::LONG          => null,
        self::ARGUMENT      => null,
        self::MULTIPLE      => null,
        self::INCREMENTABLE => null,
        self::NEGATABLE     => null,
        self::DESCRIPTION   => null,
        self::DEFAULT_VALUE => null,
        self::VALIDATOR     => null,
    ];

    private $validated = false;

    /**
     * Option constructor.
     */
    public function __construct() {
    }

    /**
     * set the option's short name
     *
     * @param string $short
     * @return $this
     */
    public function short($short) {
        $this->validated = false;
        if (!self::validateShort($short)) {
            throw new OptionConfigException('short option must be a single character: ' . $short);
        }
        $this->config[self::SHORT] = $short;
        return $this;
    }

    /**
     * set the option's long name
     *
     * @param string $long
     * @return $this
     */
    public function long($long) {
        $this->validated = false;
        if (!self::validateLong($long)) {
            throw new OptionConfigException('invalid long option format: ' . $long);
        }
        $this->config[self::LONG] = $long;
        return $this;
    }

    /**
     * set the option's description used in the help message
     *
     * @param string $description
     * @return $this
     */
    public function description($description) {
        $this->config[self::DESCRIPTION] = $description;
        return $this;
    }

    /**
     * let the option require an argument with the given name
     *
     * @param string $name
     * @return $this
     */
    public function argument($name) {
        $this->validated = false;
        if (!preg_match('#^[a-zA-Z0-9\-_]+$#', $name)) {
            throw new OptionConfigException('illegal characters in name: ' . $name);
        }
        $this->config[self::ARGUMENT] = $name;
        return $this;
    }

    /**
     * change the option from optional to mandatory
     *
     * @return $this
     */
    public function required() {
        $this->validated = false;
        $this->config[self::REQUIRED] = true;
        return $this;
    }

    /**
     * set the option argument's default value
     *
     * @param mixed $value
     * @return $this
     */
    public function defaultValue($value) {
        $this->validated = false;
        $this->config[self::DEFAULT_VALUE] = $value;
        return $this;
    }

    /**
     * allow the option to be given multiple times
     *
     * @return $this
     */
    public function multiple() {
        $this->validated = false;
        $this->config[self::MULTIPLE] = true;
        return $this;
    }

    /**
     * allow a negated version of the option (argumentless options only)
     *
     * @return $this
     */
    public function negatable() {
        $this->validated = false;
        $this->config[self::NEGATABLE] = true;
        return $this;
    }

    /**
     * set the argument to incremental (increased by $increment when given multiple times)
     *
     * @param int $increment
     * @return $this
     */
    public function incrementable($increment = 1) {
        $this->validated = false;
        $this->config[self::INCREMENTABLE] = $increment;
        return $this;
    }

    /**
     * set a validator function that checks if the option's argument is valid
     *
     * @param callable $callable
     * @return $this
     */
    public function validator(callable $callable) {
        $this->config[self::VALIDATOR] = $callable;
        return $this;
    }

    /**
     * return full option configuration, or a specific parameter of the configuration
     *
     * @param string $param
     * @return mixed
     */
    public function get($param = '') {
        $this->validate();

        if ($param) {
            if (array_key_exists($param, $this->config)) {
                return $this->config[$param];
            }
            else {
                throw new OptionConfigException('option param not valid: ' . $param);
            }
        }

        return $this->config;
    }

    /**
     * @throw OptionConfigException
     */
    private function validate() {
        if ($this->validated) {
            return;
        }

        $valid = true;
        if (!is_null($this->config[self::ARGUMENT])) {
            // if the option requires an argument, it can't be incremental or negatable
            if (!is_null($this->config[self::INCREMENTABLE])
                || !is_null($this->config[self::NEGATABLE])) {
                $valid = false;
            }
        }
        else {
            // incrementable or flag
            if (!is_null($this->config[self::MULTIPLE])) {
                $valid = false;
            }
        }

        // negatable requires long option
        if (!is_null($this->config[self::NEGATABLE]) && is_null($this->config[self::LONG])) {
            $valid = false;
        }

        // 'multiple' options must have an argument
        if (!is_null($this->config[self::MULTIPLE])) {
            if (is_null($this->config[self::ARGUMENT])) {
                $valid = false;
            }
        }

        // required argument cannot have a default value
        if (!is_null($this->config[self::REQUIRED])) {
            if (!is_null($this->config[self::DEFAULT_VALUE])) {
                $valid = false;
            }
        }

        // at least one of short and long have to be set
        if (is_null($this->config[self::SHORT]) && is_null($this->config[self::LONG])) {
            $valid = false;
        }

        if (!$valid) {
            throw new OptionConfigException('incompatible settings in option config');
        }

        $this->validated = true;
    }

    /**
     * @param string $opt
     * @return bool
     */
    public static function validateShort($opt) {
        return boolval(preg_match('#^[a-zA-Z0-9]$#', $opt));
    }

    /**
     * @param string $opt
     * @return bool
     */
    public static function validateLong($opt) {
        return boolval(preg_match('#^[a-zA-Z0-9\-_]+$#', $opt));
    }
}
