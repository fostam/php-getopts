<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class MissingArgumentsException
 * @package Fostam\GetOpts\Exception
 */
class MissingArgumentsException extends UsageException {
    /**
     * MissingArgumentsException constructor.
     */
    public function __construct() {
        parent::__construct('missing arguments');
    }
}