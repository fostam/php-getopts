<?php

namespace Fostam\GetOpts\Exception;

/**
 * Class MissingOptionArgumentException
 * @package Fostam\GetOpts\Exception
 */
class MissingOptionArgumentException extends UsageException {
    private string $option;

    /**
     * MissingOptionArgumentException constructor.
     */
    public function __construct(string $option) {
        $this->option = $option;
        parent::__construct('missing argument to option: ' . $option);
    }

    public function getOption(): string {
        return $this->option;
    }
}
