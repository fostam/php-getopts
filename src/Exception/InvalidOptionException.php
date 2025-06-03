<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class InvalidOptionException
 * @package Fostam\GetOpts\Exception
 */
class InvalidOptionException extends UsageException {
    private string $option;
    private mixed $value;

    /**
     * InvalidOptionException constructor.
     * @param mixed $value
     */
    public function __construct(string $option, $value) {
        $this->option = $option;
        $this->value = $value;
        parent::__construct("invalid value '{$value}' for '{$option}'");
    }

    public function getOption(): string {
        return $this->option;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed {
        return $this->value;
    }
}
