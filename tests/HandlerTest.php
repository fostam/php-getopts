<?php

namespace Fostam\GetOpts\Tests;

use Fostam\GetOpts\Exception\MissingArgumentsException;
use Fostam\GetOpts\Handler;
use PHPUnit\Framework\TestCase;

/**
 * @covers Handler
 */
final class HandlerTest extends TestCase {
    public function testHandling() {
        $handler = new Handler();
        $handler->addOption('opt1')->short('a')->incrementable();
        $handler->addOption('opt2')->short('b')->defaultValue('defoptval');
        $handler->addOption('opt3')->short('c');
        $handler->addArgument('arg1');
        $handler->addArgument('arg2')->defaultValue('defargval');
        $handler->parse(['script', '-aaa', 'test']);

        $this->assertEquals(['opt1' => 3, 'opt2' => 'defoptval', 'opt3' => null], $handler->getOptions());
        $this->assertEquals(['arg1' => 'test', 'arg2' => 'defargval'], $handler->getArguments());
        $this->assertEquals('script', $handler->getScriptName());
    }

    public function testDisableErrorHandling() {
        $this->expectException(MissingArgumentsException::class);

        $handler = new Handler();
        $handler->disableErrorHandling();
        $handler->addArgument('test')->required();
        $handler->parse([]);
    }
}