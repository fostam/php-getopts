<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class MissingOptionsException
 * @package Fostam\GetOpts\Exception
 */
class MissingOptionsException extends UsageException {
    /**
     * MissingOptionsException constructor.
     */
    public function __construct() {
        parent::__construct('missing options');
    }
}
