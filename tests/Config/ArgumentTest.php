<?php

namespace Fostam\GetOpts\Tests\Config;

use Fostam\GetOpts\Exception\ArgumentConfigException;
use PHPUnit\Framework\TestCase;
use Fostam\GetOpts\Config\Argument;

class ArgumentTest extends TestCase {
    /** @var Argument */
    private $argument;

    public function setUp() {
        parent::setUp();
        $this->argument = new Argument();
    }

    public function testName() {
        $this->argument->name('test');
        $this->assertEquals('test', $this->argument->get(Argument::NAME));
        $this->assertEquals('test', $this->argument->get()[Argument::NAME]);
    }

    public function testNameInvalid() {
        $this->expectException(ArgumentConfigException::class);
        $this->argument->name('');
    }

    public function testRequired() {
        $this->argument->required();
        $this->assertTrue($this->argument->get(Argument::REQUIRED));
        $this->assertTrue($this->argument->get()[Argument::REQUIRED]);
    }

    public function testMultiple() {
        $this->argument->multiple();
        $this->assertTrue($this->argument->get(Argument::MULTIPLE));
        $this->assertTrue($this->argument->get()[Argument::MULTIPLE]);
    }

    public function testDefaultValue() {
        $this->argument->defaultValue('test');
        $this->assertEquals('test', $this->argument->get(Argument::DEFAULT_VALUE));
        $this->assertEquals('test', $this->argument->get()[Argument::DEFAULT_VALUE]);
    }

    public function testValidator() {
        $validator = function() {};
        $this->argument->validator($validator);
        $this->assertEquals($validator, $this->argument->get(Argument::VALIDATOR));
        $this->assertEquals($validator, $this->argument->get()[Argument::VALIDATOR]);
    }

    public function testValidateName() {
        $this->assertTrue(Argument::validateName('test'));
        $this->assertTrue(Argument::validateName('t'));
        $this->assertTrue(Argument::validateName('33333'));
        $this->assertFalse(Argument::validateName('*'));
        $this->assertFalse(Argument::validateName(null));
        $this->assertFalse(Argument::validateName(''));
    }

    public function testGetOnInvalidArgumentParam() {
        $this->expectException(ArgumentConfigException::class);
        $this->argument->get('invalid_arg');
    }
}
