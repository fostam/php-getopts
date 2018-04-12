<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class InvalidOptionException
 * @package Fostam\GetOpts\Exception
 */
class InvalidOptionException extends UsageException {
    private $option;
    private $value;

    /**
     * InvalidOptionException constructor.
     * @param string $option
     * @param mixed $value
     */
    public function __construct($option, $value) {
        $this->option = $option;
        $this->value = $value;
        parent::__construct("invalid value '{$value}' for '{$option}'");
    }

    /**
     * @return string
     */
    public function getOption() {
        return $this->option;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }
}
