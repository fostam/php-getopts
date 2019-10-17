<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class InvalidArgumentException
 * @package Fostam\GetOpts\Exception
 */
class InvalidArgumentException extends UsageException {
    private $argumentName;

    /**
     * InvalidArgumentException constructor.
     * @param string $argument
     */
    public function __construct($argument) {
        $this->argumentName = $argument;
        parent::__construct('invalid argument: ' . $argument);
    }

    /**
     * @return string
     */
    public function getArgumentName() {
        return $this->argumentName;
    }
}
