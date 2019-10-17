<?php

namespace Fostam\GetOpts\Config;

use Fostam\GetOpts\Exception\ArgumentConfigException;

/**
 * Class Argument
 * @package Fostam\GetOpts\Config
 */
class Argument {
    const REQUIRED      = 'REQUIRED';
    const NAME          = 'NAME';
    const MULTIPLE      = 'MULTIPLE';
    const DEFAULT_VALUE = 'DEFAULT_VALUE';
    const VALIDATOR     = 'VALIDATOR';

    private $config = [
        self::REQUIRED      => null,
        self::NAME          => null,
        self::MULTIPLE      => null,
        self::DEFAULT_VALUE => null,
        self::VALIDATOR     => null,
    ];

    private $validated = false;


    /**
     * Argument constructor.
     */
    public function __construct() {
    }

    /**
     * change the argument from optional to mandatory
     *
     * @return $this
     */
    public function required() {
        $this->validated = false;
        $this->config[self::REQUIRED] = true;
        return $this;
    }

    /**
     * set the argument's name
     *
     * @param string $name
     * @return $this
     */
    public function name($name) {
        $this->config[self::NAME] = $name;
        if (!self::validateName($name)) {
            throw new ArgumentConfigException('illegal characters in name: ' . $name);
        }
        return $this;
    }

    /**
     * allow the argument to be given multiple times (only valid for the last argument)
     *
     * @return $this
     */
    public function multiple() {
        $this->validated = false;
        $this->config[self::MULTIPLE] = true;
        return $this;
    }

    /**
     * set the argument's default value
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
     * set a validator function that checks if the argument is valid
     *
     * @param callable $validator
     * @return $this
     */
    public function validator(callable $validator) {
        $this->config[self::VALIDATOR] = $validator;
        return $this;
    }

    /**
     * return full argument configuration, or a specific parameter of the configuration
     *
     * @param string $param
     * @return mixed
     * @throws ArgumentConfigException
     */
    public function get($param = '') {
        $this->validate();

        if ($param) {
            if (array_key_exists($param, $this->config)) {
                return $this->config[$param];
            }
            else {
                throw new ArgumentConfigException('argument param not valid: ' . $param);
            }
        }

        return $this->config;
    }

    /**
     * @param $name
     * @return int
     */
    public static function validateName($name) {
        return boolval(preg_match('#^[a-zA-Z0-9\-\_]+$#', $name));
    }

    /**
     * @throws ArgumentConfigException
     */
    private function validate() {
        if ($this->validated) {
            return;
        }

        // required argument cannot have a default value
        if (!is_null($this->config[self::REQUIRED])) {
            if (!is_null($this->config[self::DEFAULT_VALUE])) {
                throw new ArgumentConfigException('incompatible settings in argument config');
            }
        }

        $this->validated = true;
    }
}
