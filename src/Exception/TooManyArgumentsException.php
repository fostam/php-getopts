<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class TooManyArgumentsException
 * @package Fostam\GetOpts\Exception
 */
class TooManyArgumentsException extends UsageException {
    /**
     * TooManyArgumentsException constructor.
     */
    public function __construct() {
        parent::__construct('too many arguments');
    }
}