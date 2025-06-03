<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class InvalidArgumentException
 * @package Fostam\GetOpts\Exception
 */
class InvalidArgumentException extends UsageException {
    private string $argumentName;

    /**
     * InvalidArgumentException constructor.
     */
    public function __construct(string $argument) {
        $this->argumentName = $argument;
        parent::__construct('invalid argument: ' . $argument);
    }

    public function getArgumentName(): string {
        return $this->argumentName;
    }
}
