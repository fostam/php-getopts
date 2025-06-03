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

    private array $config = [
        self::REQUIRED      => null,
        self::NAME          => null,
        self::MULTIPLE      => null,
        self::DEFAULT_VALUE => null,
        self::VALIDATOR     => null,
    ];

    private bool $validated = false;


    /**
     * Argument constructor.
     */
    public function __construct() {
    }

    /**
     * change the argument from optional to mandatory
     */
    public function required(): self {
        $this->validated = false;
        $this->config[self::REQUIRED] = true;
        return $this;
    }

    /**
     * set the argument's name
     */
    public function name(string $name): self {
        $this->config[self::NAME] = $name;
        if (!self::validateName($name)) {
            throw new ArgumentConfigException('illegal characters in name: ' . $name);
        }
        return $this;
    }

    /**
     * allow the argument to be given multiple times (only valid for the last argument)
     */
    public function multiple(): self {
        $this->validated = false;
        $this->config[self::MULTIPLE] = true;
        return $this;
    }

    /**
     * set the argument's default value
     */
    public function defaultValue(mixed $value): self {
        $this->validated = false;
        $this->config[self::DEFAULT_VALUE] = $value;
        return $this;
    }

    /**
     * set a validator function that checks if the argument is valid
     */
    public function validator(callable $validator): self {
        $this->config[self::VALIDATOR] = $validator;
        return $this;
    }

    /**
     * return full argument configuration, or a specific parameter of the configuration
     *
     * @throws ArgumentConfigException
     */
    public function get(string $param = ''): mixed {
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

    public static function validateName(?string $name): bool {
        return boolval(preg_match('#^[a-zA-Z0-9\-_]+$#', $name));
    }

    /**
     * @throws ArgumentConfigException
     */
    private function validate(): void {
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
