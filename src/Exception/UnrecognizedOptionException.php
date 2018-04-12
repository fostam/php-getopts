<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class UnrecognizedOptionException
 * @package Fostam\GetOpts\Exception
 */
class UnrecognizedOptionException extends UsageException {
    private $option;

    /**
     * TooManyArgumentsException constructor.
     * @param string $option
     */
    public function __construct($option) {
        $this->option = $option;
        parent::__construct('unrecognized option: ' . $option);
    }

    /**
     * @return mixed
     */
    public function getOption() {
        return $this->option;
    }
}
