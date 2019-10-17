<?php

namespace Fostam\GetOpts\Tests;

use Fostam\GetOpts\Exception\MissingArgumentsException;
use Fostam\GetOpts\Exception\ConfigException;
use Fostam\GetOpts\Handler;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase {
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

    public function testSetHelpOptionLong() {
        $handler = new Handler();
        $result = $handler->setHelpOptionLong('--long');

        $this->assertInstanceOf(Handler::class, $result);
    }

    public function testSetHelpOptionLongOnInvalidLongOption() {
        $this->expectException(ConfigException::class);

        $handler = new Handler();
        $handler->setHelpOptionLong('invalid long option description.');
    }

    public function testSetHelpOptionShort() {
        $handler = new Handler();
        $result = $handler->setHelpOptionShort('s');

        $this->assertInstanceOf(Handler::class, $result);
    }

    public function testSetHelpOptionShortOnInvalidShortOption() {
        $this->expectException(ConfigException::class);

        $handler = new Handler();
        $result = $handler->setHelpOptionShort('invalid short option description.');

        $this->assertInstanceOf(Handler::class, $result);
    }

    public function testAddOptionOnExistedOption() {
        $this->expectException(ConfigException::class);

        $handler = new Handler();
        $handler->addOption('opt1')->short('a')->incrementable();
        $handler->addOption('opt1')->short('a')->incrementable();
    }

    public function testAddArgumentOnExistedParam() {
        $this->expectException(ConfigException::class);

        $handler = new Handler();
        $handler->addArgument('arg1');
        $handler->addArgument('arg1');
    }

    public function testEnableTerseUsage() {
        $handler = new Handler();
        $result = $handler->enableTerseUsage();

        $this->assertInstanceOf(Handler::class, $result);
    }

    public function testSetExtraHelpText() {
        $handler = new Handler();
        $result = $handler->setExtraHelpText('this is extra help text.');

        $this->assertInstanceOf(Handler::class, $result);
    }

    public function testGetUsageString() {
        $handler = new Handler();

        $this->assertSame('php-script', $handler->getUsageString());
    }

    public function testGetHelpText() {
        $handler = new Handler();

        $this->assertSame('', $handler->getHelpText());
    }
}
