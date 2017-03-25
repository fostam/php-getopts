<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class MissingOptionArgumentException
 * @package Fostam\GetOpts\Exception
 */
class MissingOptionArgumentException extends UsageException {
    private $option;

    /**
     * MissingOptionArgumentException constructor.
     * @param string $option
     */
    public function __construct($option) {
        $this->option = $option;
        parent::__construct('missing argument to option: ' . $option);
    }

    /**
     * @return mixed
     */
    public function getOption() {
        return $this->option;
    }
}