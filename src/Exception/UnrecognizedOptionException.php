<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class UnrecognizedOptionException
 * @package Fostam\GetOpts\Exception
 */
class UnrecognizedOptionException extends UsageException {
    private string $option;

    /**
     * TooManyArgumentsException constructor.
     */
    public function __construct(string $option) {
        $this->option = $option;
        parent::__construct('unrecognized option: ' . $option);
    }

    public function getOption(): string {
        return $this->option;
    }
}
